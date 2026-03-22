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
require_once(__DIR__ . '/fixtures/H5PCore_Mock.php');

class mod_hvp_xapi_result_test extends \core_phpunit\testcase {

    /**
     * @return void
     */
    public function test_handle_ajax_invalid_token(): void {
        $token = 'testToken';

        $core = new H5PCore_Mock();
        \mod_hvp\xapi_result::get_core($core);
        \mod_hvp\xapi_result::handle_ajax($token, 1, 'test');
        $error = $core::getAjaxError();
        $this->assertSame('Invalid security token.', $error);
    }

    /**
     * @return void
     */
    public function test_handle_ajax_invalid_context(): void {
        $token = 'test_token';

        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\xapi_result::get_core($core);
        \mod_hvp\xapi_result::handle_ajax($token, 1, 'test');
        $error = $core::getAjaxError();
        $this->assertSame('No such content', $error);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_handle_ajax_without_required_capability(): void {
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
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);

        $user = $this->getDataGenerator()->create_user();
        $role = $this->getDataGenerator()->create_role();
        $mod_context = context_module::instance($cm->id);
        assign_capability('mod/hvp:saveresults', CAP_PREVENT, $role, $mod_context->id, true);
        $this->getDataGenerator()->role_assign($role, $user->id, $mod_context->id);
        $this->setUser($user);

        $token = 'test_token';

        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\xapi_result::get_core($core);
        \mod_hvp\xapi_result::handle_ajax($token, $cm->id, 'test');
        $error = $core::getAjaxError();
        $this->assertSame(get_string('nopermissiontosaveresult', 'hvp'), $error);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_handle_ajax_invalid_json_result(): void {
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
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);

        $user = $this->getDataGenerator()->create_user();
        $role = $this->getDataGenerator()->create_role();
        $mod_context = context_module::instance($cm->id);
        assign_capability('mod/hvp:saveresults', CAP_ALLOW, $role, $mod_context->id, true);
        $this->getDataGenerator()->role_assign($role, $user->id, $mod_context->id);
        $this->setUser($user);

        $token = 'test_token';

        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\xapi_result::get_core($core);
        \mod_hvp\xapi_result::handle_ajax($token, $cm->id, 'test');
        $error = $core::getAjaxError();
        $this->assertSame('Invalid json in xAPI data.', $error);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_handle_ajax_invalid_xapi_data(): void {
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
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);

        $user = $this->getDataGenerator()->create_user();
        $role = $this->getDataGenerator()->create_role();
        $mod_context = context_module::instance($cm->id);
        assign_capability('mod/hvp:saveresults', CAP_ALLOW, $role, $mod_context->id, true);
        $this->getDataGenerator()->role_assign($role, $user->id, $mod_context->id);
        $this->setUser($user);

        $token = 'test_token';

        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\xapi_result::get_core($core);
        $xapi_result = json_encode('testing');
        \mod_hvp\xapi_result::handle_ajax($token, $cm->id, $xapi_result);
        $error = $core::getAjaxError();
        $this->assertSame('Invalid xAPI data.', $error);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_handle_ajax_valid_api_data(): void {
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
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);

        $user = $this->getDataGenerator()->create_user();
        $role = $this->getDataGenerator()->create_role();
        $mod_context = context_module::instance($cm->id);
        assign_capability('mod/hvp:saveresults', CAP_ALLOW, $role, $mod_context->id, true);
        $this->getDataGenerator()->role_assign($role, $user->id, $mod_context->id);
        $this->setUser($user);

        $token = 'test_token';

        $core = new H5PCore_Mock();
        $core::setValidToken(true);
        \mod_hvp\xapi_result::get_core($core);

        $data = new stdClass();
        $statement = new stdClass();
        $statement->object = new stdClass();
        $statement->object->definition = new stdClass();
        $statement->object->definition->interactionType = 'test';
        $data->statement = $statement;

        $xapi_result = json_encode($data);

        \mod_hvp\xapi_result::handle_ajax($token, $cm->id, $xapi_result);
        $error = $core::getAjaxError();
        $this->assertNull($error);
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        H5PCore_Mock::setValidToken(false);
        H5PCore_Mock::ajaxSuccess(null);
        H5PCore_Mock::ajaxError(null);
        parent::tearDown();
    }
}