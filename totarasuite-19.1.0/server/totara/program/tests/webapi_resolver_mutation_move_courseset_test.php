<?php
/*
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
* @author Gihan Hewaralalge <gihan.hewaralalage@totara.com>
* @package totara_program
*/

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core\testing\generator as data_generator;
use totara_program\entity\program_courseset as program_courseset_entity;
use totara_program\program as program;
use totara_webapi\phpunit\webapi_phpunit_helper;

class totara_program_webapi_resolver_mutation_move_courseset_test extends \core_phpunit\testcase {

    private const MUTATION = 'totara_program_move_courseset';

    use webapi_phpunit_helper;

    /** @var data_generator|null */
    private ?data_generator $generator;

    /** @var \totara_program\testing\generator*/
    private $program_generator;

    /** @var stdClass|null */
    private ?stdClass $course1;
    private ?stdClass $course2;
    private ?stdClass $course3;

    /** @var program */
    private $program1;
    private $program2;

    /**
     * @return void
     * @throws coding_exception
     */
    protected function setUp(): void {
        $this->generator = $this->getDataGenerator();
        $this->program_generator = $this->generator->get_plugin_generator('totara_program');
        $completion_generator = $this->generator->get_plugin_generator('core_completion');

        $completioncriteria = [COMPLETION_CRITERIA_TYPE_SELF => 1];

        // Create courses.
        $this->course1 = $this->generator->create_course();
        $completion_generator->enable_completion_tracking($this->course1);
        $completion_generator->set_completion_criteria($this->course1, $completioncriteria);

        $this->course2 = $this->generator->create_course();
        $completion_generator->enable_completion_tracking($this->course2);
        $completion_generator->set_completion_criteria($this->course2, $completioncriteria);

        $this->course3 = $this->generator->create_course();
        $completion_generator->enable_completion_tracking($this->course3);
        $completion_generator->set_completion_criteria($this->course3, $completioncriteria);

        // Create a program.
        $this->program1 = $this->program_generator->create_program();
        $this->program2 = $this->program_generator->create_program();

        // Adding courses to the programs.
        $this->program_generator->add_courses_and_courseset_to_program($this->program1, [[$this->course1, $this->course2]]);
        $this->program_generator->add_courses_and_courseset_to_program($this->program1, [[$this->course2, $this->course3]]);
        $this->program_generator->add_courses_and_courseset_to_program($this->program1, [[$this->course1, $this->course3]]);
        $this->program_generator->add_courses_and_courseset_to_program($this->program1, [[$this->course1, $this->course2, $this->course3]]);
        $this->program_generator->add_courses_and_courseset_to_program($this->program2, [[$this->course1]]);
        $this->program_generator->add_courses_and_courseset_to_program($this->program2, [[$this->course2]]);


        // Reset sortorder
        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();

        $sortorder = 1;
        foreach ($course_sets as $course_set) {
            $courseset = new program_courseset_entity($course_set['id']);
            $courseset->sortorder = $sortorder;
            $courseset->save();
            $sortorder++;
        }
    }

    /**
     * Move a course set without logged in .
     */
    public function test_resolver_move_courseset_with_no_access() {
        try {
            $this->resolve_graphql_mutation(self::MUTATION, []);
            self::fail('require_login_exception');
        } catch (\exception $ex) {
            self::assertSame('Course or activity not accessible. (You are not logged in)', $ex->getMessage());
        }
    }

    /**
     * Move a course set without args.
     */
    public function test_resolver_move_courseset_with_no_args() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $args = [
            'input' => [
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Invalid parameter value detected (invalid program id)', $ex->getMessage());
        }
    }

