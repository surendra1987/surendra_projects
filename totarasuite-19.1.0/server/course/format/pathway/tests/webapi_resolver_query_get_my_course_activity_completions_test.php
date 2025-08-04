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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package format_pathway
 */

use core_phpunit\testcase;
use format_pathway\webapi\resolver\query\get_my_course_activity_completions;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group format_pathway
 */
class format_pathway_webapi_resolver_query_get_my_course_activity_completions_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * Check when there is no completion for the activity completion is not enabled
     *
     * @return void
     * @throws coding_exception
     */
    public function test_resolve_no_completion(): void {
        // create a course
        $course = $this->getDataGenerator()->create_course(['format' => 'pathway']);
        // create a few activities
        $section = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        $forum = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,
            'shortname' => 'test forum',
            'idnumber' => 'test_forum',
            'completion' => 0,
            'completionview' => 0,
        ]);
        // create a user
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        // enrol the user in the course
        $this->setUser($user);
        $response = $this->resolve_graphql_query(
            $this->get_graphql_name(get_my_course_activity_completions::class),
            ['course_id' => $course->id]
        );

        $activity = reset($response);
        $this->assertFalse($activity['completionstatus']);
        $this->assertFalse($activity['activitycompletionenabled']);
        $this->assertSame('NONE', $activity['activitycompletiontracking']);
        $this->assertEquals("https://www.example.com/moodle/mod/forum/view.php?id=" . $activity['cmid'], $activity['activity_url']);
        $this->assertEmpty($activity['completion_status_description']);
    }

    /**
     * Check when completion is enabled but activity is not completed
     *
     * @return void
     * @throws coding_exception
     */
    public function test_resolve_completion_view_not_completed(): void {
        // create a course
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'format' => 'pathway']);
        // create a few activities
        $section = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        $forum = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,
            'shortname' => 'test forum',
            'idnumber' => 'test_forum',
            'completion' => 1,
            'completionview' => 1,
        ]);
        // create a user
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        // enrol the user in the course
        $this->setUser($user);
        $response = $this->resolve_graphql_query(
            $this->get_graphql_name(get_my_course_activity_completions::class),
            ['course_id' => $course->id]
        );

        $activity = reset($response);
        $this->assertSame($forum->cmid, $activity['cmid']);
        $this->assertFalse($activity['completionstatus']);
        $this->assertTrue($activity['activitycompletionenabled']);
        $this->assertSame('MANUAL', $activity['activitycompletiontracking']);
    }

    /**
     * Check when completion is required and the course is completed
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_completion_view_is_completed(): void {
        global $DB;
        // create a course
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'format' => 'pathway']);
        // create a few activities
        $section = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        $forum = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,
            'shortname' => 'test forum',
            'idnumber' => 'test_forum',
            'completion' => COMPLETION_TRACKING_MANUAL,
            'completionview' => 1,
        ]);
        $choice = $this->getDataGenerator()->create_module('choice', [
            'course' => $course,
            'section' => 1,
            'shortname' => 'test choice',
            'idnumber' => 'test_choice',
            'completion' => COMPLETION_TRACKING_MANUAL,
            'completionview' => 1,
        ]);

        // create a user
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        // enrol the user in the course
        $this->setUser($user);
        // ensure the course module is marked as completed
        $modulecompletion= new stdClass();
        $modulecompletion->coursemoduleid = $forum->cmid;
        $modulecompletion->userid = $user->id;
        $modulecompletion->completionstate = COMPLETION_COMPLETE;
        $modulecompletion->progress = 100;
        $modulecompletion->viewed = 1;
        $modulecompletion->timemodified = 345;
        $modulecompletion->timecompleted = 456;
        $modulecompletion->reaggregage = 567;
        $cmcid = $DB->insert_record('course_modules_completion', $modulecompletion);

        $response = $this->resolve_graphql_query(
            $this->get_graphql_name(get_my_course_activity_completions::class),
            ['course_id' => $course->id]
        );

        // forum should be completed
        $activity = reset($response);
        $this->assertSame($forum->cmid, $activity['cmid']);
        $this->assertTrue($activity['completionstatus']);
        $this->assertTrue($activity['activitycompletionenabled']);
        $this->assertSame('MANUAL', $activity['activitycompletiontracking']);

        // choice should not be completed
        $last_activity = end($response);
        $this->assertSame($choice->cmid, $last_activity['cmid']);
        $this->assertFalse($last_activity['completionstatus']);
        $this->assertTrue($last_activity['activitycompletionenabled']);
        $this->assertSame('MANUAL', $activity['activitycompletiontracking']);
    }

    /**
     * Check when there is completion data for an activity but that activity is not required for course completion
     *
     * @return void
     */
    public function test_resolve_with_completion_for_untracked_activity(): void {
        global $DB;

        // Create a course.
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'format' => 'pathway']);

        // Create an activity.
        $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        $forum = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,
            'shortname' => 'test forum',
            'idnumber' => 'test_forum',
            'completion' => COMPLETION_TRACKING_NONE,
            'completionview' => 1,
        ]);

        // Create a user
        $user = $this->getDataGenerator()->create_user();

        // Enrol the user in the course
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        // Ensure the course module is marked as completed.
        $modulecompletion= new stdClass();
        $modulecompletion->coursemoduleid = $forum->cmid;
        $modulecompletion->userid = $user->id;
        $modulecompletion->completionstate = COMPLETION_COMPLETE;
        $modulecompletion->progress = 100;
        $modulecompletion->viewed = 1;
        $modulecompletion->timemodified = 345;
        $modulecompletion->timecompleted = 456;
        $modulecompletion->reaggregage = 567;
        $DB->insert_record('course_modules_completion', $modulecompletion);

        // Run the query.
        $this->setUser($user);
        $response = $this->resolve_graphql_query(
            $this->get_graphql_name(get_my_course_activity_completions::class),
            ['course_id' => $course->id]
        );

        // Forum should NOT be complete.
        $activity = reset($response);
        $this->assertSame($forum->cmid, $activity['cmid']);
        $this->assertFalse($activity['completionstatus']);
        $this->assertFalse($activity['activitycompletionenabled']);
        $this->assertSame('NONE', $activity['activitycompletiontracking']);
    }

        /**
     * Check the response comes back ordered by the activity id
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_completion_ordered_by_cmid(): void {
        global $DB;
        // create a course
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1, 'format' => 'pathway']);
        // create a few activities
        $section = $this->getDataGenerator()->create_course_section(['course' => $course, 'section' => 1]);
        $forum = $this->getDataGenerator()->create_module('forum', [
            'course' => $course,
            'section' => 1,
            'shortname' => 'test forum',
            'idnumber' => 'test_forum',
            'completion' => COMPLETION_TRACKING_MANUAL,
            'completionview' => 1,
        ]);
        $choice = $this->getDataGenerator()->create_module('choice', [
            'course' => $course,
            'section' => 1,
            'shortname' => 'test choice',
            'idnumber' => 'test_choice',
            'completion' => COMPLETION_TRACKING_MANUAL,
            'completionview' => 1,
        ]);
        $choice_2 = $this->getDataGenerator()->create_module('choice', [
            'course' => $course,
            'section' => 1,
            'shortname' => 'test choice 2',
            'idnumber' => 'test_choice_2',
            'completion' => COMPLETION_TRACKING_MANUAL,
            'completionview' => 1,
        ]);
        // create a user
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        // enrol the user in the course
        $this->setUser($user);

        $response = $this->resolve_graphql_query(
            $this->get_graphql_name(get_my_course_activity_completions::class),
            ['course_id' => $course->id]
        );
        $activity_ids = [$forum->cmid, $choice->cmid, $choice_2->cmid];
        usort($activity_ids, function($a, $b) {
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        });

        $received_order = array_column($response, 'cmid');

        for ($i = 0; $i < count($activity_ids); $i++) {
            $this->assertSame($activity_ids[$i], $received_order[$i]);
        }
    }

    public function test_resolve_completion_description(): void {
        global $DB;
        $gen = $this->getDataGenerator();

        // create a course
        $course = $gen->create_course(['enablecompletion' => 1, 'format' => 'pathway']);
        // create a few activities
        $section = $gen->create_course_section(['course' => $course, 'section' => 1]);

        $quiz = $gen->create_module('quiz', [
            'course' => $course,
            'section' => 1,
            'completion' => 1,
            'completionview' => 1,
        ]);

        // create a user
        $user = $gen->create_user();
        $gen->enrol_user($user->id, $course->id);
        // enrol the user in the course
        $this->setUser($user);

        // Create mock data
        $mod_quiz = get_coursemodule_from_instance('quiz', $quiz->id);
        $modules_completion = new stdClass();
        $modules_completion->userid = $user->id;
        $modules_completion->coursemoduleid = $mod_quiz->id;
        $modules_completion->completionstate = COMPLETION_COMPLETE_FAIL;
        $modules_completion->progress = null;
        $modules_completion->timemodified = time();
        $modules_completion->viewed = COMPLETION_NOT_VIEWED;
        $modules_completion->timecompleted = null;
        $modules_completion->reaggregate = 0;
        $DB->insert_record('course_modules_completion', $modules_completion);

        $grade_item = new grade_item(['itemtype' => 'mod', 'itemmodule' => 'quiz', 'iteminstance' => $quiz->id, 'courseid' => $course->id, 'gradepass' => 2], false);
        $grade_item->insert();

        $response = $this->resolve_graphql_query(
            $this->get_graphql_name(get_my_course_activity_completions::class),
            ['course_id' => $course->id]
        );

        $activity = reset($response);
        $this->assertTrue($activity['completionstatus']);
        $this->assertTrue($activity['activitycompletionenabled']);
        $this->assertEquals("https://www.example.com/moodle/mod/quiz/view.php?id=" . $activity['cmid'], $activity['activity_url']);
        $this->assertEquals(get_string('completion_not_pass_grade', 'format_pathway'), $activity['completion_status_description']);
    }
}
