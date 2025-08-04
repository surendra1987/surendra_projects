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

namespace format_pathway;

use context_course;
use course_modinfo;

class overview_helper {

    /**
     * Used for obtaining the first visible course module, from the first visible section
     *
     * @param int $course_id
     * @param int $user_id
     * @return \cm_info|null
     */
    public static function get_first_course_module_for_user(int $course_id, int $user_id): ?\cm_info {
        $course_modinfo = course_modinfo::instance($course_id, $user_id);
        $sections = $course_modinfo->get_section_info_all();
        $course_context = context_course::instance($course_id);
        $user_can_view_hidden_sections = has_capability('moodle/course:viewhiddensections', $course_context, $user_id);

        $adminoptions = course_get_user_administration_options($course_modinfo->get_course(), $course_context);

        foreach ($sections as $section) {
            if (!$section->visible && !$user_can_view_hidden_sections) {
                continue;
            }

            $section_modinfo = $section->modinfo;
            $cms = $section_modinfo->get_cms();
            foreach ($cms as $cm) {
                // Will check capability viewhiddenactivities, mod/[activityname]:view, available
                if (!$cm->get_user_visible()) {
                    continue;
                }

                // If the user cannot administer sections and activities then exclude blacklisted activities.
                if (!$adminoptions->update) {
                    if (blacklist_helper::is_blacklisted($cm->modname)) {
                        continue;
                    }
                }

                return $cm;
            }
        }
        return null;
    }

    /**
     * Used for ensuring the page being access is the course overview page
     *
     * @param \moodle_page $page
     * @return bool
     */
    public static function is_page_course_view(\moodle_page $page): bool {
        global $PAGE;
        if (
            $PAGE == $page
            && $page->has_set_url()
            && $page->url->compare(new \moodle_url('/course/view.php'), URL_MATCH_BASE)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Used for ensuring the request isn't the one to set the page into editing mode
     *
     * @return bool
     * @throws \coding_exception
     */
    public static function is_request_to_edit_page(): bool {
        $edit = optional_param('edit', -1, PARAM_BOOL);
        if (($edit == 0 || $edit == 1) && confirm_sesskey()) {
            return true;
        }
        return false;
    }

    /**
     * Is the current user editing the page
     *
     * @return bool
     */
    public static function is_user_editing(): bool {
        global $USER;
        return !empty($USER->editing);
    }
}