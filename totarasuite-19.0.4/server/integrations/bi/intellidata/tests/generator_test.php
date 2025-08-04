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
 * PHPUnit data generator tests.
 *
 * @package    bi_intellidata
 * @copyright  2022 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */


use bi_intellidata\persistent\tracking;
use bi_intellidata\testing\generator;
use core_phpunit\testcase;

/**
 * PHPUnit data generator testcase
 * @group bi_intellidata
 *
 * @package    bi_intellidata
 * @copyright  2022 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */
class bi_intellidata_generator_test extends testcase {

    /**
     * Test tracking generator.
     *
     * @return void
     * @covers \bi_intellidata\testing\generator::create_tracking
     */
    public function test_tracking_generator() {
        self::setAdminUser();

        // Validate empty table.
        $this->assertEquals(0, tracking::count_records());

        // Validate generator instance.
        $generator = generator::instance();
        $this->assertInstanceOf('bi_intellidata\testing\generator', $generator);

        // Create user.
        $userdata = [
            'firstname' => 'unit test create user',
            'username' => 'unittest_create_user',
            'password' => 'Unittest_User1!',
        ];
        $user = self::getDataGenerator()->create_user($userdata);

        // Create tracking.
        $tracking = $generator->create_tracking(['userid' => $user->id]);

        $this->assertEquals(1, tracking::count_records());

        $savedtracking = tracking::get_record(['id' => $tracking->id]);
        $this->assertEquals($savedtracking->get('userid'), $user->id);
    }
}