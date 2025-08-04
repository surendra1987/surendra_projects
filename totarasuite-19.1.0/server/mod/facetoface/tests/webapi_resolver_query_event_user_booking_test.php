<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package mod_facetoface
 */

use core\webapi\execution_context;
use core_phpunit\testcase;
use mod_facetoface\exception\signup_exception;
use mod_facetoface\seminar_event;
use mod_facetoface\signup;
use mod_facetoface\signup\state\booked;
use mod_facetoface\signup_status;
use totara_webapi\client_aware_exception;
use totara_webapi\graphql;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \mod_perform\webapi\resolver\query\activities
 *
 * @group perform
 */
class mod_facetoface_webapi_resolver_query_event_user_booking_test extends testcase {

    use webapi_phpunit_helper;

    const QUERY = 'mod_facetoface_event_user_booking';
    /**
     * @param seminar_event $seminarevent
     * @param int $userid
     * @return signup
     * @throws signup_exception
     * @throws coding_exception
     */
    private function create_attendee(seminar_event $seminarevent, int $userid): signup {
        $signup = signup::create($userid, $seminarevent, 0);
        $signup->save();
        $booking = new booked($signup);
        $signup_status = signup_status::create($signup, $booking);
        $signup_status->save();
        return $signup;
    }

    /**
     * @return seminar_event
     * @throws coding_exception
     */
    private function create_event(): seminar_event {
        /** @var \mod_facetoface\testing\generator $generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $event = new seminar_event();
        $event->save();
        $course = $this->getDataGenerator()->create_course();

        return $facetoface_generator->create_session_for_course($course);
    }

    public function test_capabilities_insufficient_capabilities() {
        global $DB;

        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $api_user_role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        unassign_capability('mod/facetoface:signup', $api_user_role->id);
        role_assign($api_user_role->id, $user->id, context_system::instance());

        $this->setUser($user->id);

        $seminar_event = $this->create_event();

        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions");

        $this->resolve_graphql_query(
            static::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );
    }

    public function test_capabilities_api_user_has_sufficient_capabilties_by_default() {
        global $DB;

        // We're testing here that we don't have exceptions thrown
        $this->expectNotToPerformAssertions();

        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $api_user_role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        role_assign($api_user_role->id, $user->id, context_system::instance());

        $this->setUser($user->id);

        $seminar_event = $this->create_event();

        $this->resolve_graphql_query(
            static::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );
    }

    public function test_capabilities_within_module_context() {
        global $DB;

        // We're testing here that we don't have exceptions thrown
        $this->expectNotToPerformAssertions();

        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $api_user_role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        role_assign($api_user_role->id, $user->id, context_system::instance());
        unassign_capability('mod/facetoface:signup', $api_user_role->id);
        $this->setUser($user->id);

        $seminar_event = $this->create_event();
        $seminar_event->save();

        assign_capability(
            'mod/facetoface:signup',
            CAP_ALLOW,
            $api_user_role->id,
            $seminar_event->get_seminar()->get_context()
        );

        $this->resolve_graphql_query(
            static::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );
    }

    public function test_get_event_user_booking_status() {
        $this->setAdminUser();

        // create user
        $user = $this->getDataGenerator()->create_user();
        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();

        // test user booking
        $result = $this->resolve_graphql_query(
            'mod_facetoface_event_user_booking',
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );

        $this->assertFalse($result['found']);

        // create booking for user
        $this->create_attendee($seminar_event, $user->id);

        // test user booking
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );

        $this->assertTrue($result['found']);
        $this->assertNotEmpty($result['booking']);

        $signup = $result['booking'];
        $this->assertInstanceOf(signup::class, $signup);

        $this->assertEquals($seminar_event->get_seminar()->get_context(), $this->execution_context->get_relevant_context());

        $event_result = $this->resolve_graphql_type('mod_facetoface_event_user_booking', 'event', $signup);
        $user_result = $this->resolve_graphql_type('mod_facetoface_event_user_booking', 'user', $signup);

        $this->assertEquals($event_result->get_id(), $seminar_event->get_id());
    }

    public function test_get_event_user_booking_status_user_field() {
        global $DB;
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $seminar_event = $this->create_event();
        $seminar_event->save();
        $this->create_attendee($seminar_event, $user->id);

        // Admin user isn't allowed to see the user field in the booking
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );
        $this->assertNull($this->resolve_graphql_type('mod_facetoface_event_user_booking', 'user', $result['booking']));

        // Now enrol the user in the course so that the admin user can see the user field.
        $student_role = $DB->get_record('role', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($user->id, $seminar_event->get_seminar()->get_course(), $student_role->id);

        // Admin user is now allowed to see the user field.
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );
        $user_result = $this->resolve_graphql_type('mod_facetoface_event_user_booking', 'user', $result['booking']);
        $this->assertEquals($user->id, $user_result->id);
    }

    public function test_get_event_user_booking_custom_fields() {
        $this->setAdminUser();

        // Create user
        $user = $this->getDataGenerator()->create_user();
        // Create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();
        // Create booking for user
        $signup = $this->create_attendee($seminar_event, $user->id);

        // create custom fields
        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('facetoface_signup', ['text_one']);
        $custom_field_generator->set_text($signup->to_record(), $custom_fields['text_one'], 'a long tailed macaque monkey named aaron', 'facetofacesignup', 'facetoface_signup');

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );

        $signup = $result['booking'];
        $this->assertInstanceOf(signup::class, $signup);

        $custom_fields_result = $this->resolve_graphql_type(
            'mod_facetoface_event_user_booking',
            'custom_fields',
            $signup,
            [],
            null,
            $this->execution_context,
        );

        // Filter out the default custom field
        $custom_fields_result = array_filter($custom_fields_result, function ($field) use ($custom_fields) {
            return in_array($field['definition']['id'], $custom_fields);
        });

        $this->assertCount(1, $custom_fields_result);

        $user_context = context_user::instance($user->id);
        $ec = execution_context::create('dev');
        $ec->set_variable('context', $user_context);

        $raw_value = $this->resolve_graphql_type(
            'totara_customfield_field',
            'raw_value',
            reset($custom_fields_result),
            [],
            $user_context,
            $ec
        );

        $this->assertEquals('a long tailed macaque monkey named aaron', $raw_value);
    }

    public function test_get_event_user_booking_cancellation_custom_fields() {
        $this->setAdminUser();

        // create user
        $user = $this->getDataGenerator()->create_user();
        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();
        // create booking for user
        $signup = $this->create_attendee($seminar_event, $user->id);

        // create custom fields
        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('facetoface_cancellation', ['text_one']);
        $custom_field_generator->set_text($signup->to_record(), $custom_fields['text_one'], 'a long tailed macaque monkey named aaron', 'facetofacecancellation', 'facetoface_cancellation');

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );

        $signup = $result['booking'];
        $this->assertInstanceOf(signup::class, $signup);

        $custom_fields_result = $this->resolve_graphql_type(
            'mod_facetoface_event_user_booking',
            'cancellation_custom_fields',
            $signup,
            [],
            null,
            $this->execution_context,
        );

        // Filter out the default custom field
        $custom_fields_result = array_filter($custom_fields_result, function ($field) use ($custom_fields) {
            return in_array($field['definition']['id'], $custom_fields);
        });

        $this->assertCount(1, $custom_fields_result);

        $user_context = context_user::instance($user->id);
        $ec = execution_context::create(graphql::TYPE_EXTERNAL);
        $ec->set_variable('context', $user_context);

        $resolved_custom_field = $this->resolve_graphql_type(
            'totara_customfield_field',
            'raw_value',
            reset($custom_fields_result),
            [],
            $user_context,
            $ec
        );

        $this->assertEquals('a long tailed macaque monkey named aaron', $resolved_custom_field);
    }

    public function test_get_event_user_booking_event_custom_fields() {
        $this->setAdminUser();

        // create user
        $user = $this->getDataGenerator()->create_user();
        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();
        // create booking for user
        $this->create_attendee($seminar_event, $user->id);

        // create custom fields
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('facetoface_session', ['text_one']);
        $custom_field_generator->set_text($seminar_event->to_record(), $custom_fields['text_one'], 'text', 'facetofacesession', 'facetoface_session');

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => $user->id]
            ]
        );

        $signup = $result['booking'];
        $this->assertInstanceOf(signup::class, $signup);

        $event = $this->resolve_graphql_type(
            'mod_facetoface_event_user_booking',
            'event',
            $signup,
            [],
            null,
            $this->execution_context,
        );

        $custom_fields_result = $this->resolve_graphql_type(
            'mod_facetoface_event',
          'custom_fields',
          $event,
          [],
          null,
          $this->execution_context,
        );

        $this->assertCount(1, $custom_fields_result);

        $resolved_custom_field = $this->resolve_graphql_type(
            'totara_customfield_field',
            'raw_value',
            reset($custom_fields_result),
            [],
            null,
            $this->execution_context
        );

        $this->assertEquals('text', $resolved_custom_field);
    }

    public function test_get_event_user_booking_room_custom_fields() {
        $this->setAdminUser();

        // create user
        $user = $this->getDataGenerator()->create_user();

        /** @var \mod_facetoface\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator("mod_facetoface");
        $room_record = $generator->add_site_wide_room([]);

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $sessiondate = new stdClass();
        $sessiondate->timestart = time() + DAYSECS;
        $sessiondate->timefinish = $sessiondate->timestart + (DAYSECS * 2);
        $sessiondate->sessiontimezone = 'Pacific/Auckland';
        $sessiondate->roomids = [$room_record->id];
        $event_id = $facetoface_generator->add_session(['facetoface' => $facetoface->id, 'sessiondates' => [$sessiondate]]);

        $this->create_attendee(new seminar_event($event_id), $user->id);

        // create custom fields
        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('facetoface_room', ['text_one']);
        $custom_field_generator->set_text($room_record, $custom_fields['text_one'], 'Ben\'s vim attack', 'facetofaceroom', 'facetoface_room');

        $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => $event_id],
                'user' => ['id' => $user->id]
            ]
        );

        $custom_fields_result = $this->execution_context->get_variable('custom_fields_facetofaceroom');
        $custom_fields_result = reset($custom_fields_result);

        // Filter out the default custom field
        $custom_fields_result = array_filter($custom_fields_result, function ($field) use ($custom_fields) {
            return in_array($field['definition']['id'], $custom_fields);
        });
        $custom_fields_result = reset($custom_fields_result);

        $this->assertEquals('Ben\'s vim attack', $custom_fields_result['raw_value']);
    }

    public function test_get_event_user_booking_invalid_user() {
        $this->setAdminUser();

        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage("User record could not be found or you do not have permissions.");

        // test user booking
        $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => $seminar_event->get_id()],
                'user' => ['id' => null]
            ]
        );
    }

    public function test_get_event_user_booking_invalid_event() {
        $this->setAdminUser();

        // create user
        $user = $this->getDataGenerator()->create_user();

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage("Event record could not be found or you do not have permissions.");

        // test user booking
        $this->resolve_graphql_query(
            self::QUERY,
            [
                'event' => ['id' => null],
                'user' => ['id' => $user->id]
            ]
        );
    }
}
