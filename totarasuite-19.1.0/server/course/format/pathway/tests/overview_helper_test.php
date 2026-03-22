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

use core_phpunit\testcase;
use format_pathway\overview_helper;

/**
 * @group format_pathway
 */
class format_pathway_overview_helper_test extends testcase {

    /**
     * test the first visible cm in the first visible section is returned
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_first_course_module_for_user(): void {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        $this->setAdminUser();
        // setup sections
        $section1 = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        set_section_visible($course->id, 1, 0);

        $section2 = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 2]);
        set_section_visible($course->id, 2, 1);

        $section3 = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 3]);
        set_section_visible($course->id, 3, 1);

        // setup the course modules within their sections
        $intro_raw = '<p>I am description</p><style type=\'text/css\'>body {background-color:red !important;}</style>';
        $forumHidden = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1, // '1' here means first section after the '0' section.
            'shortname' => 'Test forum',
            'idnumber' => 'test_forum',
            'introeditor' => [
                'text' => $intro_raw,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            ],
            'showdescription' => 1
        ]);

        set_coursemodule_visible($forumHidden->cmid, 0);


        $forumVisibleSectionHiddenCm = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 3,// '1' here means first section after the '0' section.
            'shortname' => 'Test forum',
            'idnumber' => 'test_forum',
            'introeditor' => [
                'text' => $intro_raw,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            ],
            'showdescription' => 1
        ]);
        set_coursemodule_visible($forumVisibleSectionHiddenCm->cmid, 0);

        $forumVisibleSectionVisibleCm = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 3,// '1' here means first section after the '0' section.
            'shortname' => 'Test forum',
            'idnumber' => 'test_forum',
            'introeditor' => [
                'text' => $intro_raw,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            ],
            'showdescription' => 1
        ]);
        set_coursemodule_visible($forumVisibleSectionVisibleCm->cmid, 1);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        // create a module
        $this->setUser($user);

        // the first visible section with a visible course module should be section 3, the second course module in the section
        $first_cm_id = \format_pathway\overview_helper::get_first_course_module_for_user($course->id, $user->id);
        $this->assertEquals($forumVisibleSectionVisibleCm->cmid, $first_cm_id->id);


    }

    /**
     * Test against users which do/don't have capability to view hidden sections
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_first_cm_cap_hidden_sections(): void {
        $user = $this->getDataGenerator()->create_user();
        $intro_raw = '<p>I am description</p><style type=\'text/css\'>body {background-color:red !important;}</style>';
        $course = $this->getDataGenerator()->create_course();

        $this->setAdminUser();
        // setup sections
        $section1 = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        $forumVisibleSectionHiddenCm = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,// '1' here means first section after the '0' section.
            'shortname' => 'Test forum',
            'idnumber' => 'test_forum',
            'introeditor' => [
                'text' => $intro_raw,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            ],
            'showdescription' => 1
        ]);
        set_coursemodule_visible($forumVisibleSectionHiddenCm->cmid, 0);

        // set the section to hidden - but add a visible cm created after - this func will reset if done after
        set_section_visible($course->id, 1, 0);

        $forumVisibleSectionVisibleCm = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,// '1' here means first section after the '0' section.
            'shortname' => 'Test forum 2',
            'idnumber' => 'test_forum_2',
            'introeditor' => [
                'text' => $intro_raw,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            ],
            'showdescription' => 1
        ]);
        set_coursemodule_visible($forumVisibleSectionVisibleCm->cmid, 1);

        // set a user with the correct capability
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $user_role = $this->getDataGenerator()->create_role();
        $course_context = context_course::instance($course->id);
        assign_capability('moodle/course:viewhiddensections', CAP_ALLOW, $user_role, $course_context->id, true);
        $this->getDataGenerator()->role_assign($user_role, $user->id, $course_context->id);
        $first_cm_id = \format_pathway\overview_helper::get_first_course_module_for_user($course->id, $user->id);
        $this->assertEquals($forumVisibleSectionVisibleCm->cmid, $first_cm_id->id);
    }

    /**
     * Check users with capability to view hidden course modules will receive those first
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_first_cm_cap_hidden_cms(): void {
        $user = $this->getDataGenerator()->create_user();
        $intro_raw = '<p>I am description</p><style type=\'text/css\'>body {background-color:red !important;}</style>';
        $course = $this->getDataGenerator()->create_course();

        $this->setAdminUser();
        // setup sections
        $section1 = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        $forumVisibleSectionHiddenCm = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,// '1' here means first section after the '0' section.
            'shortname' => 'Test forum',
            'idnumber' => 'test_forum',
            'introeditor' => [
                'text' => $intro_raw,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            ],
            'showdescription' => 1
        ]);
        set_coursemodule_visible($forumVisibleSectionHiddenCm->cmid, 0);

        $forumVisibleSectionVisibleCm = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,// '1' here means first section after the '0' section.
            'shortname' => 'Test forum 2',
            'idnumber' => 'test_forum_2',
            'introeditor' => [
                'text' => $intro_raw,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            ],
            'showdescription' => 1
        ]);
        set_coursemodule_visible($forumVisibleSectionVisibleCm->cmid, 1);

        // set the section to hidden - but add a visible cm created after - this func will reset if done after
        set_section_visible($course->id, 1, 1);

        // set a user with the correct capability - should be able to view the first cm regardless of it being hidden
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $user_role = $this->getDataGenerator()->create_role();
        $course_context = context_course::instance($course->id);
        assign_capability('moodle/course:viewhiddenactivities', CAP_ALLOW, $user_role, $course_context->id, true);
        $this->getDataGenerator()->role_assign($user_role, $user->id, $course_context->id);
        $first_cm_id = \format_pathway\overview_helper::get_first_course_module_for_user($course->id, $user->id);
        $this->assertEquals($forumVisibleSectionHiddenCm->cmid, $first_cm_id->id);
    }

    /**
     * Check that if the user doens't have access to any cm then null should be returned
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_first_course_module_for_user_no_visible_cm(): void {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        $this->setAdminUser();
        // setup sections
        $section1 = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        set_section_visible($course->id, 1, 0);

        $section2 = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 2]);
        set_section_visible($course->id, 2, 1);

        $section3 = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 3]);
        set_section_visible($course->id, 3, 1);

        // setup the course modules within their sections
        $intro_raw = '<p>I am description</p><style type=\'text/css\'>body {background-color:red !important;}</style>';
        $forumHidden = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1, // '1' here means first section after the '0' section.
            'shortname' => 'Test forum',
            'idnumber' => 'test_forum',
            'introeditor' => [
                'text' => $intro_raw,
                'format' => FORMAT_MOODLE,
                'itemid' => 0,
            ],
            'showdescription' => 1
        ]);

        set_coursemodule_visible($forumHidden->cmid, 0);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        // create a module
        $this->setUser($user);

        // there is no visible course modules so there is no first cm for the user
        $first_cm_id = \format_pathway\overview_helper::get_first_course_module_for_user($course->id, $user->id);
        $this->assertNull($first_cm_id);
    }

    /**
     * Check that blacklisted cms are excluded for users who cannot manage sections and activities.
     *
     * @return void
     * @throws coding_exception
     */
    public function test_get_first_course_module_with_blacklisted_cm(): void {
        // Create some stuff.
        $user = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);

