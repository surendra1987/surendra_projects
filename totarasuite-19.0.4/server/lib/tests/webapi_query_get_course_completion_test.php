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

use core\entity\user;
use core\exception\unresolved_record_reference;
use core\orm\query\builder;
use core_phpunit\testcase;
use totara_tenant\testing\generator as tenant_generator;
use totara_webapi\phpunit\webapi_phpunit_helper;

class core_webapi_query_get_course_completion_test extends testcase {
    private const QUERY = 'core_completion_get_course_completion';

    use webapi_phpunit_helper;

    /**
     * @covers ::resolve
     */
    public function test_no_user_params(): void {
        self::setAdminUser();

        $course = self::getDataGenerator()->create_course();

        self::expectExceptionMessage("User reference is required.");
        self::expectException(invalid_parameter_exception::class);
        self::resolve_graphql_query(self::QUERY, [
            'query' => [
                'user' => [],
                'course' => [
                    'id' => $course->id,
                ],
            ],
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_no_course_params(): void {
        self::setAdminUser();

        self::expectExceptionMessage("Course reference is required.");
        self::expectException(invalid_parameter_exception::class);
        self::resolve_graphql_query(self::QUERY, [
            'query' => [
                'user' => [
                    'id' => user::logged_in()->id,
                ],
                'course' => [],
            ],
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_get_course_completion(): void {
        self::setAdminUser();

        $generator = self::getDataGenerator();

        $user = $generator->create_user();
        $course = $generator->create_course(['enablecompletion' => 1]);

        // Test with user not yet enrolled - we should get a result but the completion data should be empty.
        $result = self::resolve_graphql_query(self::QUERY, [
            'query' => [
                'user' => [
                    'id' => $user->id,
                ],
                'course' => [
                    'id' => $course->id,
                ],
            ]
        ]);

        self::assertEquals($user->id, $result['user']->id);
        self::assertEquals($course->id, $result['course']->id);
        self::assertNull($result['completion']);

        // Test with an enrolled user who has a completion record.
        $generator->enrol_user($user->id, $course->id);

        $result = self::resolve_graphql_query(self::QUERY, [
            'query' => [
                'user' => [
                    'id' => $user->id,
                ],
                'course' => [
                    'id' => $course->id,
                ],
            ]
        ]);

        self::assertEquals($user->id, $result['user']->id);
        self::assertEquals($course->id, $result['course']->id);
        self::assertEquals($user->id, $result['completion']->userid);
        self::assertEquals($course->id, $result['completion']->course);
        self::assertEquals(0, $result['completion']->timestarted);
        self::assertNull($result['completion']->timecompleted);
        self::assertEquals(10, $result['completion']->status);
    }

    /**
     * @covers ::resolve
     */
    public function test_get_course_completion_by_tenant_user(): void {
        $generator = self::getDataGenerator();
        $tenant_generator = tenant_generator::instance();

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        $course_t1 = $generator->create_course(['enablecompletion' => 1, 'category' => $tenant1->categoryid]);
        $course_t2 = $generator->create_course(['enablecompletion' => 1, 'category' => $tenant2->categoryid]);

        $user1_t1 = $generator->create_user(['tenantid' => $tenant1->id]);
        $user2_t1 = $generator->create_user(['tenantid' => $tenant1->id]);
        $user_t2 = $generator->create_user(['tenantid' => $tenant2->id]);

        $apiuser_role = builder::table('role')
            ->where('shortname', 'apiuser')
            ->one(true);
        $generator->role_assign($apiuser_role->id, $user1_t1->id, context_tenant::instance($tenant1->id));

        // Enrol one user in each course in each tenant.
        $generator->enrol_user($user2_t1->id, $course_t1->id);
        $generator->enrol_user($user_t2->id, $course_t2->id);

        // Run the test as the api user from tenant 1.
        self::setUser($user1_t1);

        $result = self::resolve_graphql_query(self::QUERY, [
            'query' => [
                'user' => [
                    'id' => $user2_t1->id,
                ],
                'course' => [
                    'id' => $course_t1->id,
                ],
            ]
        ]);

        self::assertEquals($user2_t1->id, $result['user']->id);
        self::assertEquals($course_t1->id, $result['course']->id);
        self::assertEquals($user2_t1->id, $result['completion']->userid);
        self::assertEquals($course_t1->id, $result['completion']->course);
        self::assertEquals(0, $result['completion']->timestarted);
        self::assertNull($result['completion']->timecompleted);
        self::assertEquals(10, $result['completion']->status);

        // Make sure that the tenant 1 api user cannot access the tenant 2 course completion.
        self::expectException(unresolved_record_reference::class);
        self::expectExceptionMessage('Can not view the target user.');
        self::resolve_graphql_query(self::QUERY, [
            'query' => [
                'user' => [
                    'id' => $user_t2->id,
                ],
                'course' => [
                    'id' => $course_t2->id,
                ],
            ]
        ]);
    }
}