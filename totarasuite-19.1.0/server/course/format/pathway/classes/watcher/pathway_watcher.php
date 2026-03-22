<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package format_pathway
 */

namespace format_pathway\watcher;

use context_course;
use core\hook\before_output_start;
use core\hook\course_module_available_info;
use core\hook\modify_footer;
use core\hook\modify_header;
use core\hook\module_available_display_options;
use core\hook\module_get_final_display_type;
use core\hook\navigation_load_course_settings;
use core\hook\reset_theme_and_output;
use core\hook\set_page_layout;
use core\hook\theme_initialised_for_page_layout;
use core_completion\hook\override_activity_self_completion_form;
use core_course\hook\course_view;
use core_text;
use format_pathway\blacklist_helper;
use format_pathway\is_required_helper;
use format_pathway\overview_helper;
use moodle_url;
use navigation_node;
use pix_icon;
use totara_tui\output\component;

global $CFG;

require_once("$CFG->libdir/resourcelib.php");

class pathway_watcher {

    public static string $pathway_layout = 'legacynolayout';
    private static string $layout_nonce;

    public static function redirect_to_first_activity(course_view $hook): void {
        global $USER;

        $course = $hook->get_container();

        if ($course->format != 'pathway') {
            return;
        }

        // If the user is editing (or turning editing on) then we let the course page load, and it'll be in edit mode.
        if (overview_helper::is_user_editing() || optional_param('edit', false, PARAM_BOOL)) {
            return;
        }

        // We check that the user can login here in order to trigger enrolment if they are not enrolled,
        // rather than leaving it up to the activity page to make that check.
        require_course_login($course->id);

        // This might include activities where the user is not admin or enrolled
        // but has the capability to "view" the activity, even though they don't have access to the course.
        $first_cm = overview_helper::get_first_course_module_for_user($course->id, $USER->id);

        // If there are no activities that they can see, and they are not in editing mode, then we let the course page
        // load. It'll show a "There are no activities" message.
        if (!$first_cm) {
            return;
        }

        // If there is an activity, but it doesn't have a URL, then we'll go to the activity's edit page.
        // This shouldn't happen for learners, but there's certain edge cases where it might happen for admins.
        if (is_null($first_cm->get_url())) {
            if (has_capability('moodle/course:manageactivities', $first_cm->context)) {
                $url = new moodle_url('/course/modedit.php', array('update' => $first_cm->id));
                redirect($url);
            } else {
                // This shouldn't be possible. If the user can somehow see the activity but there's no view
                // available and they're not editing and they can't edit it then we'll let the course page load.
                return;
            }
        }

        // redirect the user to the first activity in the course
        redirect($first_cm->get_url());
    }

    /**
     * decides whether the page should apply the new layout or not
     *
     * @param theme_initialised_for_page_layout $hook
     * @return void
     */
    public static function intialise_instance(theme_initialised_for_page_layout $hook) {
        global $PAGE;
        $helper = is_required_helper::initialise_instance($PAGE, $hook->get_page_layout());
        if ($helper->should_replace_layout()) {
            $hook->set_page_layout(static::$pathway_layout);
        }
    }

    public static function before_output_start(before_output_start $hook) {
        $helper = is_required_helper::get_instance();
        if (!$helper->should_replace_layout()) {
            return;
        }

        // Register component with page so that we don't have to load it at runtime.
        // (doing `$OUTPUT->render($comp)` normally in modify_footer is too late)
        component::register_component('format_pathway/pages/MainContentDisplay', $hook->get_page());
    }

    /**
     * checks that the page should use the new format - creates a nonce for the activity layout
     * so that just that component can be linked and targeted by the frontend. The frontend gets
     * passed this info when the footer watcher is triggered
     *
     * @param modify_header $hook
     * @return void
     */
    public static function modify_header(modify_header $hook) {
        global $PAGE;

        // check if the page should be modified - should only be on course pages where the course is using the pathway format
        // generate the id and start the template tag
        $helper = is_required_helper::get_instance();
        if (!$helper->should_replace_layout()) {
            return;
        }

        $header = $hook->get_header();
        $header .= "\n";

        static::$layout_nonce = uniqid();
        $header .= '<template id="maincontent-'.static::$layout_nonce.'">';
        $hook->set_header($header);

        $PAGE->requires->output_content_js = false;
    }

