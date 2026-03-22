<?php
/**
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package container_workspace
 */

use container_workspace\member\member;
use container_workspace\member\owner_addition_conditions;
use container_workspace\testing\generator as workspace_generator;
use core\collection;
use core\entity\user;
use core_phpunit\testcase;
use totara_tenant\testing\generator as tenant_generator;

/**
 * @group container_workspace
 */
class container_workspace_owner_addition_conditions_test extends testcase {

    public function test_is_suspended_user(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        member::added_to_workspace($workspace, $user2->id);
        user_suspend_user($user2->id);

        $result = owner_addition_conditions::create($workspace, $user2->id)->evaluate();
        static::assertFalse($result->is_fulfilled);
        static::assertEquals(owner_addition_conditions::ERR_IS_SUSPENDED_USER, $result->code);
    }

    public function test_is_already_an_owner(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        $workspace_generator->add_workspace_owners($workspace, new collection([new user($user2)]));

        $result = owner_addition_conditions::create($workspace, $user2->id)->evaluate();
        static::assertFalse($result->is_fulfilled);
        static::assertEquals(owner_addition_conditions::ERR_ALREADY_AN_OWNER, $result->code);
    }

    public function test_is_member(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        $result = owner_addition_conditions::create($workspace, $user2->id)->evaluate();
        static::assertFalse($result->is_fulfilled);
        static::assertEquals(owner_addition_conditions::ERR_NOT_A_MEMBER, $result->code);
    }

    public function test_multitenancy_same_tenant(): void {
        [
            $workspace,
            $user_two,
        ] = $this->setup_workspace_with_multitenancy();

        member::added_to_workspace($workspace, $user_two->id);

        $result = owner_addition_conditions::create($workspace, $user_two->id)->evaluate();
        static::assertTrue($result->is_fulfilled);
    }

    public function test_multitenancy_wrong_tenant(): void {
        [
            $workspace,
            $user_two,
            $tenant_two,
        ] = $this->setup_workspace_with_multitenancy();

        // Switch tenant for user two.
        $generator = static::getDataGenerator();
        /** @var tenant_generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        $tenant_generator->migrate_user_to_tenant($user_two->id, $tenant_two->id);

        $result = owner_addition_conditions::create($workspace, $user_two->id)->evaluate();
        static::assertFalse($result->is_fulfilled);
        static::assertEquals(owner_addition_conditions::ERR_NO_ACCESS, $result->code);
    }

    private function setup_workspace_with_multitenancy(): array {
        $generator = static::getDataGenerator();

        $user_one = $generator->create_user();
        $user_two = $generator->create_user();

        /** @var tenant_generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();

        $tenant_one = $tenant_generator->create_tenant();
        $tenant_two = $tenant_generator->create_tenant();

        $tenant_generator->migrate_user_to_tenant($user_one->id, $tenant_one->id);
        $tenant_generator->migrate_user_to_tenant($user_two->id, $tenant_one->id);

        // Login as first user and create workspace then add user to the workspace.
        $user_one->tenantid = $tenant_one->id;
        static::setUser($user_one);

        /** @var workspace_generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        member::added_to_workspace($workspace, $user_two->id);

        return [
            $workspace,
            $user_two,
            $tenant_two,
        ];
    }

    public function test_conditions_pass(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        member::added_to_workspace($workspace, $user2->id);

        $result = owner_addition_conditions::create($workspace, $user2->id)->evaluate();
        static::assertTrue($result->is_fulfilled);
    }
}
