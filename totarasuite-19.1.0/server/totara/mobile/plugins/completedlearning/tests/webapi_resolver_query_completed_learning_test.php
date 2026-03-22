<?php
/*
 * This file is part of Totara Core
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
 * @author David Curry <david.curry@totaralearning.com>
 * @package mobile_completedlearning
 */

defined('MOODLE_INTERNAL') || die();

use totara_program\assignments\assignments;
use totara_program\content\course_set;
use totara_program\content\program_content;
use totara_program\program;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Tests the mobile completed learning query resolver
 * Note: Pagination is set to 3 items for phpunit runs.
 */
class mobile_completedlearning_webapi_resolver_query_completed_learning_test extends \core_phpunit\testcase {

    use webapi_phpunit_helper;

    private static string $query = 'mobile_completedlearning_my_items';

    /**
     * Create some completed learning data to fill a few pages of completed_learning.
     * @return array
     * @throws coding_exception
     */
    private function create_faux_data() {
        $users = [];
        $users[] = $this->getDataGenerator()->create_user(); // Assigned - Complete.
        $users[] = $this->getDataGenerator()->create_user(); // Assigned - In progress.
        $users[] = $this->getDataGenerator()->create_user(); // Not Assigned.

        $gen = $this->getDataGenerator();
        $comp_gen = $gen->get_plugin_generator('core_completion');
        $prog_gen = $gen->get_plugin_generator('totara_program');

        $now = time();

        // Create a few programs.
        for ($i = 1; $i <= 3; $i++) {
            // Create a program.
            $prog = $prog_gen->create_program(
                [
                    'fullname' => 'Test Program: ' . $i,
                ]
            );

            // Create a basic course for program content.
            $course = $gen->create_course(
                [
                    'fullname' => 'Test Prog Content: ' . $i,
                    'enablecompletion' => 1,
                ]
            );
            $comp_gen->set_completion_criteria($course, [COMPLETION_CRITERIA_TYPE_SELF => 1]);

            // Setup the courseset data.
            $cs_data = array(
                array(
                    'type' => program_content::CONTENTTYPE_MULTICOURSE,
                    'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                    'completiontype' => course_set::COMPLETIONTYPE_ALL,
                    'certifpath' => CERTIFPATH_CERT,
                    'timeallowed' => 60*60*24*7, // One week.
                    'courses' => [$course],
                ),
            );

            // Create program courseset.
            $prog_gen->legacy_add_coursesets_to_program(
                $prog,
                $cs_data
            );

            // Assign users zero and one to the program
            $prog_gen->assign_to_program(
                $prog->id,
                assignments::ASSIGNTYPE_INDIVIDUAL,
                $users[0]->id,
                null,
                true
            );

            $prog_gen->assign_to_program(
                $prog->id,
                assignments::ASSIGNTYPE_INDIVIDUAL,
                $users[1]->id,
                null,
                true
            );

            // Enrol users zero and one on the course via the program enrolment plugin.
            $gen->enrol_user($users[0]->id, $course->id, null, 'totara_program');
            $gen->enrol_user($users[1]->id, $course->id, null, 'totara_program');

            // Complete the course for user zero.
            $comp_gen->complete_course($course, $users[0], $now - (7 * $i));

            // Complete the program for user zero.
            $prog_completion = prog_load_completion($prog->id, $users[0]->id);
            $prog_completion->status = program::STATUS_PROGRAM_COMPLETE;
            $prog_completion->timecompleted = $now - (5 * $i);


            $result = prog_write_completion($prog_completion);
            $this->assertTrue($result);
        }

        // Create 3 courses unassociated with programs.
        $c1 = $this->getDataGenerator()->create_course([
            'fullname' => 'course1',
            'summary' => 'course1 summary',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($c1, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[0]->id, $c1->id); // Enrol user zero.
        $gen->enrol_user($users[1]->id, $c1->id); // Enrol user one.
        $comp_gen->complete_course($c1, $users[0], $now - 8); // Complete user zero.

        $c2 = $this->getDataGenerator()->create_course([
            'fullname' => 'course2',
            'summary' => 'course2 summary',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($c2, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[0]->id, $c2->id); // Enrol user zero.
        $gen->enrol_user($users[1]->id, $c2->id); // Enrol user one.
        $comp_gen->complete_course($c2, $users[0], $now - 12); // Complete user zero.

        $c3 = $this->getDataGenerator()->create_course([
            'fullname' => 'course3',
            'summary' => '<div class=\'summary\'>course3 summary</div>',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($c3, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[0]->id, $c3->id); // Enrol user zero.
        $gen->enrol_user($users[1]->id, $c3->id); // Enrol user one.
        $comp_gen->complete_course($c3, $users[0], $now - 25); // Complete user zero.

        $c4 = $this->getDataGenerator()->create_course([
            'fullname' => 'course4',
            'summary' => '<div class=\'summary\'>course4 summary</div>',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($c4, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[0]->id, $c4->id); // Enrol user zero.
        $gen->enrol_user($users[1]->id, $c4->id); // Enrol user one.
        $comp_gen->complete_course($c4, $users[0], $now); // Complete user zero.

        return $users;
    }

    /**
     * Test the results of the query when the current user is not logged in.
     */
    public function test_resolve_no_login() {
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Course or activity not accessible. (You are not logged in)');

        $this->resolve_graphql_query(self::$query, ['pointer' =>0]);
    }

    /**
     * Test the results of the query when the current user is logged in as the guest user.
     * @throws coding_exception
     */
    public function test_resolve_guest_user() {
        $this->setGuestUser();

        // Guests shouldn't get an error, but shouldn't have any records.
        $result1 = $this->resolve_graphql_query(self::$query, ['pointer' => 0]);
        $this->assertEquals(0, $result1->total);
        $this->assertEquals(0, $result1->pointer);
        $this->assertCount(0, $result1->items);
    }

    /**
     * Test the results of the query match expectations for a course learning item.
     */
    public function test_resolve_completed_user() {
        $users = $this->create_faux_data();

        // Check access for user 0.
        $this->setUser($users[0]);

        // Fetch and check the users first page of completed learning.
        $result1 = $this->resolve_graphql_query(self::$query, ['pointer' => 0]);
        $this->assertEquals(6, $result1->total);
        $this->assertEquals(3, $result1->pointer);
        $this->assertCount(3, $result1->items);

        $result2 = $this->resolve_graphql_query(self::$query, ['pointer' => $result1->pointer]);
        $this->assertEquals(6, $result2->total);
        $this->assertEquals(6, $result2->pointer);
        $this->assertCount(3, $result2->items);

        $result3 = $this->resolve_graphql_query(self::$query, ['pointer' => $result2->pointer]);
        $this->assertEquals(6, $result3->total);
        $this->assertEquals(6, $result3->pointer);
        $this->assertCount(0, $result3->items);

    }

    /**
     * Test the results of the query match expectations for a course learning item.
     */
    public function test_resolve_progress_user() {
        $users = $this->create_faux_data();

        // Check access for user 1.
        $this->setUser($users[1]);

        // Fetch and check the users first page of completed learning.
        $result1 = $this->resolve_graphql_query(self::$query, ['pointer' => 0]);
        $this->assertEquals(0, $result1->total);
        $this->assertEquals(0, $result1->pointer);
        $this->assertCount(0, $result1->items);

        // Everything is in progress not complete, lets make one complete one.
        $gen = $this->getDataGenerator();
        $comp_gen = $gen->get_plugin_generator('core_completion');
        $complete = $gen->create_course([
            'fullname' => 'CompleteMe',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($complete, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[1]->id, $complete->id);
        $comp_gen->complete_course($complete, $users[1]);

        // Fetch and check the users first page of completed learning.
        $result2 = $this->resolve_graphql_query(self::$query, ['pointer' => 0]);
        $this->assertEquals(1, $result2->total);
        $this->assertEquals(1, $result2->pointer);
        $this->assertCount(1, $result2->items);
    }

    /**
     * Test the results of the query match expectations for the user not assigned to the learning items
     * @throws coding_exception
     */
    public function test_resolve_unassigned_user() {
        $users = $this->create_faux_data();

        // Check access for user 1.
        $this->setUser($users[1]);

        // Fetch and check the users first page of completed learning.
        $result1 = $this->resolve_graphql_query(self::$query, ['pointer' => 0]);
        $this->assertEquals(0, $result1->total);
        $this->assertEquals(0, $result1->pointer);
        $this->assertCount(0, $result1->items);
    }

    /**
     * Test the results of the embedded mobile query through the GraphQL stack.
     * @throws coding_exception
     */
    public function test_embedded_query() {
        $users = $this->create_faux_data();

        $this->setUser($users[0]);

        try {
            $result = \totara_webapi\graphql::execute_operation(
                \core\webapi\execution_context::create('mobile', 'totara_mobile_completed_learning'),
                ["pointer" => 0]
            );
        } catch (\moodle_exception $ex) {
            $this->fail($ex->getMessage());
        }

        $data = $result->toArray()['data'];
        $this->assertEquals(6, $data['completedLearning']['total']);
        $this->assertEquals(3, $data['completedLearning']['pointer']);
        $this->assertCount(3, $data['completedLearning']['completedLearning']);

        // Lets just do a basic data structure test on the items, should be the last few courses generated.
        foreach ($data['completedLearning']['completedLearning'] as $item) {
            $this->assertSame('mobile_completedlearning_item', $item['__typename']);
            $this->assertEquals(100, $item['progress']);
            $this->assertSame('HTML', $item['summaryFormat']);

            $this->assertArrayHasKey('itemtype', $item);
            $this->assertArrayHasKey('itemcomponent', $item);
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('shortname', $item);
            $this->assertArrayHasKey('fullname', $item);
            $this->assertArrayHasKey('summary', $item);
            $this->assertArrayHasKey('urlView', $item);
            $this->assertArrayHasKey('duedate', $item);
            $this->assertArrayHasKey('duedateState', $item);
            $this->assertArrayHasKey('native', $item);
            $this->assertArrayHasKey('imageSrc', $item);
        }
    }

    /**
     * Test limiting the results of the query to 3 items per page when testing (limit is otherwise 100).
     * The faux data should be returned in the following order:
     *
     * Course 4 (now)
     * Program 1 (5s ago)
     * Course Set 1 (7s ago)
     * Course 1 (8s ago)
     * Prog 2 (10s ago)
     * Course 2 (12s ago)
     * Course Set 2 (14s ago)
     * Program 3 (15s ago)
     * Course Set 3 (21s ago)
     * Course 3 (25s ago)
     *
     */

    public function test_resolve_completed_learning_limit() {
        $users = $this->create_faux_data();

        // Check access for User 0.
        $this->setUser($users[0]);

        // Fetch and check the limits on the user's first page of completed learning
        $result1 = $this->resolve_graphql_query(self::$query, ["pointer" => 0]);
        $this->assertEquals(6, $result1->total);
        $this->assertEquals(3, $result1->pointer);
        $this->assertCount(3, $result1->items);

        // Check that the first item returned has a timecompleted value that is more recent than the second item's timecompleted value
        $this->assertGreaterThan($result1->items[1]->timecompleted, $result1->items[0]->timecompleted);

        // Fetch and check the limits on the user's second page of completed learning
        $result2 = $this->resolve_graphql_query(self::$query, ['pointer' => $result1->pointer]);
        $this->assertEquals(6, $result2->total);
        $this->assertEquals(6, $result2->pointer);
        $this->assertCount(3, $result2->items);
        $this->assertGreaterThan($result2->items[1]->timecompleted, $result2->items[0]->timecompleted);

        // Fetch and check the limits on the user's third page of completed learning
        $result3 = $this->resolve_graphql_query(self::$query, ['pointer' => $result2->pointer]);
        $this->assertEquals(6, $result3->total);
        $this->assertEquals(6, $result3->pointer);
        $this->assertCount(0, $result3->items);

        // Add another course
        $gen = $this->getDataGenerator();
        $comp_gen = $gen->get_plugin_generator('core_completion');
        $complete = $gen->create_course([
            'fullname' => 'Course 5',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($complete, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[0]->id, $complete->id);

        // Complete the course giving its timecompleted value the most recent time
        $comp_gen->complete_course($complete, $users[0], time() + 1);

        // Check first page again to make sure this new course is the most recently completed item
        $result4 = $this->resolve_graphql_query(self::$query, ['pointer' => 0]);
        $this->assertGreaterThan($result4->items[1]->timecompleted, $result4->items[0]->timecompleted);
        $this->assertEquals('Course 5', $result4->items[0]->fullname);
    }
}