    /**
     * This function closes the custom layout template header defined in the header watcher function
     * it also passes the page props (course id, section id, course moduler id) and the template id
     * that we created in the header watcher so the front end can do it's thing.
     *
     * @param modify_footer $hook
     * @return void
     * @throws \dml_exception
     */
    public static function modify_footer(modify_footer $hook) {
        global $PAGE, $DB, $CFG, $USER;

        $course_id = $PAGE->course->id ?? null;
        if (empty($course_id) || $course_id == SITEID) {
            return;
        }

        // check if the page should be modified - should only be on course pages where the course is using the pathway format
        // close the template tag
        $helper = is_required_helper::get_instance();
        if (!$helper->should_replace_layout()) {
            return;
        }

        $footer = $hook->get_footer();
        $extra_footer = '';

        if (!$PAGE->requires->output_content_js) {
            $extra_footer .= $PAGE->requires->get_content_end_code();
        }

        $extra_footer .= "\n</template>";

        // grab the course module requires
        $cmid = $PAGE->cm->id ?? null;

        $exclude_nav = overview_helper::is_page_course_view($PAGE);

        // grab the section details
        $current_section = null;
        $on_activity_page = false;
        if (!is_null($cmid)) {
            $current_section = $DB->get_field('course_modules', 'section', ['id' => $cmid]);
            $course_id_from_section = $DB->get_field('course_sections', 'course', ['id' => $current_section]);
            $mod_info = get_fast_modinfo($course_id_from_section);
            $cm = $mod_info->get_cm($cmid);
            $on_activity_page = str_starts_with($PAGE->url->out(),  $CFG->wwwroot . '/mod/' . $cm->modname);
            $plugin_name = get_string('pluginname', 'mod_' . $cm->modname);
            $back_link_text = get_string('back_to_activity', 'format_pathway', $plugin_name);
            $back_link_url = is_null($cm->get_url()) ? '' : ($cm->get_url())->out();
        } else {
            $first_cm = overview_helper::get_first_course_module_for_user($PAGE->course->id, $USER->id);
            if (is_null($first_cm)) {
                $back_link_url = '';
            } else {
                $back_link_url = is_null($first_cm->get_url()) ? '' : ($first_cm->get_url())->out();
            }
            $back_link_text = get_string('back_to_course', 'format_pathway');
        }

        // set the tui page component with course, course module and current active section
        $component = new component('format_pathway/pages/MainContentDisplay', [
            'pageProps' => [
                'cmid' => (int)$cmid,
                'sectionid' => (int)$current_section,
                 // Use course id from PAGE.
                'courseid' => $course_id,
                'excludeNav' => $exclude_nav,
                'onActivityPage' => $on_activity_page,
                'backLinkText' => $back_link_text,
                'backLinkUrl' => $back_link_url,
            ],
            'templateId' => 'maincontent-'.static::$layout_nonce,
        ]);
        $extra_footer .= $component->out_html();

        $hook->set_footer($extra_footer . "\n" . $footer);
    }

