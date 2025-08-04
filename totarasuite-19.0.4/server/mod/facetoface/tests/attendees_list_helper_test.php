<?php
/*
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package mod_facetoface
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use mod_facetoface\attendees_list_helper;
use mod_facetoface\seminar_event;
use mod_facetoface\testing\generator as seminar_generator;

class mod_facetoface_attendees_list_helper_test extends testcase {

    public function test_add_list(): void {
        global $SESSION;

        $user_live = self::getDataGenerator()->create_user(['idnumber' => 'user_live']);
        self::getDataGenerator()->create_user(['idnumber' => 'user_suspended', 'suspended' => 1]);
        self::getDataGenerator()->create_user(['idnumber' => 'user_deleted', 'deleted' => 1]);

        $event = new seminar_event();

        $data = (object) [
            's' => $event->get_id(),
            'listid' => 'test_list_id',
            'idfield' => 'idnumber',
            'csvinput' => 'user_live,user_suspended,user_deleted',
            'ignoreconflicts' => 0,
        ];

        attendees_list_helper::add_list($data);

        self::assertCount(1, $SESSION->mod_facetoface_attendeeslist['test_list_id']['userdata']);
        self::assertEquals($user_live->id, array_key_first($SESSION->mod_facetoface_attendeeslist['test_list_id']['userdata']));
    }
}