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
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use bi_intellidata\helpers\StorageHelper;
use bi_intellidata\testing\generator;
use bi_intellidata\testing\setup_helper;
use bi_intellidata\testing\test_helper;
use core_phpunit\testcase;

defined('MOODLE_INTERNAL') || die();

/**
 * Course groups migration test case.
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_group_tracking_test extends testcase {

    public function setUp():void {
        $this->setAdminUser();

        setup_helper::setup_tests_config();
    }

    /**
     * @covers \bi_intellidata\entities\groups\group
     * @covers \bi_intellidata\entities\groups\migration
     * @covers \bi_intellidata\entities\groups\observer::group_created
     */
    public function test_create() {
        $data = [
            'fullname' => 'ibcourse1',
            'idnumber' => '1111111'
        ];

        // Create course.
        $course = generator::create_course($data);

        $gdata = [
            'name' => 'testgroup1',
            'courseid' => $course->id
        ];

        // Create group.
        $group = generator::create_group($gdata);

        $entity = new \bi_intellidata\entities\groups\group($group);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $gdata);

        $storage = StorageHelper::get_storage_service(['name' => 'coursegroups']);

        $datarecord = $storage->get_log_entity_data('c', ['id' => $group->id]);
        $this->assertNotEmpty($datarecord);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $gdata);

        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\groupmembers\groupmember
     * @covers \bi_intellidata\entities\groupmembers\migration
     * @covers \bi_intellidata\entities\groupmembers\observer::group_member_added
     */
    public function test_create_member() {
        global $DB;

        $this->test_create();

        $gdata = [
            'name' => 'testgroup1'
        ];
        $group = $DB->get_record('groups', $gdata);

        $userdata = [
            'firstname' => 'ibuser1',
            'username' => 'ibuser1',
            'password' => 'Ibuser1!'
        ];

        // Create user.
        $user = generator::create_user($userdata);

        $data = [
            'userid' => $user->id,
            'courseid' => $group->courseid
        ];

        // Enrol user.
        generator::enrol_user($data);

        $gmdata = [
            'groupid' => $group->id,
            'userid' => $user->id
        ];

        // Assign user to group.
        generator::create_group_member($gmdata);

        $groupm = $DB->get_record('groups_members', $gmdata);

        $entity = new \bi_intellidata\entities\groupmembers\groupmember($groupm);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $gmdata);

        $storage = StorageHelper::get_storage_service(['name' => 'coursegroupmembers']);

        $datarecord = $storage->get_log_entity_data('c', ['id' => $groupm->id]);
        $this->assertNotEmpty($datarecord);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $gmdata);

        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\groups\group
     * @covers \bi_intellidata\entities\groups\migration
     * @covers \bi_intellidata\entities\groups\observer::group_updated
     */
    public function test_update() {
        global $DB;

        $this->test_create_member();

        $gdata = [
            'name' => 'testgroup1'
        ];
        $group = $DB->get_record('groups', $gdata);
        $group->name = 'testgroupupdate';
        $gdata['name'] = $group->name;

        groups_update_group($group);

        $entity = new \bi_intellidata\entities\groups\group($group);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $gdata);

        $storage = StorageHelper::get_storage_service(['name' => 'coursegroups']);

        $datarecord = $storage->get_log_entity_data('u', ['id' => $group->id]);
        $this->assertNotEmpty($datarecord);

        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $gdata);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\groupmembers\groupmember
     * @covers \bi_intellidata\entities\groupmembers\migration
     * @covers \bi_intellidata\entities\groupmembers\observer::group_member_removed
     */
    public function test_delete_member() {
        global $DB;

        $this->test_update();

        $gdata = [
            'name' => 'testgroupupdate'
        ];
        $group = $DB->get_record('groups', $gdata);

        $userdata = [
            'firstname' => 'ibuser1',
            'username' => 'ibuser1'
        ];
        $user = $DB->get_record('user', $userdata);

        groups_remove_member($group->id, $user->id);

        $groupm = [
            'userid' => $user->id,
            'groupid' => $group->id,
        ];
        $entity = new \bi_intellidata\entities\groupmembers\groupmember($groupm);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $groupm);

        $storage = StorageHelper::get_storage_service(['name' => 'coursegroupmembers']);

        $datarecord = $storage->get_log_entity_data('d', $groupm);
        $this->assertNotEmpty($datarecord);

        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $groupm);

        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\groups\group
     * @covers \bi_intellidata\entities\groups\migration
     * @covers \bi_intellidata\entities\groups\observer::group_deleted
     */
    public function test_delete() {
        global $DB;

        $this->test_delete_member();

        $gdata = [
            'name' => 'testgroupupdate'
        ];
        $group = $DB->get_record('groups', $gdata);

        groups_delete_group($group);

        $entity = new \bi_intellidata\entities\groups\group($group);
        $entitydata = $entity->export();

        $storage = StorageHelper::get_storage_service(['name' => 'coursegroups']);

        $datarecord = $storage->get_log_entity_data('d', ['id' => $group->id]);
        $this->assertNotEmpty($datarecord);

        $datarecorddata = json_decode($datarecord->data);
        $this->assertEquals($entitydata->id, $datarecorddata->id);
    }
}