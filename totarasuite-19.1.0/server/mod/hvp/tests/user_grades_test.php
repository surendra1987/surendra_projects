<?php
/**
 * This file is part of Totara LMS
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
 * @package mod_hvp
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../autoloader.php');
require_once(__DIR__.'/fixtures/H5PCore_Mock.php');

class mod_hvp_user_grades_test extends \core_phpunit\testcase {

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_handle_dynamic_grading_with_invalid_token(): void {
        $token = 'fails';
        $core = new H5PCore_Mock();
        $core::setValidToken(false);
        \mod_hvp\user_grades::get_core($core);

        $course = $this->getDataGenerator()->create_course();
        $course_context = context_course::instance($course->id);

        \mod_hvp\user_grades::handle_dynamic_grading($token, $course_context->id, $course->id, 99);
        $error_found = $core::getAjaxError();
        $this->assertSame(get_string('invalidtoken', 'hvp'), $error_found);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_handle_dynamic_grading_with_invalid_contextId(): void {
        $token = 'passing';
        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\user_grades::get_core($core);

        $course = $this->getDataGenerator()->create_course();
        $course_context = context_course::instance($course->id);

        \mod_hvp\user_grades::handle_dynamic_grading($token, $course_context->id, $course->id, 99);
        $ajaxError = $core::getAjaxError();
        $this->assertSame('No such content', $ajaxError);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_handle_dynamic_grading_with_valid_record(): void {
        global $DB;

        $token = 'passing';
        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\user_grades::get_core($core);

        $course = $this->getDataGenerator()->create_course();
        $machine_name = 'test';
        $major_version = $minor_version = 1;

        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machine_name}-{$major_version}.{$minor_version}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);

        // add in the hvp_xapi_results value in here...
        $user = $this->getDataGenerator()->create_user();

        $parent_record = new stdClass();
        $parent_record->content_id = 99;
        $parent_record->user_id = $user->id;
        $parent_record->parent_id = null;
        $parent_record->interaction_type = '';
        $parent_record->description = 'testing';
        $parent_record->correct_responses_pattern = 'testing';
        $parent_record->response = '';
        $parent_record->additionals = '';
        $parent_subContentId = $DB->insert_record('hvp_xapi_results', $parent_record);

        $child_record = new stdClass();
        $child_record ->content_id = 99;
        $child_record ->user_id = $user->id;
        $child_record ->parent_id = $parent_subContentId;
        $child_record ->interaction_type = '';
        $child_record ->description = 'testing';
        $child_record ->correct_responses_pattern = 'testing';
        $child_record ->response = '';
        $child_record ->additionals = '';
        $child_subContentId = $DB->insert_record('hvp_xapi_results', $child_record );

        \mod_hvp\user_grades::handle_dynamic_grading($token, $hvp->cmid, $child_subContentId, 99);
        $response = $core::getResponse();
        $this->assertIsArray($response);
        $this->assertArrayHasKey('totalUngraded', $response);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_handle_ajax_with_invalid_token(): void {
        $token = 'passing';
        $core = new H5PCore_Mock();
        $core::setValidToken(false);
        \mod_hvp\user_grades::get_core($core);


        $course = $this->getDataGenerator()->create_course();
        $course_context = context_course::instance($course->id);

        \mod_hvp\user_grades::handle_ajax($token, $course_context->id, $course->id, 99);
        $ajaxError = $core::getAjaxError();
        $this->assertSame(get_string('invalidtoken', 'hvp'), $ajaxError);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_handle_ajax_with_invalid_contextId(): void {
        $token = 'passing';
        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\user_grades::get_core($core);

        $course = $this->getDataGenerator()->create_course();
        $course_context = context_course::instance($course->id);

        \mod_hvp\user_grades::handle_ajax($token, $course_context->id, $course->id, 99);
        $ajaxError = $core::getAjaxError();

        $this->assertSame('No such content', $ajaxError);

    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_handle_ajax_without_permission(): void {
        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\user_grades::get_core($core);

        $course = $this->getDataGenerator()->create_course();
        $machine_name = 'test';
        $major_version = $minor_version = 1;

        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machine_name}-{$major_version}.{$minor_version}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);

        $user = $this->getDataGenerator()->create_user();
        $role = $this->getDataGenerator()->create_role();
        $system_context = context_system::instance();
        assign_capability('mod/hvp:saveresults',CAP_PREVENT, $role, $system_context->id, true);
        $this->getDataGenerator()->role_assign($role, $user->id);
        $this->setUser($user);
        $token = 'passing';

        \mod_hvp\user_grades::handle_ajax($token, $hvp->cmid, $course->id, 99);
        $ajaxError = $core::getAjaxError();
        $this->assertSame(get_string('nopermissiontosaveresult', 'hvp'), $ajaxError);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_handle_ajax_with_valid_record(): void {
        global $DB;

        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\user_grades::get_core($core);

        $course = $this->getDataGenerator()->create_course();
        $machine_name = 'test';
        $major_version = $minor_version = 1;

        // add record to hvp_libraries
        $hvp_library = new stdClass();
        $hvp_library->machine_name = $machine_name;
        $hvp_library->title = 'test_library';
        $hvp_library->major_version = $major_version;
        $hvp_library->minor_version = $minor_version;
        $hvp_library->patch_version = 0;
        $hvp_library->runnable = 1;
        $hvp_library->fullscreen = 1;
        $hvp_library->embed_types = '';
        $hvp_library->semantics = '';
        $hvp_library_id = $DB->insert_record('hvp_libraries', $hvp_library);

        // add hvp record
        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machine_name}-{$major_version}.{$minor_version}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->main_library_id = $hvp_library_id;
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);

        $user = $this->getDataGenerator()->create_user();
        $role = $this->getDataGenerator()->create_role();
        $system_context = context_system::instance();
        assign_capability('mod/hvp:saveresults',CAP_ALLOW, $role, $system_context->id, true);
        $this->getDataGenerator()->role_assign($role, $user->id);
        $this->setUser($user);
        $token = 'passing';

        $sink = $this->redirectEvents();
        $sink->clear();
        $this->assertSame(0, $sink->count());

        \mod_hvp\user_grades::handle_ajax($token, $hvp->cmid, 10, 99);

        $this->assertSame(1, $sink->count());
        $events = $sink->get_events();
        $first_event = array_pop($events);
        $this->assertInstanceOf(\mod_hvp\event\attempt_submitted::class, $first_event);
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_return_subcontent_grade(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\user_grades::get_core($core);

        $parent_record = new stdClass();
        $parent_record->content_id = 99;
        $parent_record->user_id = $user->id;
        $parent_record->parent_id = null;
        $parent_record->interaction_type = '';
        $parent_record->description = 'testing';
        $parent_record->correct_responses_pattern = 'testing';
        $parent_record->response = '';
        $parent_record->additionals = '';
        $parent_record->raw_score = 10;
        $parent_record->max_score = 99;
        $parent_subContentId = $DB->insert_record('hvp_xapi_results', $parent_record);

        \mod_hvp\user_grades::return_subcontent_grade($parent_subContentId);
        $response = $core::getResponse();
        $this->assertEquals(10, $response['score']);
        $this->assertEquals(99, $response['maxScore']);
        $this->assertEquals(0, $response['totalUngraded']);
    }


    protected function tearDown(): void {
        H5PCore_Mock::setValidToken(false);
        H5PCore_Mock::ajaxError(null);
        H5PCore_Mock::ajaxSuccess(null);
        parent::tearDown();
    }

}