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

use core\webapi\execution_context;
use mod_facetoface\seminar_event;
use mod_facetoface\signup;
use totara_webapi\client_aware_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_mutation_event_cancel_user_booking_test extends \core_phpunit\testcase {
    use webapi_phpunit_helper;

    const MUTATION = 'mod_facetoface_event_cancel_user_booking';

    private function create_attendee(seminar_event $seminarevent, int $userid): stdClass {
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $this->getDataGenerator()->enrol_user($userid, $seminarevent->get_seminar()->get_course());
        return $facetoface_generator->create_signup((object)['id' => $userid], $seminarevent);
    }

    private function create_event(): seminar_event {
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $course = $this->getDataGenerator()->create_course();
        return $facetoface_generator->create_session_for_course($course);
    }

    public function test_cancellation_ok() {
        global $DB;
        $api_user = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $api_user->id, context_system::instance());
        assign_capability('mod/facetoface:signup', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($api_user);

        $core_gen = $this->getDataGenerator();

        // create user + course
        $user = $core_gen->create_user();
        $course = $core_gen->create_course(
            (object)[
                'shortname' => rand(0,99999),
            ]
        );

        // create booking for user
        $core_gen->enrol_user($user->id, $course->id);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $seminar_event = $facetoface_generator->create_session_for_course($course);

        $facetoface_generator->create_signup($user, $seminar_event);

        // cancel booking
        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $user->id]
        ];

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );

        $booking = $result['booking'];

        // check if cancellation return item is ok
        $this->assertTrue($result['success']);
        $this->assertEquals($user->id, $booking->get_userid());
        $this->assertEquals($seminar_event->to_record()->id, $booking->get_sessionid());
    }

    public function test_cancellation_ok_custom_fields() {
        $this->setAdminUser();

        // create user
        $user = $this->getDataGenerator()->create_user();
        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();
        // create booking for user
        $this->create_attendee($seminar_event, $user->id);

        // create custom fields
        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_field_generator->create_text('facetoface_cancellation', ['text_one']);

        $custom_fields = [
            [
                'shortname' => 'cancellationnote',
                'data' => 'Tralalero Tralala made me do it, it scared me into doing it'
            ],
            [
                'shortname' => 'text_one',
                'data' => 'If you\'re API and you know it clap your hands!'
            ]
        ];

        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $user->id],
            'cancellation_custom_fields' => $custom_fields
        ];

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );

        $booking = $result['booking'];

        $custom_fields_result = $this->resolve_graphql_type(
            'mod_facetoface_event_user_booking',
            'cancellation_custom_fields',
            $booking,
            [],
            null,
            $this->execution_context
        );

        $this->assertCount(2, $custom_fields_result);
        $custom_fields_result_1 = reset($custom_fields_result);
        $this->assertEquals('Tralalero Tralala made me do it, it scared me into doing it', $custom_fields_result_1['raw_value']);
        $custom_fields_result_2 = end($custom_fields_result);
        $this->assertEquals('If you\'re API and you know it clap your hands!', $custom_fields_result_2['raw_value']);
    }

    public function test_cancellation_failed_user_not_booked() {
        $this->setAdminUser();

        // create user
        $user = $this->getDataGenerator()->create_user();

        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();

        // cancel nonexistant booking
        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $user->id]
        ];

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage("Signup record could not be found.");

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );
    }

    public function test_cancellation_already_cancelled() {
        $this->setAdminUser();
        $core_gen = $this->getDataGenerator();

        // create user + course
        $user = $core_gen->create_user();
        $course = $core_gen->create_course(
            (object)[
                'shortname' => rand(0,99999),
            ]
        );

        // create booking for user
        $core_gen->enrol_user($user->id, $course->id);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $seminar_event = $facetoface_generator->create_session_for_course($course);

        $facetoface_generator->create_signup($user, $seminar_event);

        // cancel booking
        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $user->id]
        ];

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );

        // cancel it again
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );

        $booking = $result['booking'];

        // check if we get the cancellation error message
        $this->assertNotNull($result['cancellation_errors']);
        $error = reset($result['cancellation_errors']);
        $this->assertEquals("There is no direct way to change signup status to required state", $error['message']);
        $this->assertEquals(signup\state\user_cancelled::class, $error['state']);
        $this->assertFalse($result['success']);
        $this->assertEquals($user->id, $booking->get_userid());
        $this->assertEquals($seminar_event->to_record()->id, $booking->get_sessionid());
    }

    public function test_cancellation_bad_user() {
        $this->setAdminUser();

        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage("User record could not be found");

        // cancel booking
        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => null]
        ];

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );
    }

    public function test_cancellation_when_not_logged_in_user() {
        $user = $this->getDataGenerator()->create_user();

        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();

        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions");

        // cancel booking
        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $user->id]
        ];

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );
    }

    public function test_capabilities_insufficient_capabilities() {
        global $DB;
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $api_user_role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        unassign_capability('mod/facetoface:signup', $api_user_role->id);
        role_assign($api_user_role->id, $user->id, context_system::instance());

        $this->setUser($user);

        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();

        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions");

        // Cancel booking
        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $user->id]
        ];

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
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

        // create course + seminar + event
        $seminar_event = $this->create_event();
        $seminar_event->save();

        $attendee = $core_generator->create_user();
        $this->create_attendee($seminar_event, $attendee->id);

        // Cancel booking
        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $attendee->id]
        ];

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
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

        $attendee = $core_generator->create_user();
        $this->create_attendee($seminar_event, $attendee->id);

        assign_capability(
            'mod/facetoface:signup',
            CAP_ALLOW,
            $api_user_role->id,
            $seminar_event->get_seminar()->get_context()
        );

        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $attendee->id],
        ];

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );;
    }

    public function test_cancellation_bad_event() {
        $this->setAdminUser();
        $user = $this->getDataGenerator()->create_user();

        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions.");

        // cancel booking
        $input = [
            'event' => ['id' => null],
            'user' => ['id' => $user->id]
        ];

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
            ]
        );
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

        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $user->id],
        ];

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
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
        $custom_field_generator->set_text($signup, $custom_fields['text_one'], 'a long tailed macaque monkey named aaron', 'facetofacesignup', 'facetoface_signup');

        $input = [
            'event' => ['id' => $seminar_event->get_id()],
            'user' => ['id' => $user->id],
        ];

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
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

        $input = [
            'event' => ['id' => $event_id],
            'user' => ['id' => $user->id],
        ];

        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => $input
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
}
