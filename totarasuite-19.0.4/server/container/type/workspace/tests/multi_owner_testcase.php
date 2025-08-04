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

use core\collection;
use core_phpunit\testcase;
use container_workspace\testing\generator;
use core\entity\user;

abstract class container_workspace_multi_owner_testcase extends testcase {
    /**
     * Generates test data.
     *
     * @param int $owner_count number of owners to create for the workspace.
     * @param int $member_count number of 'normal' members to create for the
     *        workspace.
     * @param int $non_member_count other users to be generated.
     *
     * @return stdClass object with these fields:
     *         - int $id: generated \container_workspace\workspace id. NB: this
     *           is the associated _course id_ NOT the workspace _entity id_.
     *         - collection<user> $owners: users who are workspace owners.
     *         - collection<user> $normal_members: 'normal' members ie WITHOUT
     *           owners.
     *         - collection<user> $non_members: users who are not members of the
     *           workspace.
     */
    final protected function create_multi_owner_workspace(
        int $owner_count=3,
        int $member_count=10,
        int $non_member_count=5
    ): stdClass {
        $this->setAdminUser();

        [$owners, $members, $non_workspace_users] = array_map(
            $this->create_users(...),
            [$owner_count, $member_count, $non_member_count]
        );

        $generator = generator::instance();
        $workspace = $generator->add_workspace_members(
            $generator->create_workspace_with_multiple_owners($owners),
            $members
        );

        return (object) [
            'id' => $workspace->id,
            'owners' => $owners,
            'normal_members' => $members,
            'non_members' => $non_workspace_users,
            'workspace' => $workspace
        ];
    }

    /**
     * Generates 'normal' users.
     *
     * @param int $count no of users to generate.
     *
     * @return collection<user> a list of users.
     */
    final protected function create_users(int $count): collection {
        $generator = $this->getDataGenerator();

        return collection::new(range(0, $count - 1))
            ->map_to(fn (int $i): user => new user($generator->create_user()));
    }
}
