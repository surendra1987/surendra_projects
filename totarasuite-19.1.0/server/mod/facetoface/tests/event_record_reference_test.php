<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

use core\entity\user;
use core\exception\unresolved_record_reference;
use core_phpunit\testcase;
use mod_facetoface\seminar_event;
use mod_facetoface\webapi\reference\event_record_reference;

class mod_facetoface_event_record_reference_test extends testcase {

    public function test_load_for_viewer_with_insufficient_capabilities(): void {
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');
        $course = $core_generator->create_course();
        $event = $facetoface_generator->create_session_for_course($course);

        $this->expectException(unresolved_record_reference::class);
        $this->expectExceptionMessage('Could not find event record reference or you do not have permissions.');

        event_record_reference::load_for_viewer(
            [
                'id' => $event->get_id(),
            ],
            new user($user),
        );
   }

    public function test_load_for_viewer_with_sufficient_capabilities(): void {
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $roles = get_archetype_roles('user');
        $role = reset($roles);
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $role->id, context_system::instance());
        $core_generator->role_assign($role->id, $user->id);

        $event = new seminar_event();
        $event->save();

        $record = event_record_reference::load_for_viewer(
            [
                'id' => $event->get_id(),
            ],
            new user($user),
        );

        $this->assertEquals($event->get_id(), $record->id);
    }
}
