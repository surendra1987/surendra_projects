<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use bi_intellidata\testing\setup_helper;
use bi_intellidata\testing\test_helper;
use bi_intellidata\helpers\StorageHelper;
use core_phpunit\testcase;

global $CFG;
require_once($CFG->dirroot . '/lib/completionlib.php');

/**
 * Activity Completion migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_activitycompletion_tracking_test extends testcase {

    public function setUp():void {
        self::setAdminUser();

        setup_helper::setup_tests_config();
    }

    /**
     * @covers \bi_intellidata\entities\activitycompletions\activitycompletion
     * @covers \bi_intellidata\entities\activitycompletions\migration
     * @covers \bi_intellidata\entities\activitycompletions\observer::course_module_completion_updated
     */
    public function test_update() {
        $userdata = [
            'firstname' => 'ibactivitycompletionuser1',
            'username' => 'ibactivitycompletionuser1',
            'password' => 'Ibactivitycompletionuser1!',
        ];

        $gen = self::getDataGenerator();
        // Create User.
        $user = $gen->create_user($userdata);

        $coursedata = [
            'fullname' => 'ibcourseactivity1',
        ];

        // Create Course.
        $course = $gen->create_course($coursedata);

        // Create Module.
        $page = $gen->create_module('page', ['course' => $course->id]);

        $data = [
            "coursemoduleid" => $page->cmid,
            "userid" => $user->id
        ];

        $idata = (object)$data;
        $idata->id = 0;
        $idata->completionstate = COMPLETION_COMPLETE;
        $idata->timemodified = time();
        $idata->viewed = COMPLETION_NOT_VIEWED;
        $idata->timecompleted = null;
        $idata->reaggregate = 0;
        $idata->overrideby = 0;
        $idata->progress = 10;

        $c = new completion_info($course);
        $c->internal_set_data($page, $idata);

        $entity = new \bi_intellidata\entities\activitycompletions\activitycompletion((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'activitycompletions']);
        $datarecord = $storage->get_log_entity_data('u', $data);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }
}