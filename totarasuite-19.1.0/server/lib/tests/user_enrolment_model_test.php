<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core
 */

defined('MOODLE_INTERNAL') || die();

use core\model\enrol;
use core_phpunit\testcase;
use core\model\user_enrolment;

/**
 * @coversDefaultClass \core\model\user_enrolment
 */
class core_user_enrolment_model_test extends testcase {

    /**
     * @covers ::find_by_instance_and_user
     */
    public function test_find_by_instance_and_user() {
        $generator = $this->getDataGenerator();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        $course1 = $generator->create_course();
        $user4 = $generator->create_and_enrol($course1);

        $course2 = $generator->create_course();

        // Add two self-enrolment instances to course2.
        $enrol = enrol_get_plugin('self');
        $instance1_id = $enrol->add_instance($course2, []);
        $instance2_id = $enrol->add_instance($course2, []);

        $instance1 = enrol::load_by_id($instance1_id);
        $instance2 = enrol::load_by_id($instance2_id);

        // Enrol user1 on instance1.
        $enrol->enrol_user($instance1->to_stdClass(), $user1->id);

        // Enrol user3 on both instances.
        $enrol->enrol_user($instance1->to_stdClass(), $user3->id);
        $enrol->enrol_user($instance2->to_stdClass(), $user3->id);

        // Test with user on one instance.
        $result1 = user_enrolment::find_by_instance_and_user($instance1->id, $user1->id);
        $this->assertInstanceOf(user_enrolment::class, $result1);
        $this->assertEquals($instance1->id, $result1->enrolid);
        $this->assertEquals($user1->id, $result1->userid);

        // Test with user on zero instances.
        $result2 = user_enrolment::find_by_instance_and_user($instance1->id, $user2->id);
        $this->assertNull($result2);

        // Test with user on two instances.
        $result3 = user_enrolment::find_by_instance_and_user($instance2->id, $user3->id);
        $this->assertInstanceOf(user_enrolment::class, $result3);
        $this->assertEquals($instance2->id, $result3->enrolid);
        $this->assertEquals($user3->id, $result3->userid);
    }
}