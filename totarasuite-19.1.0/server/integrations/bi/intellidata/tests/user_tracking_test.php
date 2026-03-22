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
use bi_intellidata\testing\generator;
use bi_intellidata\testing\setup_helper;
use bi_intellidata\testing\test_helper;
use core_phpunit\testcase;

/**
 * User migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_user_tracking_test extends testcase {
    public function setUp():void {
        $this->setAdminUser();
        setup_helper::setup_tests_config();
    }

    /**
     * @covers \bi_intellidata\entities\users\user
     * @covers \bi_intellidata\entities\users\migration
     * @covers \bi_intellidata\entities\users\observer::user_created
     */
    public function test_create() {
        $data = [
            'firstname' => 'unit test create user',
            'username' => 'unittest_create_user',
            'password' => 'Unittest_User1!',
        ];

        // Create user.
        $user = generator::instance()->create_user_with_event_trigger($data);

        $entity = new \bi_intellidata\entities\users\user($user);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'users']);
        $datarecord = $storage->get_log_entity_data('c', ['id' => $user->id]);

        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\users\user
     * @covers \bi_intellidata\entities\users\migration
     * @covers \bi_intellidata\entities\users\observer::user_updated
     */
    public function test_update() {
        global $DB;

        $this->test_create();

        $data = [
            'username' => 'unittest_create_user'
        ];

        $user = $DB->get_record('user', $data);
        $user->firstname = 'unit test update user';
        $data['firstname'] = $user->firstname;

        user_update_user($user, false);

        $entity = new \bi_intellidata\entities\users\user($user);
        $entitydata = $entity->export();
        $entitydata = test_helper::filter_fields($entitydata, $data);

        $storage = StorageHelper::get_storage_service(['name' => 'users']);
        $datarecord = $storage->get_log_entity_data('u', ['id' => $user->id]);
        $datarecorddata = test_helper::filter_fields(json_decode($datarecord->data), $data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata, $datarecorddata);
    }

    /**
     * @covers \bi_intellidata\entities\users\user
     * @covers \bi_intellidata\entities\users\migration
     * @covers \bi_intellidata\entities\users\observer::user_deleted
     */
    public function test_delete() {
        global $DB;

        $this->test_create();

        $data = [
            'username' => 'unittest_create_user'
        ];

        $user = $DB->get_record('user', $data);

        user_delete_user($user);

        $entity = new \bi_intellidata\entities\users\user($user);
        $entitydata = $entity->export();

        $storage = StorageHelper::get_storage_service(['name' => 'users']);
        $datarecord = $storage->get_log_entity_data('d', ['id' => $user->id]);

        $datarecorddata = json_decode($datarecord->data);

        $this->assertNotEmpty($datarecord);
        $this->assertEquals($entitydata->id, $datarecorddata->id);
    }
}