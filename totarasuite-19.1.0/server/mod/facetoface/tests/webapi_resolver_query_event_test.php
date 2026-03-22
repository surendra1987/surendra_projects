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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package mod_facetoface
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use mod_facetoface\seminar_event;
use totara_webapi\client_aware_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_query_event_test extends testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'mod_facetoface_event';

    private const TYPE = 'mod_facetoface_event';

    public function test_invalid_event_id(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        // create an event - then delete it to ensure we have a reference to an event which doesn't exist
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $sessiondate = new stdClass();
        $sessiondate->timestart = time() + DAYSECS;
        $sessiondate->timefinish = $sessiondate->timestart + (DAYSECS * 2);
        $sessiondate->sessiontimezone = 'Pacific/Auckland';
        $event_id = $facetoface_generator->add_session(['facetoface' => $facetoface->id, 'sessiondates' => [$sessiondate]]);
        $event = new seminar_event($event_id);
        $event->delete();

        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions");

        $args = [
            'event' => [
                'id' => $event_id,
            ],
        ];
        $this->resolve_graphql_query(static::QUERY, $args);
    }

    public function test_not_authenticated(): void {
        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions");

        $args = [
            'event' => [
                'id' => 1,
            ],
        ];
        $this->resolve_graphql_query(static::QUERY, $args);
    }

    public function test_missing_capability(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions");

        $args = [
            'event' => [
                'id' => 1,
            ],
        ];
        $this->resolve_graphql_query(static::QUERY, $args);
    }

    public function test_retrieves_existing_event(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');

        $room = $facetoface_generator->add_site_wide_room([]);
        $room_custom_fields = $custom_field_generator->create_text('facetoface_room', ['room_text_one']);
        $custom_field_generator->set_text($room, $room_custom_fields['room_text_one'], 'Room text', 'facetofaceroom', 'facetoface_room');

        $sessiondate = new stdClass();
        $sessiondate->timestart = time() + DAYSECS;
        $sessiondate->timefinish = $sessiondate->timestart + (DAYSECS * 2);
        $sessiondate->sessiontimezone = 'Pacific/Auckland';
        $sessiondate->roomids = [$room->id];
        $event_id = $facetoface_generator->add_session(['facetoface' => $facetoface->id, 'sessiondates' => [$sessiondate]]);

        $event_custom_fields = $custom_field_generator->create_text('facetoface_session', ['event_text_one', 'event_text_two']);
        $seminar_event = $DB->get_record('facetoface_sessions', ['facetoface' => $facetoface->id]);
        $custom_field_generator->set_text($seminar_event, $event_custom_fields['event_text_one'], 'Event text', 'facetofacesession', 'facetoface_session');

        $args = [
            'event' => [
                'id' => $event_id,
            ],
        ];
        /** @var \mod_facetoface\seminar_event $found */
        $response = $this->resolve_graphql_query(static::QUERY, $args);
        $returned_event = $response['event'];

        $resolved_event_custom_fields = $this->resolve_graphql_type(static::TYPE, 'custom_fields', $returned_event, [], null, $this->execution_context);
        $this->assertCount(2, $resolved_event_custom_fields);
        $this->assertEquals(
            'Event text',
            $this->resolve_graphql_type('totara_customfield_field', 'value', reset($resolved_event_custom_fields), [], null, $this->execution_context)
        );

        $this->assertEquals($event_id, $this->resolve_graphql_type(static::TYPE, 'id', $returned_event, [], null, $this->execution_context));
        $this->assertCount(1, $this->resolve_graphql_type(static::TYPE, 'sessions', $returned_event, [], null, $this->execution_context));

        $resolved_room_custom_fields = $this->execution_context->get_variable('custom_fields_facetofaceroom');
        $this->assertNotEmpty($resolved_room_custom_fields);
        // 2 custom fields that exist by default, and 1 field that we created.
        $this->assertCount(3, $resolved_room_custom_fields[$room->id]);
    }
}