        // Blacklisted cm is first!
        $blacklisted_module = self::getDataGenerator()->create_module('book', array('course' => $course));
        $blacklisted_cm = get_coursemodule_from_id(null, $blacklisted_module->cmid);

        // Non-blacklisted cm is second.
        $valid_module = self::getDataGenerator()->create_module('choice', array('course' => $course));
        $valid_cm = get_coursemodule_from_id(null, $valid_module->cmid);

        // Admin sees the blacklisted cm first.
        $this->setAdminUser();
        $first_cm = overview_helper::get_first_course_module_for_user($course->id, $user->id);
        $this->assertEquals($blacklisted_cm->id, $first_cm->id);

        // User who cannot manage sections and activities doesn't see the blacklisted cm, they see the second cm instead.
        $this->setUser($user);
        $first_cm = overview_helper::get_first_course_module_for_user($course->id, $user->id);
        $this->assertEquals($valid_cm->id, $first_cm->id);
    }

    /**
     * Check the page is tested to ensure the coure overview page is being used - as the calling function (pathway/lib.php) is called
     * when viewing an activity as well
     *
     * @return void
     * @throws coding_exception
     */
    public function test_is_page_course_view(): void {
        global $PAGE;
        $page = new moodle_page();
        $valid = \format_pathway\overview_helper::is_page_course_view($page);
        $this->assertFalse($valid);

        $page->set_url('/course/view.php?id=1');
        $PAGE = $page;
        $valid = \format_pathway\overview_helper::is_page_course_view($page);
        $this->assertTrue($valid);
    }

    /**
     * Check the request to edit the page is recognised by the helper
     *
     * @return void
     * @throws coding_exception
     */
    public function test_is_request_to_edit_page(): void {
        global $USER;
        $USER->ignoresesskey = true;
        $is_edit_request = \format_pathway\overview_helper::is_request_to_edit_page();
        $this->assertFalse($is_edit_request);
        $_POST['edit'] = 1;

        $is_edit_request = \format_pathway\overview_helper::is_request_to_edit_page();
        $this->assertTrue($is_edit_request);
    }

    /**
     * check if the current user is in editing mode
     *
     * @return void
     */
    public function test_is_user_editing(): void {
        global $USER;
        $USER->editing = false;
        $user_editing = \format_pathway\overview_helper::is_user_editing();
        $this->assertFalse($user_editing);
        $USER->editing = true;
        $user_editing = \format_pathway\overview_helper::is_user_editing();
        $this->assertTrue($user_editing);
    }

}