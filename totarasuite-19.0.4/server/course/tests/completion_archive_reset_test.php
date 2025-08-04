<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package core_course
 */

use core_phpunit\testcase;
use core\orm\query\builder;
use core_course\local\archive_progress_helper\single_user;
use core_course\local\archive_progress_helper\data_repository;

/**
 * @covers archive_course_completion()
 */
class core_course_completion_archive_reset_test extends testcase {

    public function test_reset(): void {
        static::markTestSkipped('TL-41398 requires an improvement for users in progress');
        [$course, $user1] = $this->generate_data();

        static::assertCourseCompletionCritComplCount(1, $course, $user1);
        static::assertBlockTotaraStatsCount(1, $course, $user1);
        static::assertCourseCompletionCount(1, $course, $user1);
        static::assertTrue(data_repository::user_has_progress($course->id, $user1->id));

        $instance = new single_user($course, $user1);
        $instance->archive_and_reset();

        static::assertCourseCompletionCritComplCount(0, $course, $user1);
        static::assertBlockTotaraStatsCount(0, $course, $user1);
        static::assertCourseCompletionCount(0, $course, $user1);
    }

    private function generate_data(): array {
        $course = static::getDataGenerator()->create_course();
        $completion_generator = static::getDataGenerator()->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);

        // Enrol users to the course.
        $user1 = static::getDataGenerator()->create_user();
        $studentrole = builder::table('role')
            ->where('shortname', 'student')
            ->get()
            ->first();
        static::getDataGenerator()->enrol_user($user1->id, $course->id, $studentrole->id);

        $page1 = static::getDataGenerator()->create_module(
            'page',
            array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1)
        );
        $page2 = static::getDataGenerator()->create_module(
            'page',
            array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1)
        );
        $completion_generator->set_activity_completion($course->id, [$page1, $page2]);

        $context = context_module::instance($page1->cmid);
        $cm = get_coursemodule_from_instance('page', $page1->id);

        // Trigger the event.
        static::setUser($user1);
        page_view($page1, $course, $cm, $context);

        static::setAdminUser();
        // Call function to complete the activity for the course.
        $criteria = builder::table('course_completion_criteria')
            ->where('course', $course->id)
            ->where('criteriatype', COMPLETION_CRITERIA_TYPE_ACTIVITY)
            ->get()
            ->first();
        $params = array(
            'userid'     => $user1->id,
            'course'     => $course->id,
            'criteriaid' => $criteria->id
        );
        $completion = new completion_criteria_completion($params);
        $completion->mark_complete();

        return [$course, $user1];
    }

    private static function assertCourseCompletionCritComplCount($expected, $course, $user): void {
        $result = builder::table('course_completion_crit_compl')
            ->where('course', $course->id)
            ->where('userid', $user->id)
            ->get()
            ->all();
        static::assertCount($expected, $result);
    }

    private static function assertBlockTotaraStatsCount($expected, $course, $user): void {
        $result = builder::table('block_totara_stats')
            ->where('data2', $course->id)
            ->where_in('eventtype', ['eventstarted' => STATS_EVENT_COURSE_STARTED, 'eventcomplete' => STATS_EVENT_COURSE_COMPLETE])
            ->where('userid', $user->id)
            ->get()
            ->all();
        static::assertCount($expected, $result);
    }

    private static function assertCourseCompletionCount($expected, $course, $user): void {
        $result = builder::table('course_completions')
            ->where('course', $course->id)
            ->where('userid', $user->id)
            ->get()
            ->all();
        static::assertCount($expected, $result);
    }
}
