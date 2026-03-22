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


use bi_intellidata\helpers\StorageHelper;
use bi_intellidata\testing\setup_helper;
use bi_intellidata\testing\test_helper;
use container_course\course_helper;
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

class bi_intellidata_course_tracking_test extends testcase {

    public function setUp():void {
        self::setAdminUser();

        setup_helper::setup_tests_config();
    }

    /**
     * @covers \bi_intellidata\entities\courses\course
     * @covers \bi_intellidata\entities\courses\migration
     * @covers \bi_intellidata\entities\courses\observer::course_created
     */
    public function test_create() {
        $data = [
            'fullname' => 'ibcourse1',
            'idnumber' => '1111111',
        ];

        $gen = self::getDataGenerator();
        // Create course.
        $course = $gen->create_course($data);

        $entity = new \bi_intellidata\entities\courses\course($course);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'courses']);

        $datarecord = $storage->get_log_entity_data('c', ['id' => $course->id]);
        $this->assertNotEmpty($datarecord);

        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\courses\course
     * @covers \bi_intellidata\entities\courses\migration
     * @covers \bi_intellidata\entities\courses\observer::course_updated
     */
    public function test_update() {
        global $DB;

        $this->test_create();

        $data = [
            'fullname' => 'ibcourse1'
        ];

        $course = $DB->get_record('course', $data);
        $course->idnumber = '2222222';
        $data['idnumber'] = $course->idnumber;

        course_helper::update_course($course->id, $course);

        $entity = new \bi_intellidata\entities\courses\course($course);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'courses']);

        $datarecord = $storage->get_log_entity_data('u', ['id' => $course->id]);
        $this->assertNotEmpty($datarecord);

        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\courses\course
     * @covers \bi_intellidata\entities\courses\migration
     * @covers \bi_intellidata\entities\courses\observer::course_deleted
     */
    public function test_delete() {
        global $DB;

        $this->test_create();

        $data = [
            'fullname' => 'ibcourse1'
        ];

        $course = $DB->get_record('course', $data);

        delete_course($course, false);

        $entity = new \bi_intellidata\entities\courses\course($course);
        $entitydata = $entity->export();

        $storage = StorageHelper::get_storage_service(['name' => 'courses']);

        $datarecord = $storage->get_log_entity_data('d', ['id' => $course->id]);
        $this->assertNotEmpty($datarecord);

        $datarecorddata = json_decode($datarecord->data);
        $this->assertEquals($entitydata->id, $datarecorddata->id);
    }
}