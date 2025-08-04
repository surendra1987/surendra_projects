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

use core\collection;
use totara_webapi\phpunit\webapi_phpunit_helper;
use container_workspace\interactor\workspace\interactor;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @group container_workspace
 */
class container_workspace_webapi_resolver_mutation_remove_owners_test extends container_workspace_multi_owner_testcase {
    private const MUTATION = 'container_workspace_remove_owners';

    use webapi_phpunit_helper;

    public function test_successful_query(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $owner1 = $owners->first();

        self::setAdminUser();

        // Remove one owner, at least one remaining owner left when the owners are removed from the workspace
        $owner_ids = $owners->filter(
            fn($owner) => $owner1->id !== $owner->id
        )->pluck('id');

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => $owner_ids
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertEmpty($errors, "successful remove has errors: " . print_r($errors, true));
        static::assertTrue($result, 'remove owners failed');
        $workspace->reload();
        static::assertCount(1, $workspace->owners());
    }

    public function test_owner_can_remove_other_owners(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $owner1 = $owners->first();
        static::setUser($owner1);

        $interactor1 = new interactor($workspace, $owner1->id);
        static::assertTrue($interactor1->is_owner());

        // Remove owner1 user from the owner list to test it can remove others
        $owner_ids = $owners->filter(
            fn($owner) => $owner1->id !== $owner->id
        )->pluck('id');

        $args = [
            'workspace_id' => $workspace->id,
            // Remove owner1 user from the owner list to test it can remove others
            'owner_ids' => $owner_ids
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertEmpty($errors, "successful remove has errors: " . print_r($errors, true));
        static::assertTrue($result, 'remove owners failed');
        // Created 4 owners, 3 removed, left 1
        $workspace->reload();
        static::assertCount(1, $workspace->owners());
    }

    public function test_owner_can_remove_others_including_self(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $owner1 = $owners->first();
        static::setUser($owner1);

        $interactor1 = new interactor($workspace, $owner1->id);
        static::assertTrue($interactor1->is_owner());

        // Remove owner4 user from the owner list to test it can remove others
        $owner4 = $owners->last();
        $owner_ids = $owners->filter(
            fn($owner) => $owner4->id !== $owner->id
        )->pluck('id');

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => $owner_ids
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertEmpty($errors, "successful remove has errors: " . print_r($errors, true));
        static::assertTrue($result, 'remove owners failed');
        $workspace->reload();
        static::assertCount(1, $workspace->owners());
    }

    public function test_owner_cant_remove_owners_with_limitation(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $owner = $owners->first();
        static::setUser($owner);

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => $owners->pluck('id')
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertCount(1, $errors);
        static::assertEquals(
            "Cannot remove the owners, at least one remaining owner should be left, add a new owner first",
            $errors[0]['message']
        );
        static::assertFalse($result, 'remove owners successful');
        $workspace->reload();
        // Created 4 owners, none removed
        static::assertCount(4, $workspace->owners());
    }

    public function test_member_cant_remove_owners(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $members = $testdata->normal_members;
        $member = $members->first();
        static::setUser($member);

        $interactor1 = new interactor($workspace, $member->id);
        static::assertFalse($interactor1->is_owner());

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => $owners->pluck('id')
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertCount(1, $errors);
        static::assertEquals('Actor does not have ability to remove workspace owner', $errors[0]['message']);
        static::assertFalse($result, 'remove owners successful');
        $workspace->reload();
        // Created 4 owners, none removed
        static::assertCount(4, $workspace->owners());
    }

    public function test_guest_cant_remove_owners(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;

        self::setGuestUser();

        $args = [
            'workspace_id' => $workspace->id,
            'owner_ids' => $owners->pluck('id')
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        [
            'success' => $result,
            'errors' => $errors
        ] = $this->get_webapi_operation_data($result);

        static::assertCount(1, $errors);
        static::assertEquals('Actor does not have ability to remove workspace owner', $errors[0]['message']);
        static::assertFalse($result, 'remove owners successful');
        $workspace->reload();
        // Created 4 owners, none removed
        static::assertCount(4, $workspace->owners());
    }
}
