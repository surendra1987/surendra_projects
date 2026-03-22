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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Katherine Galano <katherine.galano@totara.com>
 * @package totara_core
 */

use totara_program\assignments\assignments;
use totara_program\content\course_set;
use totara_program\content\program_content;
use totara_program\program;
use totara_webapi\phpunit\webapi_phpunit_helper;

class totara_core_webapi_resolver_query_my_completed_learning_test extends \core_phpunit\testcase {
    use webapi_phpunit_helper;

    private static string $query = 'totara_core_my_completed_learning';

    /**
     * Create some completed learning data to fill a few pages of completed_learning.
     *
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
                    'timeallowed' => 60 * 60 * 24 * 7, // One week.
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
            $comp_gen->complete_course($course, $users[0]);

            // Complete the program for user zero.
            $prog_completion = prog_load_completion($prog->id, $users[0]->id);
            $prog_completion->status = program::STATUS_PROGRAM_COMPLETE;
            $prog_completion->timecompleted = time();

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
        $comp_gen->complete_course($c1, $users[0]); // Complete user zero.

        $c2 = $this->getDataGenerator()->create_course([
            'fullname' => 'course2',
            'summary' => 'course2 summary',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($c2, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[0]->id, $c2->id); // Enrol user zero.
        $gen->enrol_user($users[1]->id, $c2->id); // Enrol user one.
        $comp_gen->complete_course($c2, $users[0]); // Complete user zero.

        $c3 = $this->getDataGenerator()->create_course([
            'fullname' => 'course3',
            'summary' => '<div class=\'summary\'>course3 summary</div>',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($c3, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[0]->id, $c3->id); // Enrol user zero.
        $gen->enrol_user($users[1]->id, $c3->id); // Enrol user one.
        $comp_gen->complete_course($c3, $users[0]); // Complete user zero.

        $c4 = $this->getDataGenerator()->create_course([
            'fullname' => 'course4',
            'summary' => '<div class=\'summary\'>course4 summary</div>',
            'enablecompletion' => 1,
        ]);
        $comp_gen->set_completion_criteria($c4, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        $gen->enrol_user($users[0]->id, $c4->id); // Enrol user zero.
        $gen->enrol_user($users[1]->id, $c4->id); // Enrol user one.
        $comp_gen->complete_course($c4, $users[0]); // Complete user zero.

        return $users;
    }

    /**
     * Test the results of the query when the current user is not logged in.
     */
    public function test_resolve_no_login() {
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Course or activity not accessible. (You are not logged in)');

        $this->resolve_graphql_query(self::$query);
    }

    /**
     * Test the results of the query when the current user is logged in as the guest user.
     *
     * @throws coding_exception
     */
    /**
     * Test the results of the query when the current user is logged in as the guest user.
     */
    public function test_resolve_guest_user() {
        $this->setGuestUser();

        $results = $this->resolve_graphql_query(self::$query);
        $this->assertEmpty($results); // Guests shouldn't have any items.
    }

    /**
     * Test the results of the query match expectations for a course learning item.
     */
    public function test_resolve_completed_user() {
        $users = $this->create_faux_data();

        // User with completed learning
        $this->setUser($users[0]);
        $items = $this->resolve_graphql_query(self::$query);
        $this->assertCount(6, $items);

        // Lets just do a basic data structure test on the items, should be the last few courses generated.
        foreach ($items as $item) {
            $this->assertTrue($item->id > 0);
            $this->assertNotEmpty($item->itemtype);
            $this->assertNotEmpty($item->itemcomponent);
            $this->assertNotEmpty($item->shortname);
            $this->assertNotEmpty($item->fullname);
            $this->assertNotEmpty($item->url_view);
            $this->assertNotEmpty($item->image_src);
            $this->assertEquals(100, $item->progress);
        }
    }

    public function test_resolve_zero_completed_user() {
        $users = $this->create_faux_data();

        // User with no completed learning
        $this->setUser($users[1]);
        $items = $this->resolve_graphql_query(self::$query);
        $this->assertEmpty($items);
    }
}