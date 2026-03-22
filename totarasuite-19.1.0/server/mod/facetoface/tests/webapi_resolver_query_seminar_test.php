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
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package mod_facetoface
 */

use core_phpunit\testcase;
use mod_facetoface\seminar_event;
use totara_webapi\client_aware_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_query_seminar_test extends testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'mod_facetoface_seminar';

    public function test_invalid_seminar_id(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('Seminar record could not be found or you do not have permissions.');

        $args = [
            'seminar' => [
                'id' => 99999999,
            ],
        ];
        $this->resolve_graphql_query(static::QUERY, $args);
    }

    public function test_not_authenticated(): void {
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('Seminar record could not be found or you do not have permissions.');

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        $args = [
            'seminar' => [
                'id' => $facetoface->id,
            ],
        ];
        $this->resolve_graphql_query(static::QUERY, $args);
    }

    public function test_missing_capability_correct_id(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('Seminar record could not be found or you do not have permissions.');

        $args = [
            'seminar' => [
                'id' => $facetoface->id,
            ],
        ];
        $this->resolve_graphql_query(static::QUERY, $args);
    }

    public function test_retrieves_existing_seminar(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:view', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        $args = [
            'seminar' => [
                'id' => $facetoface->id,
            ],
        ];

        $response = $this->resolve_graphql_query(static::QUERY, $args);
        $returned_seminar = $response['seminar'];
        $this->assertEquals($facetoface->id, $returned_seminar->get_id());
    }

    public function test_retrieves_existing_seminar_using_shortname(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:view', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'shortname' => "mark"]);

        $args = [
            'seminar' => [
                'shortname' => "mark",
            ],
        ];

        $response = $this->resolve_graphql_query(static::QUERY, $args);
        $returned_seminar = $response['seminar'];
        $this->assertEquals($facetoface->id, $returned_seminar->get_id());
    }

    public function test_retrieves_existing_seminar_with_custom_fields(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:view', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();

        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        // adding a room and custom fields
        $room = $facetoface_generator->add_site_wide_room([]);
        $room_custom_fields = $custom_field_generator->create_text('facetoface_room', ['room_text_one']);
        $custom_field_generator->set_text($room, $room_custom_fields['room_text_one'], 'heich dee tiket', 'facetofaceroom', 'facetoface_room');

        $sessiondate = new stdClass();
        $sessiondate->timestart = time() + DAYSECS;
        $sessiondate->timefinish = $sessiondate->timestart + (DAYSECS * 2);
        $sessiondate->sessiontimezone = 'Pacific/Auckland';
        $sessiondate->roomids = [$room->id];
        $event_id = $facetoface_generator->add_session(['facetoface' => $facetoface->id, 'sessiondates' => [$sessiondate]]);

        // adding event custom fields
        $event_custom_fields = $custom_field_generator->create_text('facetoface_session', ['event_text_one']);
        $custom_field_generator->set_text((new seminar_event($event_id))->to_record(), $event_custom_fields['event_text_one'], 'Oh god it\'s green, that\'s crazy, why is it green?', 'facetofacesession', 'facetoface_session');

        $args = [
            'seminar' => [
                'id' => $facetoface->id,
            ],
        ];
        /** @var \mod_facetoface\seminar_event $found */
        $response = $this->resolve_graphql_query(static::QUERY, $args);
        $returned_seminar = $response['seminar'];
        $this->assertEquals($facetoface->id, $returned_seminar->get_id());

        // resolve the custom fields
        $custom_fields_result_session = $this->execution_context->get_variable('custom_fields_facetofacesession');
        $custom_fields_result_room = $this->execution_context->get_variable('custom_fields_facetofaceroom');

        // check session value
        $custom_field_array = reset($custom_fields_result_session);
        $custom_fields_result_session = reset($custom_field_array);
        $this->assertEquals("Oh god it's green, that's crazy, why is it green?", $custom_fields_result_session['raw_value']);

        // check room value
        $custom_fields_result_room = reset($custom_fields_result_room);

        // Filter out the default custom fields
        $custom_fields_result_room = array_filter($custom_fields_result_room, function ($field) use ($room_custom_fields) {
            return in_array($field['definition']['id'], $room_custom_fields);
        });
        $custom_fields_result_room = reset($custom_fields_result_room);
        $this->assertEquals("heich dee tiket", $custom_fields_result_room['raw_value']);
    }
}
