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
use core_phpunit\testcase;
use totara_cohort\exception\cohort_is_user_member_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class core_webapi_query_cohort_is_user_member_test extends testcase {
    private const QUERY = 'core_cohort_is_user_member';

    use webapi_phpunit_helper;

    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');
    }

    /**
     * @covers ::resolve
     */
    public function test_query_cohort_static_cohorts(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $user1 = $gen->create_user();
        $cohortgen = \totara_cohort\testing\generator::instance();
        $cohort = $cohortgen->create_cohort();
        $cohort1 = $cohortgen->create_cohort();
        $cohortgen->cohort_assign_users($cohort->id, [$user->id]);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user->id
            ],
            'cohort' => [
                'idnumber' => $cohort->idnumber,
                'id' => $cohort->id
            ]
        ]);

        self::assertTrue($result['user_is_member']);

        // Incorrect user id;
        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user1->id
            ],
            'cohort' => [
                'id' => $cohort->id
            ]
        ]);

        self::assertFalse($result['user_is_member']);

        // Incorrect cohort id;
        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user->id
            ],
            'cohort' => [
                'id' => $cohort1->id
            ]
        ]);
        self::assertFalse($result['user_is_member']);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_param_exception(): void {
        self::setAdminUser();

        self::expectExceptionMessage('User reference is required.');
        self::expectException(cohort_is_user_member_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'user' => [
            ],
            'cohort' => [
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_apiuser(): void {
        $gen = self::getDataGenerator();
        $api_user = $gen->create_user();

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $api_user->id, context_system::instance());

        assign_capability('moodle/cohort:view', CAP_ALLOW, $role->id, SYSCONTEXTID);

        self::setUser($api_user);

        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $user1 = $gen->create_user();
        $cohortgen = \totara_cohort\testing\generator::instance();
        $cohort = $cohortgen->create_cohort();
        $cohortgen->cohort_assign_users($cohort->id, [$user->id]);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user->id
            ],
            'cohort' => [
                'idnumber' => $cohort->idnumber,
                'id' => $cohort->id
            ]
        ]);

        self::assertTrue($result['user_is_member']);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user1->id
            ],
            'cohort' => [
                'id' => $cohort->id
            ]
        ]);

        self::assertFalse($result['user_is_member']);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_tenancy(): void {
        $gen = self::getDataGenerator();

        $generator = \totara_cohort\testing\generator::instance();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        // tennant manager.
        $user1_t1 = $gen->create_user(['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]);
        $user2_t1 = $gen->create_user(['tenantid' => $tenant1->id]);

        $user1_t2 = $gen->create_user(['tenantid' => $tenant2->id]);
        $user2_t2 = $gen->create_user(['tenantid' => $tenant2->id]);

        $system_user = $gen->create_user();

        $tenant1_category_context = context_coursecat::instance($tenant1->categoryid);
        $tenant2_category_context = context_coursecat::instance($tenant2->categoryid);

        $cohort1 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);
        $generator->cohort_assign_users($cohort1->id, [$user1_t1->id, $user2_t1->id]);

        $cohort2 = $generator->create_cohort(['contextid' => $tenant2_category_context->id]);
        $generator->cohort_assign_users($cohort2->id, [$user1_t2->id]);
        $cohort3 = $generator->create_cohort();

        // Login as tenant1 domain manager
        self::setUser($user1_t1);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user2_t1->id
            ],
            'cohort' => [
                'id' => $cohort1->id
            ]
        ]);

        self::assertTrue($result['user_is_member']);

        try {
            // Check tenant2
            $this->resolve_graphql_query(self::QUERY, [
                'user' => [
                    'id' => $user1_t2->id
                ],
                'cohort' => [
                    'id' => $cohort1->id
                ]
            ]);
            $this->fail('Exception expected');
        } catch (Exception $exception) {
            self::assertEquals('Can not view the target user.', $exception->getMessage());
        }

        // cohort under different tenant.
        try {
            $this->resolve_graphql_query(self::QUERY, [
                'user' => [
                    'id' => $user2_t1->id
                ],
                'cohort' => [
                    'id' => $cohort2->id
                ]
            ]);
            $this->fail('Exception expected');
        } catch (Exception $exception) {
            self::assertEquals('You do not have capabilities to view a target audience.', $exception->getMessage());
            $this->assertInstanceOf(unresolved_record_reference::class, $exception);
        }

        // System cohort.
        try {
            $this->resolve_graphql_query(self::QUERY, [
                'user' => [
                    'id' => $user2_t1->id
                ],
                'cohort' => [
                    'id' => $cohort3->id
                ]
            ]);
            $this->fail('Exception expected');
        } catch (Exception $exception) {
            self::assertEquals('You do not have capabilities to view a target audience.', $exception->getMessage());
            $this->assertInstanceOf(unresolved_record_reference::class, $exception);
        }

        self::setAdminUser();
        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user1_t2->id
            ],
            'cohort' => [
                'id' => $cohort2->id
            ]
        ]);

        self::assertTrue($result['user_is_member']);

        $api_user = $gen->create_user();
        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $api_user->id, context_system::instance());
        assign_capability('moodle/cohort:view', CAP_ALLOW, $role->id, SYSCONTEXTID);

        // System api user can see tenant cohort
        self::setUser($api_user);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user1_t2->id
            ],
            'cohort' => [
                'id' => $cohort2->id
            ]
        ]);

        self::assertTrue($result['user_is_member']);

        self::setAdminUser();
        $tenant_api_user = $gen->create_user(['tenantid' => $tenant1->id]);

        role_assign($role->id, $tenant_api_user->id, context_system::instance());
        assign_capability('moodle/cohort:view', CAP_ALLOW, $role->id, SYSCONTEXTID);

        // Login as tenant api user
        self::setUser($tenant_api_user);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => $user2_t1->id
            ],
            'cohort' => [
                'id' => $cohort1->id
            ]
        ]);

        self::assertTrue($result['user_is_member']);

        try {
            $this->resolve_graphql_query(self::QUERY, [
                'user' => [
                    'id' => $user2_t1->id
                ],
                'cohort' => [
                    'id' => $cohort2->id
                ]
            ]);
            $this->fail('Exception expected');
        } catch (Exception $exception) {
            self::assertStringContainsString('You do not have capabilities to view a target audience.', $exception->getMessage());
            $this->assertInstanceOf(unresolved_record_reference::class, $exception);
        }
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_authenticated_user(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        self::setUser($user);

        self::expectExceptionMessage('You do not have capabilities to view or manage audiences.');
        self::expectException(cohort_is_user_member_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'user' => [
                'id' => '3'
            ],
            'cohort' => [
                'id' => '5'
            ]
        ]);
    }
}