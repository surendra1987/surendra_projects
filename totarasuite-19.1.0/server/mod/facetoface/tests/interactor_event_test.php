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
use mod_facetoface\interactor\event as event_interactor;

class mod_facetoface_interactor_event_test extends \core_phpunit\testcase {

    public function test_can_not_view_event_with_no_capability(): void {
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');
        $course = $core_generator->create_course();
        $event = $facetoface_generator->create_session_for_course($course);

        $interactor = new event_interactor($event, new user($user));
        $this->assertFalse($interactor->can_view());
    }

    public function test_can_view_unassociated_event_with_system_capability(): void {
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $roles = get_archetype_roles('user');
        $role = reset($roles);
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $role->id, context_system::instance());
        $core_generator->role_assign($role->id, $user->id);

        $event = new \mod_facetoface\seminar_event();
        $event->save();

        $interactor = new event_interactor($event, new user($user));
        $this->assertTrue($interactor->can_view());
    }

    public function test_can_view_event_with_system_capability(): void {
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $roles = get_archetype_roles('user');
        $role = reset($roles);
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $role->id, context_system::instance());
        $core_generator->role_assign($role->id, $user->id);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');
        $course = $core_generator->create_course();
        $event = $facetoface_generator->create_session_for_course($course);

        $interactor = new event_interactor($event, new user($user));
        $this->assertTrue($interactor->can_view());
    }

    public function test_can_view_event_with_module_capability(): void {
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $roles = get_archetype_roles('user');
        $role = reset($roles);
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');
        $course = $core_generator->create_course();
        $event = $facetoface_generator->create_session_for_course($course);
        assign_capability('mod/facetoface:view', CAP_ALLOW, $role->id, $event->get_seminar()->get_context());
        $core_generator->role_assign($role->id, $user->id);

        $interactor = new event_interactor($event, new user($user));
        $this->assertTrue($interactor->can_view());
    }

}
