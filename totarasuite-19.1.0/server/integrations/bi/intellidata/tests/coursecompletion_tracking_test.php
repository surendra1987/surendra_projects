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

/**
 * Cohort migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_coursecompletion_tracking_test extends testcase {

    public function setUp():void {
        self::setAdminUser();

        setup_helper::setup_tests_config();
    }

    /**
     * @covers \bi_intellidata\entities\coursecompletions\coursecompletion
     * @covers \bi_intellidata\entities\coursecompletions\migration
     * @covers \bi_intellidata\entities\coursecompletions\observer::course_completed
     */
    public function test_create() {
        $userdata = [
            'firstname' => 'ibuser1',
            'username' => 'ibuser1',
            'password' => 'Ibuser1!',
        ];

        $gen = self::getDataGenerator();
        $user = $gen->create_user($userdata);

        $coursedata = [
            'fullname' => 'ibcoursecompletion1',
            'idnumber' => '1111111',
            'enablecompletion' => true
        ];

        $course = $gen->create_course($coursedata);

        $data = [
            'userid' => $user->id,
            'course' => $course->id,
        ];

        // Create coursecompletion.
        $coursecompletion = new completion_completion($data);
        $coursecompletion->mark_complete(time());

        unset($data['course']);
        $data['courseid'] = $course->id;

        $entity = new \bi_intellidata\entities\coursecompletions\coursecompletion((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'coursecompletions']);
        $datarecord = $storage->get_log_entity_data('u', $data);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\coursecompletions\coursecompletion
     * @covers \bi_intellidata\entities\coursecompletions\migration
     * @covers \bi_intellidata\entities\coursecompletions\observer::course_completion_updated
     */
    public function test_update() {
        global $DB;

        $this->test_create();


        $coursedata = [
            'fullname' => 'ibcoursecompletion1',
            'idnumber' => '1111111',
        ];

        $course = $DB->get_record('course', $coursedata);

        $data = [
            'courseid' => $course->id,
            'context' => context_course::instance($course->id),
        ];

        \core\event\course_completion_updated::create($data)->trigger();

        unset($data['context']);

        $entity = new \bi_intellidata\entities\coursecompletions\coursecompletion((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'coursecompletions']);
        $datarecord = $storage->get_log_entity_data('u', $data);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }
}