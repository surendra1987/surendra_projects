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

use core\hook\before_output_start;
use core\hook\modify_footer;
use core\hook\modify_header;
use core\hook\module_available_display_options;
use core\hook\module_get_final_display_type;
use core\hook\navigation_load_course_settings;
use core\hook\theme_initialised_for_page_layout;
use core_completion\hook\override_activity_self_completion_form;
use core_course\hook\course_view;
use core_phpunit\testcase;
use format_pathway\is_required_helper;
use format_pathway\watcher\pathway_watcher;
use totara_tui\output\framework;

global $CFG;

require_once("$CFG->libdir/resourcelib.php");

/**
 * @group format_pathway
 */
class format_pathway_pathway_watcher_test extends testcase {

    /**
     * Test the page layout hook triggers the pathway_watcher
     *
     * @return void
     * @throws coding_exception
     */
    public function test_initialise_instance(): void {
        global $PAGE;

        self::setRequestOrigin('WEB');

        $course = $this->getDataGenerator()->create_course(['format' => 'pathway']);
        $PAGE->set_course($course);
        $assignrecord = $this->getDataGenerator()->create_module('assign', array('course' => $course, 'grade' => 100, 'format' => 'pathway'));
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id);

        $PAGE->set_url('/mod/choice/view.php?id='.$cm->id);
        $PAGE->set_cm($cm);

