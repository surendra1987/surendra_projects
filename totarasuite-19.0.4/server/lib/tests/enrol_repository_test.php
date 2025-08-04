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

use core\entity\enrol as enrol_entity;
use core\entity\enrol_repository;
use core\model\enrol;
use core_phpunit\testcase;

/**
 * @coversDefaultClass \core\entity\enrol_repository
 */
class core_enrol_repository_test extends testcase {

    /**
     * @covers ::find_by_enrol_and_course
     */
    public function test_find_by_enrol_and_course() {
        $generator = $this->getDataGenerator();

        $course1 = $generator->create_course();
        $course2 = $generator->create_course();

        // Add two additional self-enrolment instances to course2.
        $enrol = enrol_get_plugin('self');
        $instance1_id = $enrol->add_instance($course2, []);
        $instance2_id = $enrol->add_instance($course2, []);

        // Test with one instance.
        $result1 = enrol_entity::repository()->find_by_enrol_and_course('self', $course1->id);
        $this->assertEquals(1, $result1->count());
        $enrol_entity1 = $result1->first();
        $this->assertInstanceOf(enrol_entity::class, $enrol_entity1);
        $this->assertEquals($course1->id, $enrol_entity1->courseid);
        $this->assertEquals('self', $enrol_entity1->enrol);

        // Test with multiple instances.
        $result2 = enrol_entity::repository()->find_by_enrol_and_course('self', $course2->id);
        $this->assertEquals(3, $result2->count());

        // Test with zero instances.
        $result3 = enrol_entity::repository()->find_by_enrol_and_course('flatfile', $course2->id);
        $this->assertEquals(0, $result3->count());

        // Test with fictitious enrolment plugin.
        $result4 = enrol_entity::repository()->find_by_enrol_and_course('pigeon', $course2->id);
        $this->assertEquals(0, $result4->count());
    }
}