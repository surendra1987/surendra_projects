<?php
/**
 * This file is part of Totara Core
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package totara
 */

use container_course\course;
use core\entity\course as course_entity;
use core\entity\course_completion as course_completion_entity;
use core\orm\query\builder;
use core_phpunit\testcase;

global $CFG;
require_once($CFG->dirroot . '/completion/completion_completion.php');

/**
 * Unit test(s) for the 'completion_completion' update_course_completions function.
 */
class totara_core_completion_update_course_completions_test extends testcase
{

    /**
     * @return void
     */
    public function test_update_course_completions_updates_duedate(): void
    {
        // Set up.
        global $DB;
        set_config('enablecompletion', 1);
        // Create test user(s).
        $user1 = $this->getDataGenerator()->create_user();
        // Create test courses.
        $record1 = new stdClass();
        $record1->enablecompletion = 1;
        $record1->completionstartonenrol = 1;
        $course1 = $this->getDataGenerator()->create_course($record1);

        // Assign users to the course even though the course doesn't have a duedate yet.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);

        // Verify our data before carrying on.
        $course_check = course_entity::repository()->find($course1->id);
        $course_completion_check = course_completion_entity::repository()->where('course', $course1->id)
            ->where('userid', $user1->id)->one();
        $course_completion_log_count_before = $DB->count_records('course_completion_log', ['courseid' => $course1->id]);
        $this->assertNull($course_check->duedate);
        $this->assertNull($course_completion_check->duedate);

        // Update the course, setting a duedate.
        $future_date = new DateTime();
        $future_date = $future_date->add(new DateInterval('P3M'));
        $test_course_duedate = $future_date->getTimeStamp();
        $course_check->duedate = $test_course_duedate;
        $course_check->save();
        // Verify course duedate got set.
        $course_check = course_entity::repository()->find($course1->id);
        $this->assertEquals($test_course_duedate, $course_check->duedate);

        // Operate.
        // This is the function that 'completion_regular_task' calls when it gets run by cron.
        update_course_completions();

        // Assert.
        $course_completion_check = course_completion_entity::repository()->where('course', $course1->id)
            ->where('userid', $user1->id)->one();
        $course_completion_log_count_after = $DB->count_records('course_completion_log', ['courseid' => $course1->id]);
        // The user's course_completions record should have updated with duedate set.
        $this->assertEquals($test_course_duedate, $course_completion_check->duedate);
        // A course_completion_log record should also have been inserted to log the update.
        $this->assertGreaterThan($course_completion_log_count_before, $course_completion_log_count_after);
    }

    public function test_duedate_get_set_enrolling_an_audience(): void {
        global $DB, $CFG;

        require_once("$CFG->dirroot/enrol/cohort/locallib.php");
        require_once($CFG->dirroot . '/completion/completion_completion.php');

        $generator = self::getDataGenerator();
        $trace = new null_progress_trace();

        $in_two_weeks = strtotime('+2 weeks');
        $in_six_months = strtotime('+6 months');
        $in_six_weeks = strtotime('+6 weeks');

        $cohortplugin = enrol_get_plugin('cohort');
        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();
        $cohort3 = $this->getDataGenerator()->create_cohort();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        $enabled = enrol_get_plugins(true);
        $enabled['cohort'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));

        // Create a base user.
        $user1 = $generator->create_user(['lastname' => 'User1 last name']);
        $user2 = $generator->create_user(['lastname' => 'User2 last name']);
        $user3 = $generator->create_user(['lastname' => 'User3 last name']);
        $user4 = $generator->create_user(['lastname' => 'User4 last name']);
        $user5 = $generator->create_user(['lastname' => 'User5 last name']);
        $user6 = $generator->create_user(['lastname' => 'User6 last name']);

        cohort_add_member($cohort1->id, $user1->id);
        cohort_add_member($cohort1->id, $user2->id);
        cohort_add_member($cohort1->id, $user4->id);

        cohort_add_member($cohort2->id, $user2->id);
        cohort_add_member($cohort2->id, $user3->id);
        cohort_add_member($cohort2->id, $user4->id);

        cohort_add_member($cohort3->id, $user4->id);
        cohort_add_member($cohort3->id, $user5->id);
        cohort_add_member($cohort3->id, $user6->id);

        // Create courses with a fixed due date
        $course1 = $generator->create_course([
            'fullname' => 'Test one',
            'enablecompletion' => COMPLETION_ENABLED,
            'duedate_op' => course::DUEDATEOPERATOR_FIXED,
            'duedate' => $in_six_weeks
        ]);
        $cohortplugin->add_instance($course1, ['customint1' => $cohort1->id, 'roleid' => $studentrole->id]);

        $course2 = $generator->create_course([
            'fullname' => 'Test two',
            'enablecompletion' => COMPLETION_ENABLED,
            'duedate_op' => course::DUEDATEOPERATOR_FIXED,
            'duedate' => $in_two_weeks
        ]);
        $cohortplugin->add_instance($course2, ['customint1' => $cohort2->id, 'roleid' => $studentrole->id]);

