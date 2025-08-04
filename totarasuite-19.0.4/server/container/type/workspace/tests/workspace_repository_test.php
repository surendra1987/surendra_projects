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

use container_workspace\entity\workspace_repository;
use container_workspace\member\member;
use core\entity\role;
use core\entity\user;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @coversDefaultClass \container_workspace\entity\workspace_repository
 *
 * @group container_workspace
 */
class container_workspace_workspace_repository_test extends container_workspace_multi_owner_testcase {
    public function test_workspace_user_has_role(): void {
        $testdata = $this->create_multi_owner_workspace();

        $check_owner = fn(user $user): bool => workspace_repository::user_has_role(
            $testdata->id,
            new role(member::get_role_for_owners()),
            $user
        );

        $check_member = fn(user $user): bool => workspace_repository::user_has_role(
            $testdata->id,
            new role(member::get_role_for_members()),
            $user
        );

        foreach ($testdata->owners as $user) {
            self::assertTrue(
                $check_owner($user),
                'user with owner role has wrong has_role(owner) value'
            );

            self::assertFalse(
                $check_member($user),
                'user with owner role has wrong has_role(member) value'
            );
        }

        foreach ($testdata->normal_members as $user) {
            self::assertFalse(
                $check_owner($user),
                'user with normal member role has wrong has_role(owner) value'
            );

            self::assertTrue(
                $check_member($user),
                'user with normal member role has wrong has_role(member) value'
            );
        }

        foreach ($testdata->non_members as $user) {
            self::assertFalse(
                $check_owner($user),
                'user with normal member role has wrong has_role(owner) value'
            );

            self::assertFalse(
                $check_member($user),
                'user with non member role has wrong has_role(member) value'
            );
        }
    }

    public function test_workspace_roles(): void {
        $testdata = $this->create_multi_owner_workspace();

        $role = new role(member::get_role_for_owners());
        self::assertEqualsCanonicalizing(
            $testdata->owners->pluck('id'),
            workspace_repository::users_by_role($testdata->id, $role)
                ->get()
                ->pluck('id'),
            'wrong users for workspace owner role'
        );


        $role = new role(member::get_role_for_members());
        self::assertEqualsCanonicalizing(
            $testdata->normal_members->pluck('id'),
            workspace_repository::users_by_role($testdata->id, $role)
                ->get()
                ->pluck('id'),
            'wrong users for workspace member role'
        );
    }
}
