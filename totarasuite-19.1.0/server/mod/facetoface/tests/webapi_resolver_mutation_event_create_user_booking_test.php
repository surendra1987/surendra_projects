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
 * @author Gregory Yee <gregory.yee@totara.com>
 * @package mod_facetoface
 */

use core_phpunit\testcase;
use mod_facetoface\seminar;
use mod_facetoface\seminar_event;
use mod_facetoface\signup;
use mod_facetoface\signup\state\booked;
use totara_webapi\client_aware_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_mutation_event_create_user_booking_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'mod_facetoface_event_create_user_booking';

    private const EVENT_BOOKING_TYPE = 'mod_facetoface_event_user_booking';

    public function test_create_user_booking_success(): void {
        global $DB;
        $data_generator = $this->getDataGenerator();
        $user = $data_generator->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        $this->setUser($user);

        $course = $data_generator->create_course();
        $user_to_signup = $data_generator->create_and_enrol($course);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(['course' => $course->id]);
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => $user_to_signup->id,
                ]
            ]
        ];
        $response = $this->resolve_graphql_mutation(static::MUTATION, $args);
        $this->assertTrue($response['created']);
        $this->assertNull($response['booking_errors']);
        $this->assertEquals($event_id, $response['booking']->get_sessionid());
        $this->assertEquals($user_to_signup->id, $response['booking']->get_userid());

        $created_signup = signup::create($user_to_signup->id, new seminar_event($event_id));
        $this->assertEquals(booked::class, $created_signup->get_state()::class);
    }

    public function test_create_user_booking_failure(): void {
        global $DB;
        $data_generator = $this->getDataGenerator();
        $user = $data_generator->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        $this->setUser($user);

        $course = $data_generator->create_course();
        $user_to_signup = $data_generator->create_and_enrol($course);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(array('course' => $course->id));
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        // Cancel event, thereby causing signup to fail
        $event = new seminar_event($event_id);
        // Adjusting startime of the first session to ensure the cancellation is successful
        $event->get_sessions()->get_first()->set_timestart(time()+100);
        $event->cancel();

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => $user_to_signup->id,
                ]
            ]
        ];
        $response = $this->resolve_graphql_mutation(static::MUTATION, $args);
        $this->assertFalse($response['created']);
        $this->assertNotEmpty($response['booking_errors']);
        $this->assertNull($response['booking']);

        $this->assertContainsEquals(
            [
                'state' => booked::class,
                'code' => 'event_is_not_cancelled',
                'message' => 'This event has been cancelled. It is no longer possible to sign up for it.',
            ],
            $response['booking_errors'],
        );
    }

    public function test_invalid_event_id(): void {
        global $DB;
        $data_generator = $this->getDataGenerator();
        $user = $data_generator->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        $this->setUser($user);

        $course = $data_generator->create_course();
        $user_to_signup = $data_generator->create_and_enrol($course);

        // create an event - then delete it to ensure we have a reference to an event which doesn't exist
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(array('course' => $course->id));
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);
        $event = new seminar_event($event_id);
        $event->delete();

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => $user_to_signup->id,
                ],
            ],
        ];

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('Event record could not be found or you do not have permissions.');

        $this->resolve_graphql_mutation(static::MUTATION, $args);
    }

    public function test_invalid_user_id(): void {
        global $DB;
        $data_generator = $this->getDataGenerator();
        $user = $data_generator->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        $this->setUser($user);

        $course = $data_generator->create_course();

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(array('course' => $course->id));
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => '0000',
                ]
            ]
        ];
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('User record could not be found or you do not have permissions.');

        $this->resolve_graphql_mutation(static::MUTATION, $args);
    }

    public function test_not_authenticated(): void {

        $data_generator = $this->getDataGenerator();

        $course = $data_generator->create_course();

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(array('course' => $course->id));
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions");

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => 1,
                ],
            ],
        ];
        $this->resolve_graphql_mutation(static::MUTATION, $args);
    }

    public function test_capabilities_api_user_has_sufficient_capabilties_by_default() {
        global $DB;

        $this->expectNotToPerformAssertions();

        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $api_user_role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        role_assign($api_user_role->id, $user->id, context_system::instance());

        $this->setUser($user->id);

        $course = $core_generator->create_course();

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(array('course' => $course->id));
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => 1,
                ],
            ],
        ];
        $this->resolve_graphql_mutation(static::MUTATION, $args);
    }

    public function test_capabilities_within_module_context() {
        global $DB;

        $this->expectNotToPerformAssertions();

        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $api_user_role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        role_assign($api_user_role->id, $user->id, context_system::instance());
        unassign_capability('mod/facetoface:signup', $api_user_role->id);
        $this->setUser($user->id);

        $course = $core_generator->create_course();

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(['course' => $course->id]);
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);
        $seminar = new seminar($seminar_record->id);

        assign_capability(
            'mod/facetoface:signup',
            CAP_ALLOW,
            $api_user_role->id,
            $seminar->get_context()
        );

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => 1,
                ],
            ],
        ];
        $this->resolve_graphql_mutation(static::MUTATION, $args);
    }

    public function test_insufficient_capabilities(): void {
        global $DB;

        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $api_user_role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        unassign_capability('mod/facetoface:signup', $api_user_role->id);
        role_assign($api_user_role->id, $user->id, context_system::instance());

        $this->setUser($user->id);

        $course = $core_generator->create_course();

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(['course' => $course->id]);
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        $this->expectexception(client_aware_exception::class);
        $this->expectexceptionmessage("Event record could not be found or you do not have permissions");

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => 1,
                ],
            ],
        ];
        $this->resolve_graphql_mutation(static::MUTATION, $args);
    }

    public function test_includes_booking_custom_fields(): void {
        global $DB;
        $data_generator = $this->getDataGenerator();
        $user = $data_generator->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $data_generator->create_course();
        $user_to_signup = $data_generator->create_and_enrol($course);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(['course' => $course->id]);
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $data_generator->get_plugin_generator('totara_customfield');
        $booking_custom_fields = $custom_field_generator->create_text('facetoface_signup', ['text_cf_one', 'text_cf_two']);

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => $user_to_signup->id,
                ],
                'custom_fields' => [
                    [
                        'shortname' => 'text_cf_one',
                        'data' => 'TEXT_CF_1_DATA'
                    ],
                    [
                        'shortname' => 'text_cf_two',
                        'data' => 'TEXT_CF_2_DATA'
                    ]
                ]
            ]
        ];
        /** @var \mod_facetoface\seminar_event $found */
        $response = $this->resolve_graphql_mutation(static::MUTATION, $args);
        $resolved_custom_fields = $this->resolve_graphql_type(static::EVENT_BOOKING_TYPE, 'custom_fields', $response['booking'], [], null, $this->execution_context);

        $this->assertCount(3, $resolved_custom_fields); // Includes the default 'signupnote' custom field

        // Filtering out the default custom field
        $created_custom_fields = array_filter($resolved_custom_fields, function ($field) use ($booking_custom_fields) {
            return in_array($field['definition']['id'], $booking_custom_fields);
        });

        // Testing the signup default custom field
        $this->assertCount(2, $created_custom_fields);
        foreach ($created_custom_fields as $field){
            switch ($field['definition']['shortname']) {
                case 'text_cf_one':
                    $this->assertEquals('TEXT_CF_1_DATA', $field['value']); break;
                case 'text_cf_two':
                    $this->assertEquals('TEXT_CF_2_DATA', $field['value']); break;
            }
        }
    }

    public function test_includes_cancellation_custom_fields(): void {
        global $DB;
        $data_generator = $this->getDataGenerator();
        $user = $data_generator->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $data_generator->create_course();
        $user_to_signup = $data_generator->create_and_enrol($course);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(['course' => $course->id]);
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => $user_to_signup->id,
                ]
            ]
        ];

        $response = $this->resolve_graphql_mutation(static::MUTATION, $args);
        $resolved_cancellation_custom_fields = $this->resolve_graphql_type(static::EVENT_BOOKING_TYPE, 'cancellation_custom_fields', $response['booking'], [], null, $this->execution_context);

        $this->assertCount(1, $resolved_cancellation_custom_fields); // Default cancellation note custom field
        $this->assertEquals('cancellationnote', $resolved_cancellation_custom_fields[0]['definition']['shortname']);
        $this->assertNull($resolved_cancellation_custom_fields[0]['value']);
    }

    public function test_includes_event_custom_fields(): void {
        global $DB;
        $data_generator = $this->getDataGenerator();
        $user = $data_generator->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $data_generator->create_course();
        $user_to_signup = $data_generator->create_and_enrol($course);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(array('course' => $course->id));
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar_record->id]);

        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $data_generator->get_plugin_generator('totara_customfield');
        $event_custom_fields = $custom_field_generator->create_text('facetoface_session', ['event_text_cf']);
        $custom_field_generator->set_text((new seminar_event($event_id))->to_record(), $event_custom_fields['event_text_cf'], 'EVENT CUSTOM FIELD TEXT', 'facetofacesession', 'facetoface_session');

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => $user_to_signup->id,
                ]
            ]
        ];

        // Mutation resolver sets custom fields in ec
        $this->resolve_graphql_mutation(static::MUTATION, $args);
        $resolved_event_custom_fields = $this->execution_context->get_variable('custom_fields_facetofacesession')[$event_id];

        $this->assertCount(1, $resolved_event_custom_fields); // Default event custom field
        $this->assertEquals('event_text_cf', $resolved_event_custom_fields[0]['definition']['shortname']);
        $this->assertEquals('EVENT CUSTOM FIELD TEXT', $resolved_event_custom_fields[0]['value']);
    }

    public function test_includes_room_custom_fields(): void {
        global $DB;
        $data_generator = $this->getDataGenerator();
        $user = $data_generator->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        role_assign($managerrole->id, $user->id, context_system::instance());
        assign_capability('mod/facetoface:viewallsessions', CAP_ALLOW, $managerrole->id, context_system::instance());
        $this->setUser($user);

        $course = $data_generator->create_course();
        $user_to_signup = $data_generator->create_and_enrol($course);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $data_generator->get_plugin_generator('mod_facetoface');
        $seminar_record = $facetoface_generator->create_instance(array('course' => $course->id));

        $room = $facetoface_generator->add_site_wide_room([]);

        // Set up session.
        $sessiondate = new stdClass();
        $sessiondate->timestart = time() + 10 * DAYSECS;
        $sessiondate->timefinish = time() + 10 * DAYSECS + 60;
        $sessiondate->roomids = [$room->id];
        $sessiondata = [
            'facetoface' => $seminar_record->id,
            'sessiondates' => [$sessiondate],
        ];
        $event_id = $facetoface_generator->add_session($sessiondata);

        $args = [
            'input' => [
                'event' => [
                    'id' => $event_id,
                ],
                'user' => [
                    'id' => $user_to_signup->id,
                ]
            ]
        ];

        // Mutation resolver sets custom fields in ec
        $this->resolve_graphql_mutation(static::MUTATION, $args);
        $resolved_room_custom_fields = $this->execution_context->get_variable('custom_fields_facetofaceroom')[$room->id];

        $this->assertCount(2, $resolved_room_custom_fields); // Default room custom fields
        foreach ($resolved_room_custom_fields as $field){
            switch ($field['definition']['shortname']) {
                case 'building':
                case 'location':
                    $this->assertNull($field['value']); break;
                default:
                    $this->fail('Unrecognised room custom field');
            }
        }
    }
}
