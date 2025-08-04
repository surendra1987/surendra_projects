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

use core\entity\user;
use container_workspace\member\owner_removal_conditions;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @group container_workspace
 */
class container_workspace_owner_removal_conditions_test extends container_workspace_multi_owner_testcase {

    public function test_can_remove(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;

        static::setAdminUser();

        $result = owner_removal_conditions::create($workspace, $owners, user::logged_in())->evaluate();
        static::assertFalse($result->is_fulfilled);
        static::assertEquals(owner_removal_conditions::ERR_OWNERS_LIMITATION, $result->code);
    }

    public function test_can_manage(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $owner1 = $testdata->owners->first();

        $owners = $owners->filter(
            fn($owner) => $owner1->id !== $owner->id
        );

        static::setGuestUser();

        $result = owner_removal_conditions::create($workspace, $owners, user::logged_in())->evaluate();
        static::assertFalse($result->is_fulfilled);
        static::assertEquals(owner_removal_conditions::ERR_CAN_MANAGE, $result->code);
    }

    public function test_conditions_pass(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $owner1 = $owners->first();

        // Remove owner1 user from the owner list to test it can remove others
        $owners = $owners->filter(
            fn($owner) => $owner1->id !== $owner->id
        );

        self::setUser($owner1);

        $result = owner_removal_conditions::create($workspace, $owners, $owner1)->evaluate();
        static::assertTrue($result->is_fulfilled);
    }
}
