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

use totara_core\user_learning\item_helper as learning_item_helper;
use totara_program\assignments\assignments;
use totara_program\content\course_set;
use totara_program\content\program_content;
use totara_program\program;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Tests the mobile_completedlearning page type resolver
 */
class mobile_completedlearning_webapi_resolver_type_page_test extends \core_phpunit\testcase {

    use webapi_phpunit_helper;

    private function resolve($field, $course, array $args = []) {
        return $this->resolve_graphql_type('mobile_completedlearning_page', $field, $course, $args);
    }

    /**
     * Create some completed learning data to fill a few pages of completed_learning.
     * @return object
     * @throws coding_exception
     */
    private function create_faux_data() {
        $gen = $this->getDataGenerator();
        $comp_gen = $gen->get_plugin_generator('core_completion');
        $prog_gen = $gen->get_plugin_generator('totara_program');

        // Create our test user
        $user= $gen->create_user();

        // Create a program.
        $prog = $prog_gen->create_program(
            [
                'fullname' => 'Test Program',
            ]
        );

        // Create a basic course with completion enabled for the program content.
        $course = $gen->create_course(
            [
                'fullname' => 'Test Prog Content',
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

        // Assign the user one to the program
        $prog_gen->assign_to_program(
            $prog->id,
            assignments::ASSIGNTYPE_INDIVIDUAL,
            $user->id,
            null,
            true
        );

        // Enrol user on the course via the program enrolment plugin.
        $gen->enrol_user($user->id, $course->id, null, 'totara_program');

        // Complete the course for user zero.
        $comp_gen->complete_course($course, $user);

        // Complete the program for user zero.
        $prog_completion = prog_load_completion($prog->id, $user->id);
        $prog_completion->status = program::STATUS_PROGRAM_COMPLETE;
        $prog_completion->timecompleted = time();

        $result = prog_write_completion($prog_completion);
        $this->assertTrue($result);

        // Create a course unassociated with the program.
        $c1 = $this->getDataGenerator()->create_course([
            'fullname' => 'course1',
            'summary' => 'course1 summary',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($c1, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($user->id, $c1->id); // Enrol user zero.
        $comp_gen->complete_course($c1, $user); // Complete user zero.

        return $user;
    }


    /**
     * Test the results of the type resolver for the pointer field.
     * @throws coding_exception
     */
    public function test_resolve_total() {
        $user = $this->create_faux_data();
        $this->setUser($user);

        // Create a mock page item as expected from the query.
        $items = learning_item_helper::get_users_completed_learning_items($user->id);
        $mock_page = new \stdClass();
        $mock_page->total = count($items);
        $mock_page->pointer = 0;
        $mock_page->items = $items;

        // Check we get the expected amount for our test user.
        $total = $this->resolve('total', $mock_page);
        $this->assertEquals(3, $total);

        // Let's try with a malformed input object.
        $mock_page = new \stdClass();
        $total = $this->resolve('total', $mock_page);
        $this->assertNull($total);
    }

    /**
     * Test the results of the type resolver for the pointer field.
     * @throws coding_exception
     */
    public function test_resolve_pointer() {
        $user = $this->create_faux_data();
        $this->setUser($user);

        // Create a mock page item as expected from the query.
        $items = learning_item_helper::get_users_completed_learning_items($user->id);
        $mock_page = new \stdClass();
        $mock_page->total = count($items);
        $mock_page->pointer = 3;
        $mock_page->items = $items;

        // Check we get the expected amount for our test user.
        $pointer = $this->resolve('pointer', $mock_page);
        $this->assertEquals(3, $pointer); // Phpunit tests have pagesize 3.

        // Let's try with a malformed input object.
        $mock_page = new \stdClass();
        $pointer = $this->resolve('pointer', $mock_page);
        $this->assertNull($pointer);
    }

    /**
     * Test the results of the type resolver for the pointer field.
     * @throws coding_exception
     */
    public function test_resolve_items() {
        $user = $this->create_faux_data();
        $this->setUser($user);

        // Create a mock page item as expected from the query.
        $items = learning_item_helper::get_users_completed_learning_items($user->id);
        $mock_page = new \stdClass();
        $mock_page->total = count($items);
        $mock_page->pointer = 0;
        $mock_page->items = $items;

        // Check we get the expected amount for our test user.
        $completed_learning = $this->resolve('completedLearning', $mock_page);
        $this->assertSame($items, $completed_learning);

        // Let's try with a malformed input object.
        $mock_page = new \stdClass();
        $completed_learning = $this->resolve('completedLearning', $mock_page);
        $this->assertEmpty($completed_learning);
    }

    /**
     * Test the results of the type resolver for a non-existant field.
     * @throws coding_exception
     */
    public function test_resolve_invalid() {
        $user = $this->create_faux_data();
        $this->setUser($user);

        // Create a mock page item as expected from the query.
        $mock_page = new \stdClass();
        $mock_page->total = 0;
        $mock_page->pointer = 0;
        $mock_page->items = [];

        $this->expectException(\coding_exception::class);
        $this->expectExceptionMessage('Unknown field invalidArg');

        // Check we get the expected amount for our test user.
        $invalid = $this->resolve('invalidArg', $mock_page);
    }
}
