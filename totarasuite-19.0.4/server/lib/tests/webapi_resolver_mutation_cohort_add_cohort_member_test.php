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
use core\orm\query\builder;
use core_phpunit\testcase;
use totara_cohort\exception\cohort_add_cohort_member_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class core_webapi_resolver_mutation_cohort_add_cohort_member_test extends testcase {
    private const MUTATION = 'core_cohort_add_cohort_member';

    use webapi_phpunit_helper;

    /**
     * @covers ::resolve
     */
    public function test_cohort_add_cohort_member(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $user1 = $gen->create_user();

        $cohortgen = \totara_cohort\testing\generator::instance();
        $cohort = $cohortgen->create_cohort();
        $cohortgen->cohort_assign_users($cohort->id, [$user->id]);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user->id
                ],
                'cohort' => [
                    'id' => $cohort->id
                ]
            ]
        );

        self::assertEquals($user->id, $result['user']->id);
        self::assertEquals($cohort->id, $result['cohort']->id);
        self::assertTrue($result['success']);
        self::assertTrue($result['was_already_member']);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user1->id
                ],
                'cohort' => [
                    'id' => $cohort->id
                ]
            ]
        );

        self::assertEquals($user1->id, $result['user']->id);
        self::assertEquals($cohort->id, $result['cohort']->id);
        self::assertTrue($result['success']);
        self::assertFalse($result['was_already_member']);
        self::assertTrue(
            builder::get_db()->record_exists('cohort_members', ['cohortid' => $cohort->id, 'userid' => $user1->id])
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_dynamic_cohort_exception(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        self::expectExceptionMessage('You can only add member to static audience.');
        self::expectException(cohort_add_cohort_member_exception::class);
        $cohortgen = \totara_cohort\testing\generator::instance();
        $cohort = $cohortgen->create_cohort(['cohorttype' => 2]);
        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user->id
                ],
                'cohort' => [
                    'id' => $cohort->id
                ]
            ]
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_mutation_with_tenancy(): void {
        $gen = self::getDataGenerator();

        $generator = \totara_cohort\testing\generator::instance();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        // Tenant domain manager.
        $user1_t1 = $gen->create_user(['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]);
        $user2_t1 = $gen->create_user(['tenantid' => $tenant1->id]);

        $tenant1_category_context = context_coursecat::instance($tenant1->categoryid);
        $cohort_t1 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);

        self::setUser($user1_t1);
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user2_t1->id
                ],
                'cohort' => [
                    'id' => $cohort_t1->id
                ]
            ]
        );

        self::assertEquals($user2_t1->id, $result['user']->id);
        self::assertEquals($cohort_t1->id, $result['cohort']->id);
        self::assertTrue($result['success']);
        self::assertFalse($result['was_already_member']);
        self::assertTrue(
            builder::get_db()->record_exists('cohort_members', ['cohortid' => $cohort_t1->id, 'userid' => $user2_t1->id])
        );

        $api_user = $gen->create_user();
        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $api_user->id, context_system::instance());
        assign_capability('moodle/cohort:manage', CAP_ALLOW, $role->id, SYSCONTEXTID);

        // Login as system api user
        self::setUser($api_user);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user2_t1->id
                ],
                'cohort' => [
                    'id' => $cohort_t1->id
                ]
            ]
        );

        self::assertEquals($user2_t1->id, $result['user']->id);
        self::assertEquals($cohort_t1->id, $result['cohort']->id);
        self::assertTrue($result['success']);
        self::assertTrue($result['was_already_member']);
    }

    /**
     * @covers ::resolve
     */
    public function test_mutation_with_tenancy_exception(): void {
        $gen = self::getDataGenerator();

        $generator = \totara_cohort\testing\generator::instance();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        // Tenant domain manager.
        $user1_t1 = $gen->create_user(['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]);
        $user2_t1 = $gen->create_user(['tenantid' => $tenant1->id]);

        $user1_t2 = $gen->create_user(['tenantid' => $tenant2->id, 'tenantdomainmanager' => $tenant2->idnumber]);
        $user2_t2 = $gen->create_user(['tenantid' => $tenant2->id]);

        $tenant1_category_context = context_coursecat::instance($tenant1->categoryid);
        $tenant2_category_context = context_coursecat::instance($tenant2->categoryid);

        $cohort_t1 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);
        $cohort_t2 = $generator->create_cohort(['contextid' => $tenant2_category_context->id]);

        // Login as tenant1 domain manager
        self::setUser($user1_t1);
        try {
            // Different tenant cohort.
            $this->resolve_graphql_mutation(
                self::MUTATION,
                [
                    'user' => [
                        'id' => $user2_t1->id
                    ],
                    'cohort' => [
                        'id' => $cohort_t2->id
                    ]
                ]
            );
            self::fail('unresolved_record_reference expected');
        } catch (unresolved_record_reference $exception) {
            self::assertStringContainsString('You do not have capabilities to view a target audience.', $exception->getMessage());
        }

        try {
            // Different tenant user.
            $this->resolve_graphql_mutation(
                self::MUTATION,
                [
                    'user' => [
                        'id' => $user1_t2->id
                    ],
                    'cohort' => [
                        'id' => $cohort_t1->id
                    ]
                ]
            );
            self::fail('unresolved_record_reference expected');
        } catch (unresolved_record_reference $exception) {
            self::assertStringContainsString('Can not view the target user.', $exception->getMessage());
        }
    }

    /**
     * @covers ::resolve
     */
    public function test_mutation_with_authenticated_user(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        self::setUser($user);

        self::expectExceptionMessage('Sorry, but you do not have manage or assign permissions to do that.');
        self::expectException(cohort_add_cohort_member_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION, [
            'user' => [
                'id' => '3'
            ],
            'cohort' => [
                'id' => '5'
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_mutation_with_apiuser(): void {
        $gen = self::getDataGenerator();
        $api_user = $gen->create_user();

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $api_user->id, context_system::instance());

        assign_capability('moodle/cohort:manage', CAP_ALLOW, $role->id, SYSCONTEXTID);

        self::setUser($api_user);

        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $user1 = $gen->create_user();

        $cohortgen = \totara_cohort\testing\generator::instance();
        $cohort = $cohortgen->create_cohort();
        $cohortgen->cohort_assign_users($cohort->id, [$user->id]);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user->id
                ],
                'cohort' => [
                    'id' => $cohort->id
                ]
            ]
        );

        self::assertEquals($user->id, $result['user']->id);
        self::assertEquals($cohort->id, $result['cohort']->id);
        self::assertTrue($result['success']);
        self::assertTrue($result['was_already_member']);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user1->id
                ],
                'cohort' => [
                    'id' => $cohort->id
                ]
            ]
        );

        self::assertEquals($user1->id, $result['user']->id);
        self::assertEquals($cohort->id, $result['cohort']->id);
        self::assertTrue($result['success']);
        self::assertFalse($result['was_already_member']);
        self::assertTrue(builder::get_db()->record_exists('cohort_members', ['cohortid'=>$cohort->id, 'userid'=>$user1->id]));
    }

    /**
     * @covers ::resolve
     */
    public function test_param_exception(): void {
        self::setAdminUser();

        self::expectExceptionMessage('User reference is required.');
        self::expectException(cohort_add_cohort_member_exception::class);
        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                ],
                'cohort' => [
                ]
            ]
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_system_api_user_can_add_member_to_cohort_under_multitancy(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();

        $generator = \totara_cohort\testing\generator::instance();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();
        $user_t1 = $gen->create_user(['tenantid' => $tenant1->id]);
        $user_t2 = $gen->create_user(['tenantid' => $tenant2->id]);

        $tenant1_category_context = context_coursecat::instance($tenant1->categoryid);
        $cohort_t1 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);

        // Tenant user1 can be added into tenant cohort1.
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user_t1->id
                ],
                'cohort' => [
                    'id' => $cohort_t1->id
                ]
            ]
        );

        self::assertEquals($user_t1->id, $result['user']->id);
        self::assertEquals($cohort_t1->id, $result['cohort']->id);
        self::assertTrue($result['success']);

        self::expectExceptionMessage('You cannot add the chosen user '.$user_t2->id.' into the specified audience '. $cohort_t1->id .'.');
        self::expectException(cohort_add_cohort_member_exception::class);
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'user' => [
                    'id' => $user_t2->id
                ],
                'cohort' => [
                    'id' => $cohort_t1->id
                ]
            ]
        );
    }
}