    /**
     * Move a course set with no capability.
     */
    public function test_resolver_move_courseset_no_capability() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => 1,
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);
            // Get all course set in the program
            program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();

            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Sorry, but you do not currently have permissions to do that (Configure program content)', $ex->getMessage());
        }
    }

    /**
     * Move the course set on wrong program.
     * @throws coding_exception
     */
    public function test_resolver_move_down_courseset_with_wrong_program() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program1->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $program->get_context(),
            true
        );

        $this->setUser($user);

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();

        $courseset_being_move = $course_sets[1];
        $args = [
            'input' => [
                'id' => $courseset_being_move['id'],
                'program_id' => $this->program2->id,
                'new_sortorder' => 1
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Sorry, but you do not currently have permissions to do that (Configure program content)', $ex->getMessage());
        }
    }

    /**
     * Move down the course set with capability.
     * @throws coding_exception
     */
    public function test_resolver_move_down_courseset_with_capability() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program1->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $program->get_context(),
            true
        );

        $this->setUser($user);

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();
        $coursesetids = array_column($course_sets, 'id');
        self::assertCount(4,$coursesetids);

        $courseset_being_move = $course_sets[0];
        $args = [
            'input' => [
                'id' => $courseset_being_move['id'],
                'program_id' => $this->program1->id,
                'new_sortorder' => 3
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure the courseset has been updated to the new sortorder.
            foreach ($course_sets as $course_set) {
                if ($course_set['id'] == $courseset_being_move['id']) {
                    self::assertEquals(3, $course_set['sortorder']);
                }
            }

            $expected_sortorders = [1, 2, 3, 4];
            $actual = array_column($course_sets, 'sortorder');
            self::assertEquals($expected_sortorders, $actual);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }
    }

    /**
     * Move up the course set with capability.
     * @throws coding_exception
     */
    public function test_resolver_move_up_courseset_with_capability() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program1->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $program->get_context(),
            true
        );

        $this->setUser($user);

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();
        $coursesetids = array_column($course_sets, 'id');
        self::assertCount(4,$coursesetids);

        $courseset_being_move = $course_sets[3];
        $args = [
            'input' => [
                'id' => $courseset_being_move['id'],
                'program_id' => $this->program1->id,
                'new_sortorder' => 1
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure the courseset has been updated to the new sortorder.
            foreach ($course_sets as $course_set) {
                if ($course_set['id'] == $courseset_being_move['id']) {
                    self::assertEquals(1, $course_set['sortorder']);
                }
            }

            $expected_sortorders = [1, 2, 3, 4];
            $actual = array_column($course_sets, 'sortorder');
            self::assertEquals($expected_sortorders, $actual);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }
    }

    /**
     * Move down the course set with valid NEXTSETOPERATOR.
     * @throws coding_exception
     */
    public function test_resolver_move_down_with_valid_nextsetoperator() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program1->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $program->get_context(),
            true
        );

        $this->setUser($user);

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('sortorder')
            ->get()
            ->to_array();

        // Set the NEXTSETOPERATOR field to THEN for all course sets, except for the last one.
        foreach ($course_sets as $course_set) {
            if ($course_set['sortorder']  == 4) {
                $nextsetoperator = 0;
            } else {
                $nextsetoperator = 1;
            }
            program_courseset_entity::repository()
                ->where('id', $course_set['id'])
                ->update(['nextsetoperator' => $nextsetoperator]);
        }

        $course_set_ids = array_column($course_sets, 'id');
        $course_set_sortorders = array_column($course_sets, 'sortorder');
        $expected_sortorders = [1, 2, 3, 4];

        self::assertCount(4,$course_set_ids);
        self::assertEquals($expected_sortorders, $course_set_sortorders);

        // Move the second-to-last course set down.
        $courseset_being_move = $course_sets[2];
        $args = [
            'input' => [
                'id' => $courseset_being_move['id'],
                'program_id' => $this->program1->id,
                'new_sortorder' => 4
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure that the courseset has been updated to the new sortorder and nextsetoperator.
            foreach ($course_sets as $course_set) {
                if ($course_set['id'] == $courseset_being_move['id']) {
                    self::assertEquals(4, $course_set['sortorder']);
                    self::assertEquals(0, $course_set['nextsetoperator']);
                }
            }

            $expected_sortorders = [1, 2, 3, 4];
            $expected_nextsetoperators = [1, 1, 1, 0];

            $actual_sortorders = array_column($course_sets, 'sortorder');
            $actual_nextsetoperators = array_column($course_sets, 'nextsetoperator');
            self::assertEquals($expected_sortorders, $actual_sortorders);
            self::assertEquals($expected_nextsetoperators, $actual_nextsetoperators);

            // Move one of the middle coursesets down and verify that the nextsetoperator is valid.

            // Move the second course set down.
            $courseset_being_move = $course_sets[1];
            $args = [
                'input' => [
                    'id' => $courseset_being_move['id'],
                    'program_id' => $this->program1->id,
                    'new_sortorder' => 3
                ],
            ];

            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program after update.
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure that the courseset has been updated to the new sortorder and nextsetoperator.
            foreach ($course_sets as $course_set) {
                if ($course_set['id'] == $courseset_being_move['id']) {
                    self::assertEquals(3, $course_set['sortorder']);
                    self::assertEquals(1, $course_set['nextsetoperator']);
                }
            }

            $expected_sortorders = [1, 2, 3, 4];
            $expected_nextsetoperators = [1, 1, 1, 0];

            $actual_sortorders = array_column($course_sets, 'sortorder');
            $actual_nextsetoperators = array_column($course_sets, 'nextsetoperator');
            self::assertEquals($expected_sortorders, $actual_sortorders);
            self::assertEquals($expected_nextsetoperators, $actual_nextsetoperators);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }
    }


    /**
     * Move up the course set with valid NEXTSETOPERATOR.
     * @throws coding_exception
     */
    public function test_resolver_move_up_with_valid_nextsetoperator() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program1->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $program->get_context(),
            true
        );

        $this->setUser($user);

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('sortorder')
            ->get()
            ->to_array();

        // Set the NEXTSETOPERATOR field to THEN for all course sets, except for the last one.
        foreach ($course_sets as $course_set) {
            if ($course_set['sortorder']  == 4) {
                $nextsetoperator = 0;
            } else {
                $nextsetoperator = 1;
            }
            program_courseset_entity::repository()
                ->where('id', $course_set['id'])
                ->update(['nextsetoperator' => $nextsetoperator]);
        }

        $course_set_ids = array_column($course_sets, 'id');

        self::assertCount(4,$course_set_ids);

        // Move the second course set up.
        $courseset_being_move = $course_sets[1];
        $args = [
            'input' => [
                'id' => $courseset_being_move['id'],
                'program_id' => $this->program1->id,
                'new_sortorder' => 1
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure that the courseset has been updated to the new sortorder and nextsetoperator.
            foreach ($course_sets as $course_set) {
                if ($course_set['id'] == $courseset_being_move['id']) {
                    self::assertEquals(1, $course_set['sortorder']);
                    self::assertEquals(1, $course_set['nextsetoperator']);
                }
            }

            $expected_sortorders = [1, 2, 3, 4];
            $expected_nextsetoperators = [1, 1, 1, 0];

            $actual_sortorders = array_column($course_sets, 'sortorder');
            $actual_nextsetoperators = array_column($course_sets, 'nextsetoperator');
            self::assertEquals($expected_sortorders, $actual_sortorders);
            self::assertEquals($expected_nextsetoperators, $actual_nextsetoperators);

            // Move last coursesets up and verify that the nextsetoperator is valid.
            $courseset_being_move = $course_sets[3];
            $args = [
                'input' => [
                    'id' => $courseset_being_move['id'],
                    'program_id' => $this->program1->id,
                    'new_sortorder' => 3
                ],
            ];

            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program after update.
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure that the courseset has been updated to the new sortorder and nextsetoperator.
            foreach ($course_sets as $course_set) {
                if ($course_set['id'] == $courseset_being_move['id']) {
                    self::assertEquals(3, $course_set['sortorder']);
                    self::assertEquals(1, $course_set['nextsetoperator']);
                }
            }

            $expected_sortorders = [1, 2, 3, 4];
            $expected_nextsetoperators = [1, 1, 1, 0];

            $actual_sortorders = array_column($course_sets, 'sortorder');
            $actual_nextsetoperators = array_column($course_sets, 'nextsetoperator');
            self::assertEquals($expected_sortorders, $actual_sortorders);
            self::assertEquals($expected_nextsetoperators, $actual_nextsetoperators);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }
    }


    /**
     * Make sure that an array of course sets is in order in terms of each
     * set's sort order property and reset the sort order properties to ensure
     * that it begins from 1 and there are no gaps in the order.
     *
     * @throws coding_exception|\totara_program\ProgramException
     */
    public function test_resolver_move_courseset_change_sort_order_to_zero() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program1->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $program->get_context(),
            true
        );

        $this->setUser($user);

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();
        $course_set_ids = array_column($course_sets, 'id');
        self::assertCount(4,$course_set_ids);

        // Reset sort order to 0,1,2,3.
        $sort_order = 0;
        foreach ($course_sets as $course_set) {
            program_courseset_entity::repository()
                ->where('id', $course_set['id'])
                ->update(['sortorder' => $sort_order]);
            $sort_order++;
        }

        // Move second course set sort order from 1 to 0
        $courseset_being_move = $course_sets[1];
        $args = [
            'input' => [
                'id' => $courseset_being_move['id'],
                'program_id' => $this->program1->id,
                'new_sortorder' => 0
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure that an array of course sets is in order in terms of each
            // set's sort order property and reset the sort order properties to ensure
            // that it begins from 1 and there are no gaps in the order.
            foreach ($course_sets as $course_set) {
                if ($course_set['id'] == $courseset_being_move['id']) {
                    self::assertEquals(1, $course_set['sortorder']);
                }
            }

            $expected_output = [
                [
                    'id' => $course_set_ids[1],
                    'sortorder' => 1
                ],
                [
                    'id' => $course_set_ids[0],
                    'sortorder' => 2
                ],
                [
                    'id' => $course_set_ids[2],
                    'sortorder' => 3
                ],
                [
                    'id' => $course_set_ids[3],
                    'sortorder' => 4
                ]
            ];

            $actual_output = [];
            foreach ($course_sets as $course_set) {
                $actual_output[] = ['id' => $course_set['id'], 'sortorder' => $course_set['sortorder']];
            }

            self::assertEquals($expected_output, $actual_output);

            // Reset sort order to 0,1,2,3. Because of the resetting the sort order after move,
            // zero is no longer a valid.
            $sort_order = 0;
            foreach ($course_sets as $course_set) {
                program_courseset_entity::repository()
                    ->where('id', $course_set['id'])
                    ->update(['sortorder' => $sort_order]);
                $sort_order++;
            }

            // Move multiple course set up.
            // Move last course set to top.
            $courseset_being_move = $course_sets[3];
            $args = [
                'input' => [
                    'id' => $courseset_being_move['id'],
                    'program_id' => $this->program1->id,
                    'new_sortorder' => 0
                ],
            ];

            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure the courseset has been updated to the new sortorder.
            foreach ($course_sets as $course_set) {
                if ($course_set['id'] == $courseset_being_move['id']) {
                    self::assertEquals(1, $course_set['sortorder']);
                }
            }

            $expected_output = [
                [
                    'id' => $course_set_ids[3],
                    'sortorder' => 1
                ],
                [
                    'id' => $course_set_ids[1],
                    'sortorder' => 2
                ],
                [
                    'id' => $course_set_ids[0],
                    'sortorder' => 3
                ],
                [
                    'id' => $course_set_ids[2],
                    'sortorder' => 4
                ]
            ];

            $actual_output = [];
            foreach ($course_sets as $course_set) {
                $actual_output[] = ['id' => $course_set['id'], 'sortorder' => $course_set['sortorder']];
            }
            self::assertEquals($expected_output, $actual_output);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }
    }

    /**
     * Make sure that an array of course sets is in order in terms of each
     * set's sort order property and reset the sort order properties to ensure
     * that it begins from 1 and there are no gaps in the order.
     *
     * @throws coding_exception|\totara_program\ProgramException
     */
    public function test_resolver_move_courseset_change_sort_order_from_zero() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program1->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $program->get_context(),
            true
        );

        $this->setUser($user);

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();
        $course_set_ids = array_column($course_sets, 'id');
        self::assertCount(4,$course_set_ids);

        // Reset sort order to 0,1,2,3.
        $sort_order = 0;
        foreach ($course_sets as $course_set) {
            program_courseset_entity::repository()
                ->where('id', $course_set['id'])
                ->update(['sortorder' => $sort_order]);
            $sort_order++;
        }

        // Change course set 1 sort order from 0 to 1
        $courseset_being_move = $course_sets[0];
        $args = [
            'input' => [
                'id' => $courseset_being_move['id'],
                'program_id' => $this->program1->id,
                'new_sortorder' => 1
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets_updated = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure the courseset has been updated to the new sortorder.
            // The replacement of 0 with 1 results in the previous sort order of 1 being incremented to 2.
            foreach ($course_sets_updated as $course_set_updated) {
                if ($course_set_updated['id'] == $courseset_being_move['id']) {
                    self::assertEquals(2, $course_set_updated['sortorder']);
                }
            }

            $expected_output = [
                [
                    'id' => $course_set_ids[1],
                    'sortorder' => 1
                ],
                [
                    'id' => $course_set_ids[0],
                    'sortorder' => 2
                ],
                [
                    'id' => $course_set_ids[2],
                    'sortorder' => 3
                ],
                [
                    'id' => $course_set_ids[3],
                    'sortorder' => 4
                ]
            ];

            $actual_output = [];
            foreach ($course_sets_updated as $course_set_updated) {
                $actual_output[] = ['id' => $course_set_updated['id'], 'sortorder' => $course_set_updated['sortorder']];
            }

            self::assertEquals($expected_output, $actual_output);

            // Reset sort order to 0,1,2,3. Because of the resetting the sort order after move,
            // zero is no longer a valid.
            $sort_order = 0;
            foreach ($course_sets as $course_set) {
                program_courseset_entity::repository()
                    ->where('id', $course_set['id'])
                    ->update(['sortorder' => $sort_order]);
                $sort_order++;
            }

            // Move multiple course set down. Move second course set to last.
            $courseset_being_move = $course_sets[0];
            $args = [
                'input' => [
                    'id' => $courseset_being_move['id'],
                    'program_id' => $this->program1->id,
                    'new_sortorder' => 3
                ],
            ];

            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets_updated = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)
                ->order_by('sortorder')
                ->get()
                ->to_array();

            // Make sure the courseset has been updated to the new sortorder.
            // The replacement of 0 with 1 results in the previous sort order of 3 being incremented to 4.
            foreach ($course_sets_updated as $course_set_updated) {
                if ($course_set_updated['id'] == $courseset_being_move['id']) {
                    self::assertEquals(4, $course_set_updated['sortorder']);
                }
            }

            $expected_output = [
                [
                    'id' => $course_set_ids[1],
                    'sortorder' => 1
                ],
                [
                    'id' => $course_set_ids[2],
                    'sortorder' => 2
                ],
                [
                    'id' => $course_set_ids[3],
                    'sortorder' => 3
                ],
                [
                    'id' => $course_set_ids[0],
                    'sortorder' => 4
                ]
            ];

            $actual_output = [];
            foreach ($course_sets_updated as $course_set_updated) {
                $actual_output[] = ['id' => $course_set_updated['id'], 'sortorder' => $course_set_updated['sortorder']];
            }

            self::assertEquals($expected_output, $actual_output);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_resolver_move_courseset_no_courseset_id_args() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program1->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $program->get_context(),
            true
        );

        $this->setUser($user);

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)->get()->to_array();
        $coursesetids = array_column($course_sets, 'id');
        self::assertCount(4,$coursesetids);

        $args = [
            'input' => [
                'program_id' => $this->program1->id
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Invalid parameter value detected (Missing courseset id)', $ex->getMessage());
        }
    }

    /**
     * Move a course set on the program using another program id with capability.
     * @throws coding_exception|dml_exception
     */
    public function test_resolver_move_courseset_with_other_program_id_with_access() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        $context = \context_system::instance();

        // Add capability to user on program1 context.
        assign_capability(
            'totara/program:configurecontent',
            CAP_ALLOW,
            $role,
            $context,
            true
        );

        $this->setUser($user);

        // Course sets on program2
        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program2->id)
            ->order_by('id')
            ->get()
            ->to_array();
        $coursesetids = array_column($course_sets, 'id');
        self::assertCount(2,$coursesetids);

        // Program2 course set id
        $courseset_being_move = $coursesetids[0];

        // Pass program1 id with program2 course set id.
        $args = [
            'input' => [
                'id' => $courseset_being_move,
                'program_id' => $this->program1->id,
                'new_sortorder' => 1
            ],
        ];

        try {
            // Move program1 course set with program2 context capability.
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Coding error detected, it must be fixed by a programmer: Invalid course set. The course set does not belong to the given program.', $ex->getMessage());
        }
    }
    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->generator = null;
        $this->program_generator = null;
        $this->course1 = null;
        $this->course2 = null;
        $this->course3 = null;
        $this->program1 = null;
        $this->program2 = null;

        parent::tearDown();
    }
}