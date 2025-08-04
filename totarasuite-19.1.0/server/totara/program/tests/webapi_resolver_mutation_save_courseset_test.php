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
use totara_program\model\program_courseset as program_courseset_model;
use totara_webapi\phpunit\webapi_phpunit_helper;
use core\testing\generator as data_generator;
use totara_program\entity\program as program;
use totara_program\program as program_class;

class totara_program_webapi_resolver_mutation_save_courseset_test extends \core_phpunit\testcase {

    private const MUTATION = 'totara_program_save_courseset';

    use webapi_phpunit_helper;

    /** @var data_generator|null */
    private ?data_generator $generator;

    /** @var \totara_program\testing\generator*/
    private $program_generator;

    /** @var stdClass|null */
    private ?stdClass $course1;

    /** @var stdClass|null */
    private ?stdClass $course2;

    /** @var stdClass|null */
    private ?stdClass $course3;

    /** @var program */
    private $program1;

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
    }

    /**
     * Create a course set without logged in .
     */
    public function test_resolver_save_courseset_with_no_access() {
        try {
            $this->resolve_graphql_mutation(self::MUTATION, []);
            self::fail('require_login_exception');
        } catch (\exception $ex) {
            self::assertSame('Course or activity not accessible. (You are not logged in)', $ex->getMessage());
        }
    }

    /**
     * Create a course set without args.
     */
    public function test_resolver_save_courseset_with_no_args() {
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
     * Create a course set with invalid args.
     */
    public function test_resolver_save_courseset_invalid_course_set_arg() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $course_ids = [];
        $course_ids[] = $this->course1->id;
        $args = [
            'input' => [
                'program_new_id' => $this->program1->id,
                'course_new_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
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
     * Create a course set with no capability.
     */
    public function test_resolver_save_courseset_no_capability() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $course_ids = [];

        $course_ids[] = $this->course1->id;
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
            ],
        ];

        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check $program_courseset1 has created.
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Sorry, but you do not currently have permissions to do that (Configure program content)', $ex->getMessage());
        }
    }

    /**
     * Create a course set with courses without completion setup.
     */
    public function test_resolver_save_courseset_no_completion() {
        $this->setAdminUser();
        $course_ids = [];

        $course4 = $this->generator->create_course();

        $course_ids[] = $course4->id;
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
            ],
        ];

        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check $program_courseset1 has created.
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Coding error detected, it must be fixed by a programmer: Course requires completion setup', $ex->getMessage());
        }
    }

    /**
     * Test the mutation resolvers validation of course sum field total
     *
     * @return void
     */
    public function test_resolver_save_courseset_validate_course_sum_field_total(): void {
        $this->setAdminUser();

        // Create array of courses for courseset.
        $course_ids = [];
        $course_ids[] = $this->course1->id;
        $course_ids[] = $this->course2->id;
        $course_ids[] = $this->course3->id;

        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => null,
                'course_ids' => $course_ids,
                'completion_type' => 'ALL',
                'course_sum_field_total' => 2
            ],
        ];

        // Cover the happy path.
        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            $cs_id = $result->id;

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check $program_courseset1 has created.
            self::assertContains($result->id, $coursesetids);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }

        // Now lets try update that with a non-integer
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => $cs_id,
                'course_ids' => $course_ids,
                'completion_type' => 'ALL',
                'course_sum_field_total' => 'Apples'
            ],
        ];

        // Cover the non-integer path.
        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            $message = "Invalid course sum field total. The field must be set to a non-negative integer";
            self::assertSame("Coding error detected, it must be fixed by a programmer: {$message}", $ex->getMessage());
        }

        // Now lets try update that with a negative-integer
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => $cs_id,
                'course_ids' => $course_ids,
                'completion_type' => 'ALL',
                'course_sum_field_total' => -2
            ],
        ];

        // Cover the non-integer path.
        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            $message = "Invalid course sum field total. The field must be set to a non-negative integer";
            self::assertSame("Coding error detected, it must be fixed by a programmer: {$message}", $ex->getMessage());
        }
    }

    /**
     * Test the mutation resolvers validation of min courses
     *
     * @return void
     */
    public function test_resolver_save_courseset_validate_min_courses(): void {
        $this->setAdminUser();

        // Create array of courses for courseset.
        $course_ids = [];
        $course_ids[] = $this->course1->id;
        $course_ids[] = $this->course2->id;
        $course_ids[] = $this->course3->id;

        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL',
                'min_courses' => 2
            ],
        ];

        // Cover the happy path.
        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            $cs_id = $result->id;

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check $program_courseset1 has created.
            self::assertContains($result->id, $coursesetids);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }

        // Now lets try update that with a non-integer value.
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => $cs_id,
                'completion_type' => 'ALL',
                'min_courses' => 'Apples'
            ],
        ];

        // Cover the greater than maximum path.
        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            $message = "Invalid min courses field total. The field must be set to a non-negative integer below the total number of courses in the courseset";
            self::assertSame("Coding error detected, it must be fixed by a programmer: {$message}", $ex->getMessage());
        }

        // Now lets try update that with a number greater than the total.
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => $cs_id,
                'completion_type' => 'ALL',
                'min_courses' => 5
            ],
        ];

        // Cover the greater than maximum path.
        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            $message = "Invalid min courses field total. The field must be set to a non-negative integer below the total number of courses in the courseset";
            self::assertSame("Coding error detected, it must be fixed by a programmer: {$message}", $ex->getMessage());
        }

        // Now lets try update that with a negative-integer value.
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => $cs_id,
                'completion_type' => 'ALL',
                'min_courses' => -2
            ],
        ];

        // Cover the greater than maximum path.
        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            $message = "Invalid min courses field total. The field must be set to a non-negative integer below the total number of courses in the courseset";
            self::assertSame("Coding error detected, it must be fixed by a programmer: {$message}", $ex->getMessage());
        }
    }

    /**
     * Test the mutation resolvers validation of time allowed
     */
    public function test_resolver_save_courseset_validate_time_allowed() {
        $this->setAdminUser();

        // Create array of courses for courseset.
        $course_ids = [];
        $course_ids[] = $this->course1->id;
        $course_ids[] = $this->course2->id;
        $course_ids[] = $this->course3->id;

        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => null,
                'course_ids' => $course_ids,
                'completion_type' => 'ALL',
                'time_allowed' => 60 * 60 * 24 // 1 day.
            ],
        ];

        // Cover the happy path.
        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            $cs_id = $result->id;

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check $program_courseset1 has created.
            self::assertContains($result->id, $coursesetids);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }

        // Now lets try update that with a non-integer
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => $cs_id,
                'course_ids' => $course_ids,
                'completion_type' => 'ALL',
                'time_allowed' => 'Apples'
            ],
        ];

        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            $message = "Invalid time allowed. The field must be set to a non-negative integer";
            self::assertSame("Coding error detected, it must be fixed by a programmer: {$message}", $ex->getMessage());
        }

        // Now lets try update that with a negative-integer
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'id' => $cs_id,
                'course_ids' => $course_ids,
                'completion_type' => 'ALL',
                'time_allowed' => -1 * 60 * 60 * 24 // -1 day.
            ],
        ];

        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            $message = "Invalid time allowed. The field must be set to a non-negative integer";
            self::assertSame("Coding error detected, it must be fixed by a programmer: {$message}", $ex->getMessage());
        }
    }

    /**
     * Create a course set with capability.
     */
    public function test_resolver_save_courseset_with_capability() {
        $user = $this->getDataGenerator()->create_user();
        $role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($role, $user->id);

        try {
            $program = new program_class($this->program1->id);
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
        $course_ids = [];

        $course_ids[] = $this->course1->id;
        $course_ids[] = $this->course2->id;
        $course_ids[] = $this->course3->id;

        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
            ],
        ];

        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check $program_courseset1 has created.
            self::assertContains($result->id, $coursesetids);
        } catch (\exception $ex) {
            self::fail('Unexpected exception: ' . $ex->getMessage());
        }
    }

    /**
     * Create a course set with valid args.
     */
    public function test_resolver_save_courseset_valid_course_set_arg() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->setAdminUser();
        $course_ids = [];

        $course_ids[] = $this->course1->id;
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
            ],
        ];

        try {
            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check $program_courseset1 has created.
            self::assertContains($result->id, $coursesetids);
        } catch (\exception $ex) {
            self::fail('Unexpected error: ' . $ex->getMessage());
        }
    }

    /**
     * Add course to existing course set.
     */
    public function test_resolver_save_courseset_add_course() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->setAdminUser();
        $course_ids = [];

        $course_ids[] = $this->course1->id;
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
            ],
        ];

        try {
            $result1 = $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check program_courseset1 has created.
            self::assertContains($result1->id, $coursesetids);

            // Add new course to the course set
            $course_ids[] = $this->course2->id;
            $args = [
                'input' => [
                    'program_id' => $this->program1->id,
                    'course_ids' => $course_ids,
                    'id' => $result1->id,
                    'completion_type' => 'ALL'
                ],
            ];

            $result2 = $this->resolve_graphql_mutation(self::MUTATION, $args);
            // Get all courses in the course set
            $courseset_entity = new program_courseset_entity($result2->id);
            $courses_model = program_courseset_model::from_entity($courseset_entity);
            $courses = $courses_model->get_courses();

            self::assertCount(2, $courses);
        } catch (\exception $ex) {
            self::fail('Unexpected error: ' . $ex->getMessage());
        }
    }

    /**
     * Remove course from existing course set.
     */
    public function test_resolver_save_courseset_remove_course() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->setAdminUser();
        $course_ids = [];

        $course_ids[] = $this->course1->id;
        $course_ids[] = $this->course2->id;
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
            ],
        ];

        try {
            $result1 = $this->resolve_graphql_mutation(self::MUTATION, $args);

            // Get all course set in the program
            $course_sets = program_courseset_entity::repository()
                ->where('programid', $this->program1->id)->get()->to_array();
            $coursesetids = array_column($course_sets, 'id');

            // Check program_courseset1 has created.
            self::assertContains($result1->id, $coursesetids);

            // Remove course2 from courseset
            unset($course_ids[1]);
            $args = [
                'input' => [
                    'program_id' => $this->program1->id,
                    'course_ids' => $course_ids,
                    'id' => $result1->id,
                    'completion_type' => 'ALL'
                ],
            ];

            $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
            // Get all courses in the course set
            $courseset_entity = new program_courseset_entity($result->id);
            $courses_model = program_courseset_model::from_entity($courseset_entity);
            $courses = $courses_model->get_courses();

            self::assertCount(1, $courses);
        } catch (\exception $ex) {
            self::fail('Unexpected error: ' . $ex->getMessage());
        }
    }

    /**
     * Add invalid course id .
     */
    public function test_resolver_save_courseset_add_invalid_course_id() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->setAdminUser();
        $course_ids = [];

        $course_ids[] = 100000;
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Coding error detected, it must be fixed by a programmer: Invalid course id.', $ex->getMessage());
        }
    }

    /**
     * Add invalid completion type.
     */
    public function test_resolver_save_courseset_add_invalid_completion_type() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->setAdminUser();
        $course_ids = [];

        $course_ids[] = $this->course1->id;
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'INVALID'
            ],
        ];

        try {
            $this->resolve_graphql_mutation(self::MUTATION, $args);
            self::fail('Expected an exception');
        } catch (\exception $ex) {
            self::assertSame('Coding error detected, it must be fixed by a programmer: Invalid completion type.', $ex->getMessage());
        }
    }

    /**
     * @return void
     */
    public function test_resolver_save_courseset_with_xss_input(): void {
        self::setAdminUser();

        $course_ids = [];
        $course_ids[] = $this->course1->id;

        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'label' => "<script>alert('XSS');</script>",
                'completion_type' => 'ALL'
            ],
        ];

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertEquals('alert(\'XSS\');', $result->label);
    }

    /**
     * @return void
     */
    public function test_resolver_save_courseset_with_different_tenancy(): void {
        self::setAdminUser();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $this->generator->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        $course_t1 = $this->generator->create_course(['category' => $tenant1->categoryid]);
        $course_t2 = $this->generator->create_course(['category' => $tenant2->categoryid]);

        $course_ids[] = $this->course1->id;
        $course_ids[] = $course_t1->id;
        $course_ids[] = $course_t2->id;

        $program_t1 = $this->program_generator->create_program(['category' => $tenant1->categoryid]);

        self::expectExceptionMessage("Course {$course_t2->id} can not be added into the program.");
        self::expectException(moodle_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'program_id' => $program_t1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL'
            ],
        ]);
    }

    public function test_resolver_save_courseset_with_additional_arguments(): void {
        self::setAdminUser();

        $course_ids = [];
        $course_ids[] = $this->course1->id;

        // First create a new course set with one course
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => null,
                'completion_type' => 'ALL',
                // Non-standard arguments from here:
                'next_set_operator' => 4,
                'competency_id' => 100,
                'recurrence_time' => 50,
                'recur_create_time' => 333,
                'certif_path' => 5,
                'content_type' => 999
            ],
        ];

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertEquals(4, $result->nextsetoperator);
        self::assertEquals(100, $result->competencyid);
        self::assertEquals(50, $result->recurrencetime);
        self::assertEquals(333, $result->recurcreatetime);
        self::assertEquals(5, $result->certifpath);
        self::assertEquals(999, $result->contenttype);

        // Now update the course set to ensure new values are recorded properly
        // This call also functions to ensure that values are not overwritten when not specified.
        $args = [
            'input' => [
                'program_id' => $this->program1->id,
                'course_ids' => $course_ids,
                'id' => $result->id,
                'completion_type' => 'ALL',
                // Non-standard arguments from here:
                'next_set_operator' => 56,
                'competency_id' => 976,
                'recurrence_time' => 8,
                'recur_create_time' => 42,
                // Don't pass certif_path or content_type so we can check that we get the same course set back
            ],
        ];

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertEquals(56, $result->nextsetoperator);
        self::assertEquals(976, $result->competencyid);
        self::assertEquals(8, $result->recurrencetime);
        self::assertEquals(42, $result->recurcreatetime);
        self::assertEquals(5, $result->certifpath);
        self::assertEquals(999, $result->contenttype);
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

        parent::tearDown();
    }
}
