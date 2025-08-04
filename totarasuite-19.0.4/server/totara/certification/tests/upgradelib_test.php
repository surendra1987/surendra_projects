<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Maria Torres <maria.torres@totara.com>
 * @package totara_certification
 */

use core_phpunit\testcase;
use core_completion\helper;

/**
 * @group totara_certification
 */
class totara_certification_upgradelib_test extends testcase {
    /**
     * @return void
     */
    protected function setUp(): void {
        global $CFG;

        require_once($CFG->libdir . '/gradelib.php');
        require_once("{$CFG->dirroot}/totara/certification/db/upgradelib.php");
    }

    /**
     * Test upgrade of course_completion_history grade columns is perform correctly and
     * the grade fields are filled correctly for the corresponding courses.
     */
    public function test_totara_certification_upgrade_course_completion_history_grade_columns(): void {
        global $DB;

        $completion_generator = $this->getDataGenerator()->get_plugin_generator('core_completion');

        // Create a couple of courses.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        // Add module assign to the course.
        $assignment = $this->getDataGenerator()->create_module('assign', ['course' => $course1->id, 'name' => 'Assignment 1']);

        // Completion of course1 is the criteria for completion of assignment activity.
        $completion_generator->set_activity_completion($course1->id, [$assignment]);

        // Get grademax and grademin for the course.
        $coursegi = grade_item::fetch_course_item($course1->id);
        $this->assertEquals(100,$coursegi->grademax);
        $this->assertEquals(0,$coursegi->grademin);

        // Insert grade for course2.
        $item = new stdClass();
        $item->courseid = $course2->id;
        $item->itemtype = 'course';
        $item->gradetype = 1;
        $item->grademin = 10;
        $item->grademax = 80;
        $item->id = $DB->insert_record('grade_items', $item);

        $course2gi = grade_item::fetch_course_item($course2->id);
        $this->assertEquals(80,$course2gi->grademax);
        $this->assertEquals(10,$course2gi->grademin);

        // Create users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Create course completion historic records.
        $cch = new stdClass();
        $cch->timecompleted = 123;
        $cch->courseid = $course1->id;
        $cch->userid = $user1->id;
        $cch->grade = 80;
        $this->assertNotFalse(helper::write_course_completion_history($cch));
        $params = ['courseid' => $course1->id, 'userid' => $user1->id, 'timecompleted' => 123, 'grade' => 80];
        $this->assertEquals(1, $DB->count_records('course_completion_history', $params));

        $cch = new stdClass();
        $cch->timecompleted = 123;
        $cch->courseid = $course1->id;
        $cch->userid = $user2->id;
        $cch->grade = 70;
        $this->assertNotFalse(helper::write_course_completion_history($cch));
        $params = ['courseid' => $course1->id, 'userid' => $user2->id, 'timecompleted' => 123, 'grade' => 70];
        $this->assertEquals(1, $DB->count_records('course_completion_history', $params));

        $cch = new stdClass();
        $cch->timecompleted = 123;
        $cch->courseid = $course2->id;
        $cch->userid = $user1->id;
        $cch->grade = 90;
        $this->assertNotFalse(helper::write_course_completion_history($cch));
        $params = ['courseid' => $course2->id, 'userid' => $user1->id, 'timecompleted' => 123, 'grade' => 90];
        $this->assertEquals(1, $DB->count_records('course_completion_history', $params));


        // Upgrade.
        totara_certification_upgrade_course_completion_history_grade_columns();

        /** Check the grademax and grademin are filled according to the current grades of the corresponding courses. **/

        // For course1 grademax should be 100 and grademin should be 0.
        $params = ['userid' => $user1->id, 'courseid' => $course1->id, 'grade' => 80, 'grademax' => 100, 'grademin' => 0];
        $this->assertTrue($DB->record_exists('course_completion_history', $params));

        $params = ['userid' => $user2->id, 'courseid' => $course1->id, 'grade' => 70, 'grademax' => 100, 'grademin' => 0];
        $this->assertTrue($DB->record_exists('course_completion_history', $params));

        // For course2 grademax should be 80 and grademin should be 10.
        $params = ['userid' => $user1->id, 'courseid' => $course2->id, 'grade' => 90, 'grademax' => 80, 'grademin' => 10];
        $this->assertTrue($DB->record_exists('course_completion_history', $params));
    }
}