    /**
     * When the course navigation tree is loaded, add the "Manage actions and activities" node when appropriate
     *
     * @param navigation_load_course_settings $hook
     * @return void
     */
    public static function navigation_load_course_settings(navigation_load_course_settings $hook): void {
        $course = $hook->get_course();

        if ($course->format != 'pathway') {
            return;
        }

        $coursecontext = context_course::instance($course->id);
        $adminoptions = course_get_user_administration_options($course, $coursecontext);
        if (!$adminoptions->update) {
            return;
        }

        // Find the key of the first node.
        $coursenode = $hook->get_node();
        $children_keys = $coursenode->get_children_key_list();
        $first_node_key = reset($children_keys);

        // Create the new node.
        $url = new moodle_url('/course/view.php', ['id' => $course->id, 'edit' => 'on', 'sesskey' => sesskey()]);
        $new_node = navigation_node::create(
            get_string('managesectionsandactivities', 'format_pathway'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'sectionsandactivities',
            new pix_icon('i/settings', '')
        );

        // Add it at the start of the list.
        $coursenode->add_node($new_node, $first_node_key);
    }

    /**
     * When theme are output are reset we need to reset the pathway is-required helper
     *
     * @param reset_theme_and_output $hook
     * @return void
     */
    public static function reset_theme_and_output(reset_theme_and_output $hook): void {
        is_required_helper::reset_instance();
    }

    /**
     * Clear the hooks activity self completion form to prevent display for pathway courses.
     *
     * @param override_activity_self_completion_form $hook
     * @return void
     */
    public static function override_activity_self_completion_form(override_activity_self_completion_form $hook): void {
        $course = $hook->get_course();

        // Only pathway courses should be affected.
        if ($course->format != 'pathway') {
            return;
        }

        $hook->set_form('');
    }

    /**
     * @param module_get_final_display_type $hook
     * @return void
     */
    public static function module_get_final_display_type(module_get_final_display_type $hook): void {
        switch ($hook->module_name) {
            case 'resource':
            case 'url':
                if ($hook->get_course_format() !== 'pathway') {
                    return;
                }

                if ($hook->module->display == RESOURCELIB_DISPLAY_EMBED) {
                    $hook->override_display_type = RESOURCELIB_DISPLAY_EMBED;
                    return;
                }

                // Any other display defaults to this.
                $hook->override_display_type = RESOURCELIB_DISPLAY_LINK;
        }
    }

    /**
     * @param module_available_display_options $hook
     * @return void
     */
    public static function module_available_display_options(module_available_display_options $hook): void {
        switch ($hook->module_name) {
            case 'resource':
            case 'url':
                if ($hook->get_course_format() !== 'pathway') {
                    return;
                }

                $hook->available_options = array_filter($hook->available_options, function (int $option) {
                    return in_array($option, [RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_LINK]);
                });

                if (empty($hook->available_options)) {
                    // Add default display_link option.
                    $hook->available_options[] = RESOURCELIB_DISPLAY_LINK;
                }
        }
    }

    /**
     * @param course_module_available_info $available_info
     * @return void
     */
    public static function course_module_available_info(course_module_available_info $available_info): void {
        if ($available_info->get_course_format() === 'pathway' && blacklist_helper::is_blacklisted($available_info->get_mod_name())) {
            $available_info->set_custom_style(['style' => 'color:#767676']);

            $mod_name = $available_info->get_mod_name();
            $mod_type = core_text::strtolower(get_string('activities'));
            if (plugin_supports('mod', $mod_name, FEATURE_MOD_ARCHETYPE) == MOD_ARCHETYPE_RESOURCE) {
                $mod_type = core_text::strtolower(get_string('resources'));
            }

            $available_info->set_available_info(
                get_string('available_info', 'format_pathway', ['name' => core_text::strtolower(get_string('pluginname', 'mod_' . $mod_name)), 'type' => $mod_type])
            );
        }
    }

    /**
     * @param set_page_layout $hook
     * @return void
     */
    public static function watch_set_page_layout(set_page_layout $hook): void {
        global $PAGE;

        // When viewing or grading assignment activities, stop watching to recalculate the pagelout for the pathway course.
        $stop_watch = $hook->get_page_layout() === 'incourse' && $PAGE->pagelayout === 'base' && $PAGE->activityname === 'assign';

        if ($PAGE->course) {
            if ($PAGE->course->format === 'pathway' && $hook->get_page_layout() !== self::$pathway_layout && !$stop_watch) {
                $helper = is_required_helper::initialise_instance($PAGE, $hook->get_page_layout());
                if ($helper->should_replace_layout()) {
                    $hook->set_page_layout(static::$pathway_layout);
                }
            }
        }
    }
}