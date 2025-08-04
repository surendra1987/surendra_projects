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

use container_workspace\member\member;
use core\entity\user;
use container_workspace\member\member_removal_conditions;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @group container_workspace
 */
class container_workspace_member_removal_conditions_test extends container_workspace_multi_owner_testcase {

    public function test_can_owner_be_removed(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $owner1 = $owners->first();

        static::setAdminUser();
        $owner_member = member::from_user($owner1->id, $workspace->get_id());
        $owner_member->demote_from_owner();

        $result = member_removal_conditions::create($workspace, $owner1)->evaluate();
        static::assertFalse($result->is_fulfilled);
        static::assertEquals(member_removal_conditions::ERR_CAN_BE_REMOVED, $result->code);
    }

    public function test_conditions_pass(): void {
        $testdata = $this->create_multi_owner_workspace(4);
        $workspace = $testdata->workspace;
        /** @var collection $owners */
        $owners = $testdata->owners;
        $owner1 = $owners->first();
        $owner2 = $owners->last();

        self::setUser($owner1);

        $result = member_removal_conditions::create($workspace, $owner2)->evaluate();
        static::assertTrue($result->is_fulfilled);
    }
}
