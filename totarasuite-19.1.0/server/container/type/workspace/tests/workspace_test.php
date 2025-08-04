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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package container_workspace
 */

use core\entity\user;
use container_workspace\member\member;
use container_workspace\testing\generator as workspace_generator;
use core\collection;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @coversDefaultClass \container_workspace\workspace
 *
 * @group container_workspace
 */
class container_workspace_workspace_test extends container_workspace_multi_owner_testcase {
    public function test_workspace_user_is_role(): void {
        $testdata = $this->create_multi_owner_workspace();

        foreach ($testdata->owners as $user) {
            self::assertTrue(
                $testdata->workspace->is_owner($user),
                'user with owner role has wrong is_owner() value'
            );

            self::assertFalse(
                $testdata->workspace->is_member($user),
                'user with owner role has wrong is_member() value'
            );
        }

        foreach ($testdata->normal_members as $user) {
            self::assertFalse(
                $testdata->workspace->is_owner($user),
                'user with normal member role has wrong is_owner() value'
            );

            self::assertTrue(
                $testdata->workspace->is_member($user),
                'user with owner role has wrong is_member() value'
            );
        }

        foreach ($testdata->non_members as $user) {
            self::assertFalse(
                $testdata->workspace->is_owner($user),
                'user with non member role has wrong is_owner() value'
            );

            self::assertFalse(
                $testdata->workspace->is_member($user),
                'user with non member role has wrong is_member() value'
            );
        }
    }

    public function test_workspace_owners(): void {
        $testdata = $this->create_multi_owner_workspace();

        self::assertEquals(
            $testdata->owners->count(),
            $testdata->workspace->owner_count(),
            'wrong owner count'
        );

        self::assertEqualsCanonicalizing(
            $testdata->owners->pluck('id'),
            $testdata->workspace->owners()->pluck('id'),
            'wrong owners'
        );
    }

    /**
     * A basic test for the can_be_owner() method.
     * The detailed conditions are checked in the test for the owner_addition_conditions class.
     *
     * @return void
     */
    public function test_can_be_owner(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();

        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        static::assertFalse($workspace->can_be_owner(new user($user2)));

        member::added_to_workspace($workspace, $user2->id);

        static::assertTrue($workspace->can_be_owner(new user($user2)));
    }

    public function test_add_owners_with_all_users_failing(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        $errors = $workspace->add_owners(new collection([$user2, $user3]));

        static::assertEqualsCanonicalizing([
            $user2->id . ':User is not a member of the workspace',
            $user3->id . ':User is not a member of the workspace',
        ], $errors->all());

        static::assertTrue($workspace->is_owner(new user($user1)));
        static::assertFalse($workspace->is_owner(new user($user2)));
        static::assertFalse($workspace->is_owner(new user($user3)));
    }

    public function test_add_owners_with_one_user_failing(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        member::added_to_workspace($workspace, $user2->id);

        $errors = $workspace->add_owners(new collection([$user2, $user3]));

        static::assertEquals([
            $user3->id . ':User is not a member of the workspace',
        ], $errors->all());

        // Nobody gets added as owner if any failure occurs.
        static::assertTrue($workspace->is_owner(new user($user1)));
        static::assertFalse($workspace->is_owner(new user($user2)));
        static::assertFalse($workspace->is_owner(new user($user3)));
    }

    public function test_add_owners_successful(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        member::added_to_workspace($workspace, $user2->id);
        member::added_to_workspace($workspace, $user3->id);

        $errors = $workspace->add_owners(new collection(
            [
                new user($user2),
                new user($user3)
            ]));

        static::assertSame([], $errors->all());

        static::assertTrue($workspace->is_owner(new user($user1)));
        static::assertTrue($workspace->is_owner(new user($user2)));
        static::assertTrue($workspace->is_owner(new user($user3)));
    }

    public function test_missing_permission(): void {
        $generator = static::getDataGenerator();
        $workspace_generator = workspace_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        static::setUser($user1);
        $workspace = $workspace_generator->create_workspace();

        static::setUser($user2);
        $errors = $workspace->add_owners(new collection([$user2, $user3]));

        static::assertSame(['No permission to add owners to this workspace'], $errors->all());

        static::assertTrue($workspace->is_owner(new user($user1)));
        static::assertFalse($workspace->is_owner(new user($user2)));
        static::assertFalse($workspace->is_owner(new user($user3)));
    }

    public function test_remove_owners_as_non_authority_user(): void {
        $testdata = $this->create_multi_owner_workspace(4);

        // Log in as user two and check if user two is able to transfer the ownership.
        $user_two = static::getDataGenerator()->create_user();
        static::setUser($user_two);


        $errors = $testdata->workspace->remove_owners(
            $testdata->owners->pluck('id'),
            new user($user_two->id)
        );

        static::assertCount(1, $errors);
        static::assertEquals(
            'Actor does not have ability to remove workspace owner',
            $errors->first()
        );
    }

    public function test_remove_owners(): void {
        /** @var collection $testdata->owners */
        $testdata = $this->create_multi_owner_workspace(4);
        $owner1 = $testdata->owners->first();

        static::setAdminUser();

        // Remove owner1 user from the owner list to test it can remove others
        $owner_ids = $testdata->owners->filter(
            fn($owner) => $owner1->id !== $owner->id
        )->pluck('id');

        $testdata->workspace->remove_owners($owner_ids, user::logged_in());
        // Created 3 owners, admin remove all
        $testdata->workspace->reload();
        static::assertCount(1, $testdata->workspace->owners());
    }

    public function test_owner_can_remove_other_owners(): void {
        /** @var collection $testdata->owners */
        $testdata = $this->create_multi_owner_workspace(4);
        $owner1 = $testdata->owners->first();
        static::setUser($owner1);

        // Remove owner1 user from the owner list to test it can remove others
        $owner_ids = $testdata->owners->filter(
            fn($owner) => $owner1->id !== $owner->id
        )->pluck('id');

        $testdata->workspace->remove_owners($owner_ids, $owner1);
        // Created 4 owners, 3 removed, left 1
        $testdata->workspace->reload();
        static::assertCount(1, $testdata->workspace->owners());
    }

    public function test_owner_can_remove_others_including_self(): void {
        /** @var collection $testdata->owners */
        $testdata = $this->create_multi_owner_workspace(4);
        $owner1 = $testdata->owners->first();
        static::setUser($owner1);

        // Remove owner4 user from the owner list to test it can remove others
        $owner4 = $testdata->owners->last();
        $owner_ids = $testdata->owners->filter(
            fn($owner) => $owner4->id !== $owner->id
        )->pluck('id');

        $testdata->workspace->remove_owners($owner_ids, $owner1);
        // Created 3 owners, deleted 3, left noone.
        $testdata->workspace->reload();
        static::assertCount(1, $testdata->workspace->owners());
    }
}
