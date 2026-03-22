<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Nathan Lewis <nathan.lewis@totaralms.com>
 * @package totara_core
 *
 * To test, run this from the command line from the $CFG->dirroot
 * vendor/bin/phpunit --verbose totara_core_completion_start_user_bulk_test totara/core/tests/completion_start_user_bulk_test.php
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

global $CFG;
require_once($CFG->dirroot . '/completion/completion_completion.php');

use container_course\course;
use core\orm\query\builder;
use core\orm\query\order;
use core_completion\task\update_completion_due_dates_task;
use core_phpunit\testcase;

class totara_core_completion_start_user_bulk_test extends testcase {
    protected $user1;
    protected $user2;
    protected $course1;
    protected $course2;
    protected $course3;

    protected function tearDown(): void {
        $this->user1 = null;
        $this->user2 = null;
        $this->course1 = null;
        $this->course2 = null;
        $this->course3 = null;
        parent::tearDown();
    }

    protected function setUp(): void {
        set_config('enablecompletion', 1);

        // Create test users.
        $this->user1 = $this->getDataGenerator()->create_user();
        $this->user2 = $this->getDataGenerator()->create_user();

        // Create test courses.
        $record1 = new stdClass();
        $record1->enablecompletion = 1;
        $record1->completionstartonenrol = 1;
        $record1->duedate_op = course::DUEDATEOPERATOR_FIXED;
        $record1->duedate = 123;
        $this->course1 = $this->getDataGenerator()->create_course($record1);

        $record2 = new stdClass();
        $record2->enablecompletion = 1;
        $record2->completionstartonenrol = 1;
        $record2->duedate_op = course::DUEDATEOPERATOR_FIXED;
        $record2->duedate = 123;
        $this->course2 = $this->getDataGenerator()->create_course($record2);

        $record3 = new stdClass();
        $record3->enablecompletion = 0; // Disabled, so no completion records should exist ever.
        $this->course3 = $this->getDataGenerator()->create_course($record3);

        // Assign users to the courses.
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course1->id);
        $this->getDataGenerator()->enrol_user($this->user2->id, $this->course1->id);
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course2->id);
        $this->getDataGenerator()->enrol_user($this->user2->id, $this->course2->id);
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course3->id);
        $this->getDataGenerator()->enrol_user($this->user2->id, $this->course3->id);
    }

    public function test_completion_start_user_bulk() {
        global $DB;

        // Make sure the records are there.
        $this->assertEquals(2, $DB->count_records('course_completions', array('course' => $this->course1->id)));
        $this->assertEquals(2, $DB->count_records('course_completions', array('course' => $this->course2->id)));
        $this->assertEquals(0, $DB->count_records('course_completions', array('course' => $this->course3->id)));

        // Delete all of the records - these are the ones that should be recreated.
        $DB->delete_records('course_completions', array('course' => $this->course1->id));
        $DB->delete_records('course_completions', array('course' => $this->course2->id));

        // Clear out any logs that might have been created during setup.
        $DB->delete_records('course_completion_log');

        // Run the function we're testing for just course1.
        completion_start_user_bulk($this->course1->id);

        // Check that only course1 records have been recreated.
        $this->assertEquals(2, $DB->count_records('course_completions'));
        $this->assertEquals(2, $DB->count_records('course_completions', array('course' => $this->course1->id)));
        $this->assertEquals(2, $DB->count_records('course_completion_log'));
        $this->assertEquals(2, $DB->count_records('course_completion_log', array('courseid' => $this->course1->id)));

        // Due date has not been set for any course completions.
        $this->assertEquals(2, $DB->count_records('course_completions', ['duedate' => null]));

        // Run the function for one user - should create the record for one course and update the other.
        completion_start_user_bulk(null, $this->user1->id);

        // Check the results.
        $this->assertEquals(3, $DB->count_records('course_completions'));
        $this->assertEquals(2, $DB->count_records('course_completions', array('userid' => $this->user1->id)));
        $this->assertEquals(2, $DB->count_records('course_completions', array('duedate' => 123)));
        $this->assertEquals(5, $DB->count_records('course_completion_log')); // 2 from earlier, 1 new completion, 2 duedate.

        // Run the function we're testing for all courses - should create those for course2 only since course1 already exist.
        completion_start_user_bulk();

        // Make sure the records are there.
        $this->assertEquals(4, $DB->count_records('course_completions'));
        $this->assertEquals(2, $DB->count_records('course_completions', array('course' => $this->course1->id)));
        $this->assertEquals(2, $DB->count_records('course_completions', array('course' => $this->course2->id)));
        $this->assertEquals(6, $DB->count_records('course_completion_log')); // 4 completion, 4 due date.
        $this->assertEquals(3, $DB->count_records('course_completion_log', array('courseid' => $this->course1->id)));
        $this->assertEquals(3, $DB->count_records('course_completion_log', array('courseid' => $this->course2->id)));

        // Make sure all records are marked as ready for reaggregation.
        $reaggregatecount = $DB->count_records_select('course_completions', 'reaggregate > 0');
        $this->assertEquals(4, $reaggregatecount);

        // Make sure all records are marked with timestarted 0 (it is updated elsewhere).
        $reaggregatecount = $DB->count_records_select('course_completions', 'timestarted = 0');
        $this->assertEquals(4, $reaggregatecount);

        // Make sure that all records have due date set, including course1.
        $this->assertEquals(2, $DB->count_records('course_completions', array('duedate' => 123)));
    }
}
