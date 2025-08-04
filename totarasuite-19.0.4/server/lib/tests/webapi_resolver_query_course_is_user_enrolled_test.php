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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

use core\exception\unresolved_record_reference;
use core_course\exception\course_is_user_enrolled_exception;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

class core_webapi_resolver_query_course_is_user_enrolled_test extends testcase {

    use webapi_phpunit_helper;

    private const QUERY = 'core_course_is_user_enrolled';

    /**
     * @covers ::resolve
     */
    public function test_course_is_user_enrolled(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $user1 = $gen->create_user();
        $user2 = $gen->create_user();

        $course = $gen->create_course();

        $gen->enrol_user($user1->id, $course->id);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'user' => [
                    'id' => $user1->id
                ],
                'course' => [
                    'course' => $course->id
                ],
            ]
        );

        // Enrolled
        self::assertTrue($result['user_currently_enrolled']);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'user' => [
                    'id' => $user2->id
                ],
                'course' => [
                    'course' => $course->id
                ],
            ]
        );

        // Not enrolled
        self::assertFalse($result['user_currently_enrolled']);
    }

    /**
     * @covers ::resolve
     */
    public function test_param_exception(): void {
        self::setAdminUser();

        self::expectExceptionMessage('User reference is required.');
        self::expectException(course_is_user_enrolled_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'user' => [
            ],
            'course' => [
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_nonexistent_course_exception(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        self::expectExceptionMessage('Course reference not found');
        self::expectException(unresolved_record_reference::class);
        $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user->id
            ],
            'course' => [
                'id' => 6
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_invalid_container_type_exception(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $gen->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        self::expectExceptionMessage('Course reference not found');
        self::expectException(unresolved_record_reference::class);
        $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user->id
            ],
            'course' => [
                'id' => $workspace->get_id()
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_for_apiuser(): void {
        $this->setup_apiuser();

        $gen = self::getDataGenerator();
        $user1 = $gen->create_user();
        $course = $gen->create_course();

        $gen->enrol_user($user1->id, $course->id);
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'user' => [
                    'id' => $user1->id
                ],
                'course' => [
                    'course' => $course->id
                ],
            ]
        );

        // Enrolled
        self::assertTrue($result['user_currently_enrolled']);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_for_multitancy(): void {
        $gen = self::getDataGenerator();
        $generator = \totara_cohort\testing\generator::instance();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();
        self::setup_apiuser($tenant1->id);

        // tennant manager.
        $user_t1 = $gen->create_user(['tenantid' => $tenant1->id]);
        $user_t2 = $gen->create_user(['tenantid' => $tenant2->id]);


        $course_t1 = $gen->create_course(['category' => $tenant1->categoryid]);
        $course_t2 = $gen->create_course(['category' => $tenant2->categoryid]);
        $course_t3 = $gen->create_course();

        $gen->enrol_user($user_t1->id, $course_t1->id);
        $gen->enrol_user($user_t2->id, $course_t2->id);
        // course and user under same tenancy.
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'user' => [
                    'id' => $user_t1->id
                ],
                'course' => [
                    'id' => $course_t1->id
                ],
            ]
        );
        // Enrolled
        self::assertTrue($result['user_currently_enrolled']);

        // different course tenancy.
        try {
            $result = $this->resolve_graphql_query(
                self::QUERY,
                [
                    'user' => [
                        'id' => $user_t1->id
                    ],
                    'course' => [
                        'id' => $course_t2->id
                    ],
                ]
            );

            self::fail('unresolved_record_reference expected');
        } catch (unresolved_record_reference $exception) {
            self::assertStringContainsString('You do not have capabilities to view a target course.', $exception->getMessage());
        }

        // Admin can see all tenant users.
        self::setAdminUser();
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'user' => [
                    'id' => $user_t2->id
                ],
                'course' => [
                    'id' => $course_t2->id
                ],
            ]
        );
        // Enrolled
        self::assertTrue($result['user_currently_enrolled']);

        // Course under child category.
        $child_category = $gen->create_category(['parent' => $tenant1->categoryid]);
        $child_course = $gen->create_course(['category' => $child_category->id]);
        $gen->enrol_user($user_t1->id, $child_course->id);
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'user' => [
                    'id' => $user_t1->id
                ],
                'course' => [
                    'id' => $child_course->id
                ],
            ]
        );
        // Enrolled
        self::assertTrue($result['user_currently_enrolled']);

        // Login as tenant2 API user
        self::setup_apiuser($tenant2->id);

        self::expectExceptionMessage('Can not view the target user.');
        self::expectException(core\exception\unresolved_record_reference::class);
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'user' => [
                    // Tenant1 member user
                    'id' => $user_t1->id
                ],
                'course' => [
                    // course under tenant2
                    'id' => $course_t2->id
                ],
            ]
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_query_for_systemuser(): void {
        $gen = self::getDataGenerator();
        $user1 = $gen->create_user();
        $user = $gen->create_user();
        $course = $gen->create_course();

        self::setUser($user);

        self::expectExceptionMessage('You do not have capabilities to view a target user.');
        self::expectException(core\exception\unresolved_record_reference::class);
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'user' => [
                    'id' => $user1->id
                ],
                'course' => [
                    'course' => $course->id
                ],
            ]
        );
    }

    /**
     * @param int|null $tenant_id
     * @return array|stdClass
     */
    private function setup_apiuser(int $tenant_id = null) {
        $gen = self::getDataGenerator();

        $options = [];
        if ($tenant_id) {
            $options['tenantid'] = $tenant_id;
        }
        $api_user = $gen->create_user($options);

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $api_user->id, context_system::instance());

        self::setUser($api_user);
        return $api_user;
    }
}