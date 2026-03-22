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

use core\orm\query\builder;
use core_phpunit\testcase;
use core_user\exception\get_user_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class core_webapi_query_user_user_test extends testcase {
    private const QUERY = 'core_user_user';

    use webapi_phpunit_helper;

    /**
     * @covers ::resolve
     */
    public function test_get_user_no_required_params(): void {
        self::setAdminUser();

        self::expectExceptionMessage("Please set one of user reference.");
        self::expectException(get_user_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'reference' => []
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_get_user(): void {
        self::setAdminUser();

        $user = self::getDataGenerator()->create_user();

        $result = $this->resolve_graphql_query(self::QUERY, [
            'reference' => [
                'id' => $user->id,
                'idnumber' => $user->idnumber
            ]
        ]);

        self::assertEquals($user->id, $result['user']->id);
    }

    /**
     * @covers ::resolve
     */
    public function test_get_mutlple_users_with_same_email(): void {
        set_config('allowaccountssameemail', 1);
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $gen->create_user(['email' => 'www@example.com']);
        $gen->create_user(['email' => 'www@example.com']);


        self::expectExceptionMessage("Multiple users returned with the same email.");
        self::expectException(get_user_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'reference' => [
                'email' => 'www@example.com'
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_get_user_by_tenant(): void {
        $gen = self::getDataGenerator();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        $user1_t1 = $gen->create_user(['tenantid' => $tenant1->id]);
        $user2_t1 = $gen->create_user(['tenantid' => $tenant1->id]);
        $user_t2 = $gen->create_user(['tenantid' => $tenant2->id]);

        $apiuser_role = builder::table('role')
            ->where('shortname', 'apiuser')
            ->one(true);
        $gen->role_assign($apiuser_role->id, $user1_t1->id, context_tenant::instance($tenant1->id));

        // Login as user tenant 1;
        self::setUser($user1_t1);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'reference' => [
                'id' => $user2_t1->id,
                'idnumber' => $user2_t1->idnumber
            ]
        ]);

        self::assertEquals($user2_t1->id, $result['user']->id);

        // Check tenant2.
        try {
            $this->resolve_graphql_query(self::QUERY, [
                'reference' => [
                    'id' => $user_t2->id,
                    'idnumber' => $user_t2->idnumber
                ]
            ]);
            self::fail('get_user_exception expected');
        } catch (get_user_exception $exception) {
            self::assertStringContainsString('Can not view the target user.', $exception->getMessage());
        }

        // Check system user.
        $system_user = $gen->create_user();
        try {
            $this->resolve_graphql_query(self::QUERY, [
                'reference' => [
                    'id' => $system_user->id,
                    'idnumber' => $system_user->idnumber
                ]
            ]);
            self::fail('get_user_exception expected');
        } catch (get_user_exception $exception) {
            self::assertStringContainsString('Can not view the target user.', $exception->getMessage());
        }

        $tenant2_participant = $gen->create_user();
        $tenant_generator->set_user_participation(
            $tenant2_participant->id,
            [$tenant2->id]
        );

        // Check tenant2 participant
        try {
            $this->resolve_graphql_query(self::QUERY, [
                'reference' => [
                    'id' => $tenant2_participant->id,
                    'idnumber' => $tenant2_participant->idnumber
                ]
            ]);
            self::fail('get_user_exception expected');
        } catch (get_user_exception $exception) {
            self::assertStringContainsString('Can not view the target user.', $exception->getMessage());
        }

        $tenant1_participant = $gen->create_user();
        $tenant_generator->set_user_participation(
            $tenant1_participant->id,
            [$tenant1->id]
        );

        // Check tenant1 participant
        $result = $this->resolve_graphql_query(self::QUERY, [
            'reference' => [
                'id' => $tenant1_participant->id,
                'idnumber' => $tenant1_participant->idnumber
            ]
        ]);
        self::assertEquals($tenant1_participant->id, $result['user']->id);
    }

    /**
     * @covers ::resolve
     */
    public function test_get_user_by_apiuser(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $target_user = $gen->create_user();

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $user->id, context_system::instance());

        self::setUser($user);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'reference' => [
                'id' => $target_user->id,
                'idnumber' => $target_user->idnumber
            ]
        ]);

        self::assertEquals($target_user->id, $result['user']->id);
    }
}