        // Create one with relative due date
        $course3 = $generator->create_course([
            'fullname' => 'Test three',
            'enablecompletion' => COMPLETION_ENABLED,
            'duedate_op' => course::DUEDATEOPERATOR_RELATIVE,
            'duedateoffsetamount' => 3,
            'duedateoffsetunit' => course::DUEDATEOFFSETUNIT_MONTHS,
        ]);
        $cohortplugin->add_instance($course3, ['customint1' => $cohort2->id, 'roleid' => $studentrole->id]);

        \enrol_cohort_sync($trace);

        // Now make sure all timeenrolled timestamps are set to a fixed value
        $now = time();
        builder::table('course_completions')->update(['timeenrolled' => $now]);

        self::verify_due_dates([
            [
                'course' => $course1->id,
                'userid' => $user1->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user2->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user4->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user2->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user3->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user4->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course3->id,
                'userid' => $user2->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user3->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user4->id,
                'duedate' => strtotime('+3 month', $now),
            ],
        ]);

        \update_course_completions();

        self::verify_due_dates([
            [
                'course' => $course1->id,
                'userid' => $user1->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user2->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user4->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user2->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user3->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user4->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course3->id,
                'userid' => $user2->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user3->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user4->id,
                'duedate' => strtotime('+3 month', $now),
            ],
        ]);

        $course4 = $generator->create_course([
            'fullname' => 'Test four',
            'enablecompletion' => COMPLETION_ENABLED,
            'duedate_op' => course::DUEDATEOPERATOR_FIXED,
            'duedate' => $in_six_months
        ]);
        $cohortplugin->add_instance($course4, ['customint1' => $cohort3->id, 'roleid' => $studentrole->id]);

        \enrol_cohort_sync($trace);

        // Now make sure all timeenrolled timestamps are set to a fixed value
        $now2 = time();
        builder::table('course_completions')
            ->where('course', $course4->id)
            ->update(['timeenrolled' => $now2]);

        self::verify_due_dates([
            [
                'course' => $course1->id,
                'userid' => $user1->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user2->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user4->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user2->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user3->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user4->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course3->id,
                'userid' => $user2->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user3->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user4->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course4->id,
                'userid' => $user4->id,
                'duedate' => $in_six_months,
            ],
            [
                'course' => $course4->id,
                'userid' => $user5->id,
                'duedate' => $in_six_months,
            ],
            [
                'course' => $course4->id,
                'userid' => $user6->id,
                'duedate' => $in_six_months,
            ],
        ]);

        \update_course_completions();

        self::verify_due_dates([
            [
                'course' => $course1->id,
                'userid' => $user1->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user2->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user4->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user2->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user3->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user4->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course3->id,
                'userid' => $user2->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user3->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user4->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course4->id,
                'userid' => $user4->id,
                'duedate' => $in_six_months,
            ],
            [
                'course' => $course4->id,
                'userid' => $user5->id,
                'duedate' => $in_six_months,
            ],
            [
                'course' => $course4->id,
                'userid' => $user6->id,
                'duedate' => $in_six_months,
            ],
        ]);

        // Now add a member to one of the audiences.
        // This user should automatically get a due date.
        cohort_add_member($cohort3->id, $user1->id);

        self::verify_due_dates([
            [
                'course' => $course1->id,
                'userid' => $user1->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user2->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course1->id,
                'userid' => $user4->id,
                'duedate' => $in_six_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user2->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user3->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course2->id,
                'userid' => $user4->id,
                'duedate' => $in_two_weeks,
            ],
            [
                'course' => $course3->id,
                'userid' => $user2->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user3->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course3->id,
                'userid' => $user4->id,
                'duedate' => strtotime('+3 month', $now),
            ],
            [
                'course' => $course4->id,
                'userid' => $user4->id,
                'duedate' => $in_six_months,
            ],
            [
                'course' => $course4->id,
                'userid' => $user5->id,
                'duedate' => $in_six_months,
            ],
            [
                'course' => $course4->id,
                'userid' => $user6->id,
                'duedate' => $in_six_months,
            ],
            [
                'course' => $course4->id,
                'userid' => $user1->id,
                'duedate' => $in_six_months,
            ],
        ]);
    }

    private static function verify_due_dates(array $expected): void {
        global $DB;

        $actual = $DB->get_records('course_completions');
        self::assertSame(count($expected), count($actual));

        foreach ($expected as $idx => $tst_expected) {
            foreach ($actual as $tst_actual) {
                if ($tst_expected['course'] == $tst_actual->course
                    && $tst_expected['userid'] == $tst_actual->userid
                    && ($tst_expected['duedate'] == $tst_actual->duedate || abs($tst_expected['duedate'] - $tst_actual->duedate) == 1)) {
                    unset($expected[$idx]);
                    break;
                }
            }
        }

        self::assertEmpty($expected, "Expected due dates do not match the given array. Actual: ".var_export($actual, true));
    }

}
