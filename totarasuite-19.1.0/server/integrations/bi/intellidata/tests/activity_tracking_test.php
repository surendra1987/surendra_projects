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


use bi_intellidata\testing\generator;
use bi_intellidata\testing\setup_helper;
use bi_intellidata\testing\test_helper;
use core_phpunit\testcase;
use bi_intellidata\helpers\StorageHelper;

/**
 * Activity migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_activity_tracking_test extends testcase {

    public function setUp():void {
        self::setAdminUser();

        setup_helper::setup_tests_config();
    }

    /**
     * @covers \bi_intellidata\entities\activities\activity
     * @covers \bi_intellidata\entities\activities\migration
     * @covers \bi_intellidata\entities\activities\observer::course_module_created
     */
    public function test_create() {
        $coursedata = [
            'fullname' => 'ibcourseactivity1',
            'idnumber' => '1111111',
        ];

        $course = generator::create_course($coursedata);

        $page = generator::create_module('page', ['course' => $course->id]);

        $data = [
            'courseid' => $course->id,
            'id' => $page->cmid,
        ];

        $entity = new \bi_intellidata\entities\activities\activity((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'activities']);
        $datarecord = $storage->get_log_entity_data('c', $data);

        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\activities\activity
     * @covers \bi_intellidata\entities\activities\migration
     * @covers \bi_intellidata\entities\activities\observer::course_module_updated
     */
    public function test_update() {
        global $DB;

        $this->test_create();

        $coursedata = [
            'fullname' => 'ibcourseactivity1',
            'idnumber' => '1111111',
        ];

        $course = $DB->get_record('course', $coursedata);

        $gen = self::getDataGenerator();
        $page = $gen->create_module('page', ['course' => $course->id]);

        $data = [
            'courseid' => $course->id,
            'id' => $page->cmid,
        ];

        set_coursemodule_name($data['id'], 'modulename');

        $entity = new \bi_intellidata\entities\activities\activity((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'activities']);
        $datarecord = $storage->get_log_entity_data('u', $data);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\activities\activity
     * @covers \bi_intellidata\entities\activities\migration
     * @covers \bi_intellidata\entities\activities\observer::course_module_deleted
     */
    public function test_delete() {
        global $DB;

        $this->test_create();

        $coursedata = [
            'fullname' => 'ibcourseactivity1',
            'idnumber' => '1111111',
        ];

        $course = $DB->get_record('course', $coursedata);
        $gen = self::getDataGenerator();
        $page = $gen->create_module('page', ['course' => $course->id]);

        $data = [
            'id' => $page->cmid,
        ];

        course_delete_module($page->cmid);

        $entity = new \bi_intellidata\entities\activities\activity((object)$data);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'activities']);
        $datarecord = $storage->get_log_entity_data('d');
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata->id, $datarecorddata->id);
    }
}