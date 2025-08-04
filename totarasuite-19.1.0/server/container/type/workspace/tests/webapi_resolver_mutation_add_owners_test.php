<?php
/**
 * This file is part of Totara Engage
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package container_workspace
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;
use container_workspace\testing\generator as workspace_generator;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @group container_workspace
 */
class container_workspace_webapi_resolver_mutation_add_owners_test extends testcase {
    private const MUTATION = 'container_workspace_add_owners';

    use webapi_phpunit_helper;

    public function test_successful_query(): void {
        $generator = $this->getDataGenerator();
        $owner = $generator->create_user();
        self::setUser($owner);

        $workspace_generator = workspace_generator::instance();
        $workspace = $workspace_generator->create_workspace();
        $user1 = $generator->create_user();
        $member1 = $workspace_generator->create_self_join_member($workspace, $user1->id);
        $user2 = $generator->create_user();
        $member2 = $workspace_generator->create_self_join_member($workspace, $user2->id);

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => [$user1->id, $user2->id]
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertEmpty($errors, "successful add has errors: " . print_r($errors, true));
        static::assertTrue($result, 'add owners failed');
        $workspace->reload();
        static::assertCount(3, $workspace->owners());
    }

    public function test_owner_can_add_others_including_self(): void {
        $generator = $this->getDataGenerator();
        $owner = $generator->create_user();
        self::setUser($owner);

        $workspace_generator = workspace_generator::instance();
        $workspace = $workspace_generator->create_workspace();
        $user1 = $generator->create_user();
        $member1 = $workspace_generator->create_self_join_member($workspace, $user1->id);
        $user2 = $generator->create_user();
        $member2 = $workspace_generator->create_self_join_member($workspace, $user2->id);

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => [$user1->id]
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertEmpty($errors, "successful add has errors: " . print_r($errors, true));
        static::assertTrue($result, 'add owners failed');
        $workspace->reload();
        static::assertCount(2, $workspace->owners());

        self::setUser($user1);

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => [$user2->id]
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertEmpty($errors, "successful add has errors: " . print_r($errors, true));
        static::assertTrue($result, 'add owners failed');
        $workspace->reload();
        static::assertCount(3, $workspace->owners());
    }

    public function test_user_already_owner(): void {
        $generator = $this->getDataGenerator();
        $owner = $generator->create_user();
        self::setUser($owner);

        $workspace_generator = workspace_generator::instance();
        $workspace = $workspace_generator->create_workspace();
        $user1 = $generator->create_user();
        $member1 = $workspace_generator->create_self_join_member($workspace, $user1->id);
        $user2 = $generator->create_user();
        $member2 = $workspace_generator->create_self_join_member($workspace, $user2->id);

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => [$user1->id]
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertEmpty($errors, "successful add has errors: " . print_r($errors, true));
        static::assertTrue($result, 'add owners failed');
        $workspace->reload();
        static::assertCount(2, $workspace->owners());

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => [$user1->id]
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertNotEmpty($errors, 'User is already an owner of the workspace');
        static::assertFalse($result, 'add owners failed');
        $workspace->reload();
        static::assertCount(2, $workspace->owners());
    }

    public function test_member_cant_add_others(): void {
        $generator = $this->getDataGenerator();
        $owner = $generator->create_user();
        self::setUser($owner);

        $workspace_generator = workspace_generator::instance();
        $workspace = $workspace_generator->create_workspace();
        $user1 = $generator->create_user();
        $member1 = $workspace_generator->create_self_join_member($workspace, $user1->id);
        $user2 = $generator->create_user();
        $member2 = $workspace_generator->create_self_join_member($workspace, $user2->id);

        self::setUser($user1);
        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => [$user1->id, $user2->id]
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertNotEmpty($errors, 'No permission to add owners to this workspace');
        static::assertFalse($result, 'add owners failed');
        $workspace->reload();
        static::assertCount(1, $workspace->owners());
    }

    public function test_guest_cant_add_owners(): void {
        $generator = $this->getDataGenerator();
        $owner = $generator->create_user();
        self::setUser($owner);

        $workspace_generator = workspace_generator::instance();
        $workspace = $workspace_generator->create_workspace();
        $user1 = $generator->create_user();
        $member1 = $workspace_generator->create_self_join_member($workspace, $user1->id);
        $user2 = $generator->create_user();
        $member2 = $workspace_generator->create_self_join_member($workspace, $user2->id);

        self::setGuestUser();

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => [$user1->id, $user2->id]
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertNotEmpty($errors, 'Actor does not have ability to add workspace owner');
        static::assertFalse($result, 'add owners successful');
        $workspace->reload();
        static::assertCount(1, $workspace->owners());
    }
}
