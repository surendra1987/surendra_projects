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
use container_workspace\member\member;
use container_workspace\totara_notification\placeholder\role;

/**
 * @group container_workspace
 */
class container_workspace_totara_notification_placeholder_role_test extends testcase {

    public function test_get_placeholder_name(): void {
        $role = member::get_role_for_owners();
        // should be equal to 'Workspace Owner'
        static::assertEquals(
            role_get_name($role),
            role::from_id($role->id)->do_get('full_name')
        );

        $role = member::get_role_for_members();
        // should be equal to 'Learner'
        static::assertEquals(
            role_get_name($role),
            role::from_id($role->id)->do_get('full_name')
        );
    }
}
