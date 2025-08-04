<?php
//This file is part of Moodle - http://moodle.org/
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
use core_phpunit\testcase;
use bi_intellidata\helpers\StorageHelper;

/**
 * User Grade migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_usergrade_tracking_test extends testcase {

    public function setUp():void {
        self::setAdminUser();

        setup_helper::setup_tests_config();
    }

    /**
     * @covers \bi_intellidata\entities\usergrades\usergrade
     * @covers \bi_intellidata\entities\usergrades\migration
     * @covers \bi_intellidata\entities\usergrades\observer
     */
    public function test_graded() {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');

        $userdata = [
            'firstname' => 'unit test grade user',
            'username' => 'unittest_grade_user',
            'password' => 'Unittest_GradeUser1!',
        ];

        $generator = self::getDataGenerator();
        // Create User.
        $user = $generator->create_user($userdata);

        // Create Course.
        $course = $generator->create_course();

        $data = [
            'userid' => $user->id,
            'courseid' => $course->id,
        ];

        // Enrol User.
        $generator->enrol_user($user->id, $course->id);

        $gradecategory = grade_category::fetch_course_category($course->id);
        $gradecategory->load_grade_item();

        $gradeitem = $gradecategory->grade_item;
        $gradeitem->update_final_grade($user->id, 10, 'gradebook');

        $gradegrade = new grade_grade(['userid' => $user->id, 'itemid' => $gradeitem->id], true);
        $gradegrade->grade_item = $gradeitem;

        \core\event\user_graded::create_from_grade($gradegrade);

        unset($data['courseid']);

        $entity = new \bi_intellidata\entities\usergrades\usergrade($gradegrade);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'usergrades']);
        $datarecord = $storage->get_log_entity_data('u', $data);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }
}