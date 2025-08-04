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

use container_workspace\event\user_role_changed;
use container_workspace\member\member;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @group container_workspace
 */
class container_workspace_event_user_role_changed_test extends container_workspace_multi_owner_testcase {

    public function test_role_changed(): void {
        $testdata = $this->create_multi_owner_workspace();

        $owner_role = member::get_role_for_owners();

        $other = [
            'role_id' => $owner_role->id,
            'role_name' => role_get_name($owner_role)
        ];

        $event = user_role_changed::create_from_data(
            $testdata->workspace,
            $testdata->owners->last(),
            $testdata->owners->first(),
            $other
        );
        $event->trigger();

        static::assertEquals($testdata->id, $event->objectid);
        static::assertEquals('u', $event->crud);
        static::assertEquals($testdata->workspace->owners()->first()->id, $event->userid);
        static::assertEquals($event::LEVEL_OTHER, $event->edulevel);
        static::assertEquals('role_assignments', $event->objecttable);
        static::assertSame(
            "The '" . role_get_name($owner_role) . "' role is assigned for "
            . "the user with id '".$testdata->workspace->owners()->last()->id."' of workspace id '$testdata->id' "
            . "by the user with id '".$testdata->workspace->owners()->first()->id."'",
            $event->get_description()
        );
        static::assertEventContextNotUsed($event);
    }
}