        $hook_page_layout = new theme_initialised_for_page_layout('standard');
        $hook_page_layout->execute();
        $this->assertSame(pathway_watcher::$pathway_layout, $hook_page_layout->get_page_layout());
    }

    /**
     * Test the before_output_start watcher doesn't register the component for the give page when not using pathway
     *
     * @return void
     */
    public function test_before_output_start_on_non_applicable_page(): void {
        $page = new moodle_page();

        $hook = new before_output_start($page);

        // Mock the helper and force it to return false.
        $mocked_helper = $this->getMockBuilder(is_required_helper::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'should_replace_layout',
            ])
            ->getMock();
        $mocked_helper
            ->expects(self::once())
            ->method('should_replace_layout')
            ->willReturn(false);
        is_required_helper::set_instance($mocked_helper);

        // Execute the hook watcher.
        pathway_watcher::before_output_start($hook);

        // Make sure the component was NOT set (before the page is not applicable).
        $framework = $page->requires->framework(framework::class);
        self::assertNotContains("format_pathway", $framework->get_final_components());
    }

    /**
     * Test the before_output_start watcher doesn't register the component for the give page when not using pathway
     *
     * @return void
     */
    public function test_before_output_start_on_applicable_page(): void {
        $page = new moodle_page();

        $hook = new before_output_start($page);

        // Mock the helper and force it to return false.
        $mocked_helper = $this->getMockBuilder(is_required_helper::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'should_replace_layout',
            ])
            ->getMock();
        $mocked_helper
            ->expects(self::once())
            ->method('should_replace_layout')
            ->willReturn(true);
        is_required_helper::set_instance($mocked_helper);

        // Execute the hook watcher.
        pathway_watcher::before_output_start($hook);

        // Make sure the component was NOT set (before the page is not applicable).
        $framework = $page->requires->framework(framework::class);
        self::assertContains("format_pathway", $framework->get_final_components());
    }

    /**
     * Test the modify header watcher adjusts the html headers
     *
     * @return void
     * @throws coding_exception
     */
    public function test_modify_header(): void {
        global $PAGE;

        self::setRequestOrigin('WEB');

        $course = $this->getDataGenerator()->create_course(['format' => 'pathway']);
        $PAGE->set_course($course);
        $assignrecord = $this->getDataGenerator()->create_module('assign', array('course' => $course, 'grade' => 100, 'format' => 'pathway'));
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id);

        $PAGE->set_url('/mod/choice/view.php?id='.$cm->id);
        $PAGE->set_cm($cm);

        // ensure the page layout hook is run first so that it can set the page layout for the helper
        $hook_page_layout = new theme_initialised_for_page_layout('standard');
        $hook_page_layout->execute();

        $hook_modify_header = new modify_header('test header');
        $hook_modify_header->execute();
        $this->assertStringContainsString('test header', $hook_modify_header->get_header());
        $this->assertStringContainsString('<template id="maincontent-', $hook_modify_header->get_header());
    }

    /**
     * Test the modify footer watchers closes the template and adds the component
     *
     * @return void
     * @throws coding_exception
     */
    public function test_modify_footer(): void {
        global $PAGE;

        self::setRequestOrigin('WEB');

        $course = $this->getDataGenerator()->create_course(['format' => 'pathway']);
        $PAGE->set_course($course);
        $assignrecord = $this->getDataGenerator()->create_module('assign', array('course' => $course, 'grade' => 100, 'format' => 'pathway'));
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id);
        $PAGE->set_cm($cm);

        // Create assign module and set assign view url.
        $PAGE->set_url('/mod/assign/view.php?id='.$cm->id);
        $PAGE->set_cm($cm);

        $hook_page_layout = new theme_initialised_for_page_layout('standard');
        $hook_page_layout->execute();

        $hook_modify_header = new modify_header('test header');
        $hook_modify_header->execute();

        $hook_modify_footer = new modify_footer('test footer');
        $hook_modify_footer->execute();

        $this->assertStringContainsString('test footer', $hook_modify_footer->get_footer());
        // check that the module has been added and the cmid, sectionid, and courseid are included...
        $this->assertStringContainsString('</template>', $hook_modify_footer->get_footer());

        $this->assertStringContainsString(htmlentities('"cmid":'.$cm->id), $hook_modify_footer->get_footer());
        $this->assertStringContainsString(htmlentities('"onActivityPage":true'), $hook_modify_footer->get_footer());
    }

    /**
     * Test the component in the footer references the same nonce as the header setup in the html
     *
     * @return void
     */
    public function test_footer_watcher_references_header_template(): void {
        global $PAGE;

        self::setRequestOrigin('WEB');

        $course = $this->getDataGenerator()->create_course(['format' => 'pathway']);
        $PAGE->set_course($course);
        $assignrecord = $this->getDataGenerator()->create_module('assign', array('course' => $course, 'grade' => 100, 'format' => 'pathway'));
        $cm = get_coursemodule_from_instance('assign', $assignrecord->id);

        $PAGE->set_url('/mod/choice/view.php?id='.$cm->id);
        $PAGE->set_cm($cm);

        $hook_page_layout = new theme_initialised_for_page_layout('standard');
        $hook_page_layout->execute();

        $hook_modify_header = new modify_header('test header');
        $hook_modify_header->execute();

        $hook_modify_footer = new modify_footer('test footer');
        $hook_modify_footer->execute();

        // grab the inner html details
        $this->assertStringContainsString('test header', $hook_modify_header->get_header());
        preg_match('/maincontent\-(?<nonce>.*)"/', $hook_modify_header->get_header(), $matches);

        $this->assertStringContainsString('maincontent-'.$matches['nonce'], $hook_modify_footer->get_footer());
    }

    public function test_navigation_load_course_settings_works(): void {
        // Set up admin user and applicable course.
        self::setAdminUser();
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);

        // Set up an initial course admin tree (just one child node will do).
        $course_node = navigation_node::create(
            'course node title',
            'course node action',
            navigation_node::TYPE_SETTING,
            null,
            'course key'
        );
        $control_node = navigation_node::create(
            'control node title',
            'control node action',
            navigation_node::TYPE_SETTING,
            null,
            'control key'
        );
        $course_node->add_node($control_node);
        self::assertCount(1, $course_node->get_children_key_list());

        // Execute the hook.
        $hook = new navigation_load_course_settings($course_node, $course);
        $hook->execute();

        // Check the result.
        $course_node = $hook->get_node();
        $children_key_list = $course_node->get_children_key_list();
        self::assertCount(2, $children_key_list);
        $first_child_key = reset($children_key_list);
        $first_child = $course_node->get($first_child_key);

        $this->assertSame('sectionsandactivities', $first_child->key);
        $this->assertSame(get_string('managesectionsandactivities', 'format_pathway'), $first_child->text);
        $this->assertSame("/moodle/course/view.php", $first_child->action->get_path());
    }

    public function test_navigation_load_course_settings_skips_non_admins(): void {
        // Set up non-admin user and applicable course.
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);

        // Set up an initial course admin tree (empty list will do).
        $course_node = navigation_node::create(
            'course node title',
            'course node action',
            navigation_node::TYPE_SETTING,
            null,
            'course key'
        );
        self::assertCount(0, $course_node->get_children_key_list());

        // Execute the hook.
        $hook = new navigation_load_course_settings($course_node, $course);
        $hook->execute();

        // Check the result.
        $course_node = $hook->get_node();
        self::assertCount(0, $course_node->get_children_key_list());
    }

    public function test_navigation_load_course_settings_skips_other_formats(): void {
        // Set up admin user and non-applicable course.
        self::setAdminUser();
        $course = self::getDataGenerator()->create_course();

        // Set up an initial course admin tree (empty list will do).
        $course_node = navigation_node::create(
            'course node title',
            'course node action',
            navigation_node::TYPE_SETTING,
            null,
            'course key'
        );
        self::assertCount(0, $course_node->get_children_key_list());

        // Execute the hook.
        $hook = new navigation_load_course_settings($course_node, $course);
        $hook->execute();

        // Check the result.
        $course_node = $hook->get_node();
        self::assertCount(0, $course_node->get_children_key_list());
    }

    public function test_override_activity_self_completion_form(): void {
        self::setAdminUser();

        // Set up a non-pathway course with a module.
        $course1 = self::getDataGenerator()->create_course();
        $assign1 = $this->getDataGenerator()->create_module('assign', ['course' => $course1, 'grade' => 100, 'format' => 'pathway']);
        $cm1 = cm_info::create(get_coursemodule_from_instance('assign', $assign1->id));

        // Execute the hook.
        $hook1 = new override_activity_self_completion_form('0', $cm1, $course1);
        $hook1->set_form('fake completion form');
        $hook1->execute();

        // Check the result was unchanged.
        $form1 = $hook1->get_form();
        self::assertSame('fake completion form', $form1);

        // Now set up a pathway course with a module.
        $course2 = self::getDataGenerator()->create_course(['format' => 'pathway']);
        $assign2 = $this->getDataGenerator()->create_module('assign', ['course' => $course2, 'grade' => 100, 'format' => 'pathway']);
        $cm2 = cm_info::create(get_coursemodule_from_instance('assign', $assign2->id));

        // Execute the hook.
        $hook2 = new override_activity_self_completion_form('0', $cm2, $course2);
        $hook2->set_form('fake completion form');
        $hook2->execute();

        // Check the form was zero'd out.
        $form2 = $hook2->get_form();
        self::assertSame('', $form2);
    }

    public function test_module_available_display_options(): void {
        // Testing with course with non-pathway format.
        $course = self::getDataGenerator()->create_course();

        $available_options = [RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP, RESOURCELIB_DISPLAY_LINK, RESOURCELIB_DISPLAY_EMBED];

        // With resource module no changes occur if the course is not pathway format.
        $hook = new module_available_display_options($available_options, 'resource', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEquals($available_options, $hook->available_options);

        // With url module no changes occur if the course is not pathway format.
        $hook = new module_available_display_options($available_options, 'url', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEquals($available_options, $hook->available_options);

        // Testing with course with pathway format.
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);

        // With other module no changes occur even if the course is pathway format.
        $hook = new module_available_display_options($available_options, 'control_module_name', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEquals($available_options, $hook->available_options);

        // With resource module and pathway format the incompatible options are removed.
        $hook = new module_available_display_options($available_options, 'resource', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEqualsCanonicalizing([RESOURCELIB_DISPLAY_LINK, RESOURCELIB_DISPLAY_EMBED], $hook->available_options);

        // With resource module and pathway format the incompatible options are removed.
        $hook = new module_available_display_options($available_options, 'url', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEqualsCanonicalizing([RESOURCELIB_DISPLAY_LINK, RESOURCELIB_DISPLAY_EMBED], $hook->available_options);

        // If no compatible available options are provided with resource then display link is returned.
        $hook = new module_available_display_options([RESOURCELIB_DISPLAY_POPUP], 'resource', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEqualsCanonicalizing([RESOURCELIB_DISPLAY_LINK], $hook->available_options);

        // If no compatible available options are provided with url then display link is returned.
        $hook = new module_available_display_options([RESOURCELIB_DISPLAY_POPUP], 'url', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEqualsCanonicalizing([RESOURCELIB_DISPLAY_LINK], $hook->available_options);

        // If only embed is provided with resource then display link is not returned.
        $hook = new module_available_display_options([RESOURCELIB_DISPLAY_EMBED], 'resource', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEqualsCanonicalizing([RESOURCELIB_DISPLAY_EMBED], $hook->available_options);

        // If only embed is provided with url then display link is not returned.
        $hook = new module_available_display_options([RESOURCELIB_DISPLAY_EMBED], 'url', $course->id);
        pathway_watcher::module_available_display_options($hook);
        self::assertEqualsCanonicalizing([RESOURCELIB_DISPLAY_EMBED], $hook->available_options);
    }

    public function test_module_get_final_display_type(): void {
        // The module is set to embed, so the result should be embed or null.
        $module = (object)[
            'display' => RESOURCELIB_DISPLAY_EMBED,
        ];

        // Testing with course with non-pathway format.
        $course = self::getDataGenerator()->create_course();

        // With resource module there is no override if the course is not pathway format.
        $hook = new module_get_final_display_type('resource', $course->id, $module);
        pathway_watcher::module_get_final_display_type($hook);
        self::assertNull($hook->override_display_type);

        // With URL module there is no override if the course is not pathway format.
        $hook = new module_get_final_display_type('url', $course->id, $module);
        pathway_watcher::module_get_final_display_type($hook);
        self::assertNull($hook->override_display_type);

        // Testing with course with pathway format.
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);

        // With other module there is no override even if the course is pathway format.
        $hook = new module_get_final_display_type('control_module_name', $course->id, $module);
        pathway_watcher::module_get_final_display_type($hook);
        self::assertNull($hook->override_display_type);

        // With resource module and pathway format the override is set.
        $hook = new module_get_final_display_type('resource', $course->id, $module);
        pathway_watcher::module_get_final_display_type($hook);
        self::assertEquals(RESOURCELIB_DISPLAY_EMBED, $hook->override_display_type);

        // With url module and pathway format the override is set.
        $hook = new module_get_final_display_type('url', $course->id, $module);
        pathway_watcher::module_get_final_display_type($hook);
        self::assertEquals(RESOURCELIB_DISPLAY_EMBED, $hook->override_display_type);

        // When the module is not set to embed we always return link.
        $module = (object)[
            'display' => RESOURCELIB_DISPLAY_POPUP,
        ];
        $hook = new module_get_final_display_type('url', $course->id, $module);
        pathway_watcher::module_get_final_display_type($hook);
        self::assertEquals(RESOURCELIB_DISPLAY_LINK, $hook->override_display_type);
    }

    /**
     * This test arises from an error wherein an un-enrolled authenticated user would be redirected
     * to an unexpected activity after enrolment. This issue came about because the "next" URL was not
     * updated to reflect the user's new permissions after enrolment.
     */
    function test_redirect_to_first_activity(): void {
        global $PAGE, $DB;

        // Create a logged-in user to prevent redirection to login screen
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        self::setRequestOrigin('WEB');
        $course = $this->getDataGenerator()->create_course(['format' => 'pathway']);
        $PAGE->set_course($course);

        // We need at least two records - the first should be one that an unenrolled authorized user cannot access,
        // while the second should be one that that user has permission to view.
        $seminar_record = $this->getDataGenerator()->create_module(
            'facetoface', ['course' => $course, 'format' => 'pathway']
        );

        $page_record = $this->getDataGenerator()->create_module(
            'page', ['course' => $course, 'format' => 'pathway']
        );

        $this->assertNotEquals($seminar_record->id, $page_record->id);

        // We expect to be redirected to the enrolment page first
        try {
            pathway_watcher::redirect_to_first_activity(new course_view($course->id));
        } catch (moodle_exception $e) {
            // Need to cast to string to find int ID in string link
            $this->assertStringContainsString((string)$course->id, $e->link);
        }

        // We need to clear the static cache because the visibility is cached for each request, and
        // unit tests count as a single process, unlike the browser
        course_modinfo::clear_instance_cache($course->id);
        // After the user is enrolled, we want to be redirected to the first activity in the course
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        try {
            pathway_watcher::redirect_to_first_activity(new course_view($course->id));
        } catch (moodle_exception $e) {
            $this->assertStringContainsString((string)$seminar_record->cmid, $e->link);
        }

        // Test guest enrollment is able to see the first activity
        $course2 = $this->getDataGenerator()->create_course(['format' => 'pathway']);
        $guestplugin = enrol_get_plugin('guest');
        $this->assertNotEmpty($guestplugin);

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $guestplugin->add_instance($course2, array('status' => ENROL_INSTANCE_ENABLED,
            'name' => 'Test instance',
            'customint6' => 1,
            'roleid' => $studentrole->id));
        
        $page_record = $this->getDataGenerator()->create_module(
            'page',
            ['course' => $course2, 'format' => 'pathway']
        );
        $PAGE->set_course($course2);
        try {
            pathway_watcher::redirect_to_first_activity(new course_view($course2->id));
        } catch (moodle_exception $e) {
            $this->assertStringContainsString((string)$page_record->cmid, $e->link);
        }
    }
}
