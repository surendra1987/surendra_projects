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

use totara_program\entity\program_courseset as program_courseset_entity;
use totara_webapi\phpunit\webapi_phpunit_helper;
use core\testing\generator as data_generator;
use totara_program\program as program;

class totara_program_webapi_resolver_mutation_delete_courseset_test extends \core_phpunit\testcase {

    private const MUTATION = 'totara_program_delete_courseset';

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
        $this->program_generator->add_courses_and_courseset_to_program($this->program2, [[$this->course2]]);
    }

    /**
     * Delete a course set without logged in .
     */
    public function test_resolver_delete_courseset_with_no_access() {
        try {
            $this->resolve_graphql_mutation(self::MUTATION, []);
            self::fail('require_login_exception');
        } catch (\exception $ex) {
            self::assertSame('Course or activity not accessible. (You are not logged in)', $ex->getMessage());
        }
    }

    /**
     * Delete a course set without args.
     */
    public function test_resolver_delete_courseset_with_no_args() {
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
     * Delete a course set with no capability.
     */
    public function test_resolver_save_courseset_no_capability() {
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
     * Delete a course set with capability.
     * @throws coding_exception
     */
    public function test_resolver_delete_courseset_with_capability() {
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
        self::assertCount(2,$coursesetids);

        $delete_couseset_id = $coursesetids[0];
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => $delete_couseset_id
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check $program_courseset1 has created.
            self::assertNotContains($delete_couseset_id, $coursesetids);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }
    }

    /**
     * Delete a course set on the program using another program id with no suitable access capability.
     * @throws coding_exception
     */
    public function test_resolver_delete_courseset_on_other_program_without_access() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program($this->program2->id);
        } catch (ProgramException | coding_exception $e) {
            self::fail('Unexpected an exception');
        }

        // Add capability to user on program2 context.
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
        self::assertCount(2,$coursesetids);

        $delete_couseset_id = $coursesetids[0];
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => $delete_couseset_id
            ],
        ];

        try {
            // Delete program1 course set with program2 context capability.
            $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Sorry, but you do not currently have permissions to do that (Configure program content)', $ex->getMessage());
        }
    }


    /**
     * Delete a course set on the program using another program id with capability.
     * @throws coding_exception
     */
    public function test_resolver_delete_courseset_with_other_program_id_with_access() {
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
            ->where('programid', $this->program2->id)->get()->to_array();
        $coursesetids = array_column($course_sets, 'id');
        self::assertCount(1,$coursesetids);

        // Program2 course set id
        $delete_couseset_id = $coursesetids[0];
        // Pass program1 id with program2 course set id.
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => $delete_couseset_id
            ],
        ];

        try {
            // Delete program1 course set with program2 context capability.
            $this->resolve_graphql_mutation(self::MUTATION, $args);

            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Coding error detected, it must be fixed by a programmer: Invalid course set. The course set does not belong to the given program.', $ex->getMessage());
        }
    }

    /**
     * Pass invalid course set id .
     * @throws coding_exception
     */
    public function test_resolver_delete_courseset_no_courseset_id_args() {
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
        self::assertCount(2,$coursesetids);

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
