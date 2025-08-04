<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\data_provider\idp_user_map;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp_user_map as idp_user_map_model;
use auth_ssosaml\model\saml_log_entry;
use auth_ssosaml\model\user\manager;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\provider\logging\contract;
use auth_ssosaml\provider\logging\factory as logger_factory;
use core\entity\user;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\model\user\manager
 * @group auth_ssosaml
 */
class auth_ssosaml_model_user_manager_test extends base_saml_testcase {
    /**
     * Assert that no user fields are updated when the mapping isn't set.
     *
     * @return void
     */
    public function test_field_maps(): void {
        // Confirm that users are never changed if no mapping is defined
        $idp = idp::create([], []);

        $user = new stdClass();
        $user->firstname = 'Daniel';
        $user->lastname = 'Field';
        $user->email = 'person@example.com';
        $user->username = 'dfield';
        $user->custom2 = 'I am a thing';

        $original = clone $user;

        $this->invoke_for_user($idp, $user, false);

        $this->assertEqualsCanonicalizing($original, $user);
        $this->assertCount(5, get_object_vars($user));

        // Confirm that users are populated on create
        $user = new stdClass();

        // Create the user custom fields
        /** @var \totara_core\testing\generator $generator */
        $generator = self::getDataGenerator()->get_plugin_generator('totara_core');

        $generator->create_custom_profile_field([
            'datatype' => 'text',
            'name' => 'custom',
            'shortname' => 'custom',
        ]);

        $idp = idp::create([
            'field_mapping_config' => [
                'delimiter' => ',',
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'fname', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'lname', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'uname', 'update' => 'CREATE'],
                    ['internal' => 'profile_field_custom', 'external' => 'custom', 'update' => 'CREATE'],
                ],
            ]
        ], []);
        $this->invoke_for_user($idp, $user, false);
        $this->assertCount(5, get_object_vars($user));
        $user = (array) $user;
        $this->assertEqualsCanonicalizing([
            'firstname' => 'Bob',
            'lastname' => 'Example',
            'email' => 'bob@example.com',
            'username' => 'bexample',
            'custom' => 'Value',
        ], $user);

        // Now check that we don't update on login
        $user = new stdClass();
        $user->firstname = 'Alex';
        $user->lastname = 'Alex';

        $this->invoke_for_user($idp, $user, true);
        $this->assertCount(2, get_object_vars($user));
        $user = (array) $user;
        $this->assertEqualsCanonicalizing([
            'firstname' => 'Alex',
            'lastname' => 'Alex',
        ], $user);

        // Check we do update on login
        $user = new stdClass();
        $user->firstname = 'Daniel';
        $user->lastname = 'Field';
        $user->email = 'person@example.com';
        $user->username = 'dfield';
        $user->profile_field_custom = 'I am a thing';
        $idp = idp::create([
            'field_mapping_config' => [
                'delimiter' => ',',
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'fname', 'update' => 'LOGIN'],
                    ['internal' => 'lastname', 'external' => 'lname', 'update' => 'LOGIN'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'LOGIN'],
                    ['internal' => 'username', 'external' => 'uname', 'update' => 'LOGIN'],
                    ['internal' => 'profile_field_custom', 'external' => 'custom', 'update' => 'LOGIN'],
                ],
            ]
        ], []);
        $this->invoke_for_user($idp, $user, true);
        $this->assertCount(5, get_object_vars($user));
        $user = (array) $user;
        $this->assertEqualsCanonicalizing([
            'firstname' => 'Bob',
            'lastname' => 'Example',
            'email' => 'bob@example.com',
            'username' => 'bexample',
            'custom' => 'Value',
        ], $user);

        // Check we can mix & match the updates
        $user = new stdClass();
        $user->firstname = 'Daniel';
        $user->lastname = 'Field';
        $user->email = 'person@example.com';
        $user->username = 'dfield';
        $user->profile_field_custom2 = 'I am a thing';
        $idp = idp::create([
            'field_mapping_config' => [
                'delimiter' => ',',
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'fname', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'lname', 'update' => 'LOGIN'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'uname', 'update' => 'LOGIN'],
                    ['internal' => 'profile_field_custom', 'external' => 'custom', 'update' => 'LOGIN'],
                ],
            ]
        ], []);
        $this->invoke_for_user($idp, $user, true);
        $this->assertCount(6, get_object_vars($user));
        $user = (array) $user;
        $this->assertEqualsCanonicalizing([
            'firstname' => 'Daniel',
            'lastname' => 'Example',
            'email' => 'person@example.com',
            'username' => 'bexample',
            'profile_field_custom2' => 'I am a thing',
            'profile_field_custom' => 'Value',
        ], $user);
    }

    /**
     * Assert fields doesn't match with response.
     *
     * @return void
     */
    public function test_unknown_fields_in_response(): void {
        $user = new stdClass();
        $idp = idp::create([
            'field_mapping_config' => [
                'delimiter' => ',',
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'f_name', 'update' => 'CREATE'], //make field unknown to response
                    ['internal' => 'lastname', 'external' => 'l_name', 'update' => 'CREATE'], //make field unknown to response
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'uname', 'update' => 'CREATE'],
                ],
            ]
        ], []);

        $response = authn_response::make([
            'name_id' => 'ABC',
            'name_id_format' => 'ABC',
            'issuer' => 'ABC',
            'status' => 'ABC',
            'attributes' => [
                'fname' => 'Bob',
                'lname' => 'Example',
                'email' => 'bob@example.com',
                'uname' => 'bexample',
            ]
        ]);
        $method = $this->get_map_attributes_to_user_fields_method();
        $changed_count = $method->invoke(null, $idp, $response, $user, false);
        $this->assertEquals(2, $changed_count);
        $this->assertSame('bexample', $user->username);
        $this->assertSame('bob@example.com', $user->email);

        //Make email mapping field unknown to response
        $user = new stdClass();
        $idp = idp::create([
            'field_mapping_config' => [
                'delimiter' => ',',
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'fname', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'lname', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email_1', 'update' => 'CREATE'], //make field unknown to response
                    ['internal' => 'username', 'external' => 'uname', 'update' => 'CREATE'],
                ],
            ]
        ], []);

        $response = authn_response::make([
            'name_id' => 'ABC',
            'name_id_format' => 'ABC',
            'issuer' => 'ABC',
            'status' => 'ABC',
            'attributes' => [
                'fname' => 'Bob',
                'lname' => 'Example',
                'email' => 'bob@example.com',
                'uname' => 'bexample',
            ]
        ]);
        $method = $this->get_map_attributes_to_user_fields_method();
        $changed_count = $method->invoke(null, $idp, $response, $user, false);
        $this->assertEquals(3, $changed_count);
        $this->assertSame('bexample', $user->username);
        $this->assertSame('Bob', $user->firstname);
        $this->assertSame('Example', $user->lastname);
    }

    /**
     * @return void
     */
    public function test_process_login_create_user(): void {
        global $DB;

        $idp = idp::create([
            'status' => 1,
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'firstname', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'lastname', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'username', 'update' => 'CREATE'],
                ]
            ],
            'create_users' => false,
            'idp_user_id_field' => 'username',
            'totara_user_id_field' => 'username',
        ], []);
        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'a@example.com',
                'username' => ['a', 'b'],
            ],
            'name_id' => 'ab',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);

        $manager = new manager($idp);

        $sink = $this->redirectEvents();
        // Expect to see it fail because create_user is disabled
        $result = $manager->process_login($response);
        $events = $sink->get_events();
        $sink->close();
        $event = array_pop($events);
        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assert_login_error('authentication_failure', $result);
        $idp->update(['create_users' => true], []);

        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'a@example.com',
                'username' => '',
            ],
            'name_id' => 'ab',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);

        // Expect to see it fail because it has no username
        $sink = $this->redirectEvents();
        $result = $manager->process_login($response);
        $events = $sink->get_events();
        $sink->close();
        $event = array_pop($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assert_login_error('authentication_failure', $result);
        $this->assertEquals('The user identifier IdP field "username" can not be found in the IdP response.', $result['reason']);

        // Expect to see it fail because it has a duplicate email
        $existing_user = $this->getDataGenerator()->create_user(['email' => 'ally-b@example.com']);
        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => $existing_user->email,
                'username' => 'ab',
            ],
            'name_id' => 'ab',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);
        $sink = $this->redirectEvents();
        $result = $manager->process_login($response);
        $events = $sink->get_events();
        $sink->close();
        $event = array_pop($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assert_login_error('authentication_failure', $result);

        // Create and confirm
        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'a@example.com',
                'username' => 'abc',
            ],
            'name_id' => 'ab',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);
        try {
            $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertNotEmpty($exception->link);
        }

        // Assert the user was created & has a value in the password field
        $new_user = $DB->get_record('user', ['username' => 'abc']);
        $this->assertSame('a@example.com', $new_user->email);
        $this->assertSame(AUTH_PASSWORD_NOT_CACHED, $new_user->password);
    }

    /**
     * @return void
     */
    public function test_process_login_update_user_no_confirmation(): void {
        global $CFG, $DB;

        $idp = idp::create([
            'status' => 1,
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'firstname', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'lastname', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'username', 'update' => 'CREATE'],
                ]
            ],
            'create_users' => false,
            'idp_user_id_field' => 'email',
            'totara_user_id_field' => 'email',
            'autolink_users' => idp::AUTOLINK_NO_CONFIRMATION,
        ], []);
        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'a@example.com',
                'username' => ['a', 'b'],
            ],
            'name_id' => 'ab',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);

        $manager = new manager($idp);

        // Add multiple users with the same identifier
        $CFG->allowaccountssameemail = 1;
        $user_b = $this->getDataGenerator()->create_user(['email' => 'a@example.com', 'auth' => 'ssosaml']);
        $user_c = $this->getDataGenerator()->create_user(['email' => 'a@example.com', 'auth' => 'ssosaml']);

        // Expect to see it fail because we have duplicate accounts is disabled
        $sink = $this->redirectEvents();
        $result = $manager->process_login($response);
        $events = $sink->get_events();
        $sink->close();
        $event = array_pop($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\user_login_failed', $event);
        $this->assert_login_error('authentication_failure', $result);

        // Change $user_c into a different authtype
        $user_c->auth = 'manual';
        $DB->update_record('user', $user_c);

        try {
            $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertNotEmpty($exception->link);
        }
    }

    /**
     * Test non-saml user fields are not updated on login
     *
     * @return void
     */
    public function test_non_saml_user_profile_fields_are_not_updated(): void {
        $idp = idp::create([
            'status' => 1,
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'firstname', 'update' => 'LOGIN'],
                    ['internal' => 'lastname', 'external' => 'lastname', 'update' => 'LOGIN'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'username', 'update' => 'LOGIN'],
                ]
            ],
            'create_users' => false,
            'idp_user_id_field' => 'email',
            'totara_user_id_field' => 'email',
            'autolink_users' => idp::AUTOLINK_NO_CONFIRMATION,
        ], []);
        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'a@example.com',
                'username' => ['a', 'b'],
            ],
            'name_id' => 'ab',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);

        $manager = new manager($idp);

        $user_a = $this->getDataGenerator()->create_user(['email' => 'a@example.com', 'auth' => 'manual']);
        try {
            $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
        }

        $user_a = new user($user_a->id);

        // Assert the first & last name do not update
        $this->assertNotEquals('Ally', $user_a->firstname);
        $this->assertNotEquals('McBeal', $user_a->lastname);
    }

    /**
     * @return void
     */
    public function test_process_login_update_user_auto_link_none(): void {
        global $DB, $USER;

        $idp = idp::create([
            'status' => 1,
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'firstname', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'lastname', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'username', 'update' => 'CREATE'],
                ]
            ],
            'create_users' => false,
            'idp_user_id_field' => 'email',
            'totara_user_id_field' => 'email',
            'autolink_users' => idp::AUTOLINK_NONE,
        ], []);
        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'a@example.com',
                'username' => ['abc'],
            ],
            'name_id' => 'abc',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);

        $manager = new manager($idp);
        $user_b = $this->getDataGenerator()->create_user(['email' => 'a@example.com', 'auth' => 'manual']);

        // Expect to see it fail because the only single result isn't ssosaml
        $user_b->auth = 'ssosaml';
        $test_cases = [
            ['suspended', 1],
            ['confirmed', 0],
            ['auth', 'nologin'],
            ['auth', 'manual']
        ];
        foreach ($test_cases as $test_case) {
            $user_clone = clone($user_b);
            $user_clone->{$test_case[0]} = $test_case[1];
            $DB->update_record('user', $user_clone);

            $sink = $this->redirectEvents();
            $result = $manager->process_login($response);
            $events = $sink->get_events();
            $sink->close();
            $event = array_pop($events);

            // Check that the event data is valid.
            $this->assertInstanceOf('\core\event\user_login_failed', $event);
            $this->assert_login_error('authentication_failure', $result);
        }

        // Make the user good again
        $DB->update_record('user', $user_b);

        try {
            $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertNotEmpty($exception->link);
        }
        $this->assertEquals($USER->id, $user_b->id);
    }

    /**
     * @return void
     */
    public function test_process_login_with_relay_state(): void {
        $idp = idp::create([
            'status' => 1,
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'firstname', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'lastname', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'username', 'update' => 'CREATE'],
                ]
            ],
            'create_users' => false,
            'idp_user_id_field' => 'email',
            'totara_user_id_field' => 'email',
            'autolink_users' => idp::AUTOLINK_NONE,
        ], []);

        $manager = new manager($idp);
        $this->getDataGenerator()->create_user(['email' => 'a@example.com', 'auth' => 'ssosaml']);

        $auth_response = [
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'a@example.com',
                'username' => ['abc'],
            ],
            'name_id' => 'abc',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => '',
        ];

        // Test with valid relay state
        $relay_state = (new moodle_url('/auth/ssosaml/test.php'))->out();

        $auth_response['relay_state'] = $relay_state;
        $response = authn_response::make($auth_response);
        try {
            $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertNotEmpty($exception->link);
            $this->assertEquals($relay_state, $exception->link);
        }

        // Test with Invalid relay state
        $relay_state = 'htt//example.com';
        $auth_response['relay_state'] = $relay_state;
        $response = authn_response::make($auth_response);

        try {
            $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertNotEmpty($exception->link);
            $this->assertNotEquals($relay_state, $exception->link);
        }
    }

    /**
     * @return void
     */
    public function test_process_login_update_user_auto_link_email(): void {
        $idp = idp::create([
            'status' => 1,
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'firstname', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'lastname', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'username', 'update' => 'CREATE'],
                ]
            ],
            'create_users' => false,
            'idp_user_id_field' => 'email',
            'totara_user_id_field' => 'email',
            'autolink_users' => idp::AUTOLINK_WITH_CONFIRMATION,
        ], []);
        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'a@example.com',
                'username' => ['abc'],
            ],
            'name_id' => 'abc',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);

        $manager = new manager($idp);
        $user_b = $this->getDataGenerator()->create_user(['email' => 'a@example.com', 'auth' => 'manual']);

        // Make sure they don't have a map already
        $map = idp_user_map::get_latest_for_user($idp->id, $user_b->id);
        $this->assertNull($map);

        $message_sink = $this->redirectEmails();
        $result = $manager->process_login($response);
        $message_sink->close();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('action', $result);
        $this->assertSame('confirm', $result['action']);

        // Make sure we have a pending confirmation record
        $map = idp_user_map::get_latest_for_user($idp->id, $user_b->id);
        $this->assertInstanceOf(idp_user_map_model::class, $map);
        $this->assertFalse($map->is_confirmed());

        // Confirm the record
        $map->validate_user_with_code($map->code);

        $message_sink = $this->redirectEmails();
        $message_sink->clear();
        try {
            $result = $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertNotEmpty($exception->link);
            $message_sink->close();
            $this->assertEquals(0, $message_sink->count());
        }
    }

    /**
     * Test user identifier matching is case insensitive
     *
     * @return void
     */
    public function test_user_identifier_case_insensitive(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['email' => 'fOO@eXample.cOm', 'auth' => 'manual']);

        $idp = idp::create([
            'status' => 1,
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                ]
            ],
            'create_users' => false,
            'idp_user_id_field' => 'email',
            'totara_user_id_field' => 'email',
            'autolink_users' => idp::AUTOLINK_NO_CONFIRMATION,
        ], []);
        $response = authn_response::make([
            'attributes' => [
                'firstname' => 'Ally',
                'lastname' => 'McBeal',
                'email' => 'Foo@Example.Com',
                'username' => 'Foo',
            ],
            'name_id' => 'ab',
            'name_id_format' => idp\config\nameid::FORMAT_UNSPECIFIED,
            'issuer' => 'Unit Tests',
            'status' => ''
        ]);

        $manager = new manager($idp);
        try {
            $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertNotEmpty($exception->link);
        }

        $idp->update([
            'idp_user_id_field' => 'idnumber',
            'totara_user_id_field' => 'idnumber',
        ], []);
        $DB->update_record('user', [
            'id' => $user->id,
            'email' => 'a@b.com',
            'idnumber' => 'FoO',
        ]);

        $manager = new manager($idp);
        try {
            $manager->process_login($response);
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertNotEmpty($exception->link);
        }
    }

    /**
     * Assert username in lowercase.
     *
     * @return void
     */
    public function test_username_in_lowercase(): void {
        $user = new stdClass();
        $idp = idp::create([
            'field_mapping_config' => [
                'delimiter' => ',',
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'f_name', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'l_name', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'uname', 'update' => 'CREATE'],
                ],
            ]
        ], []);

        $response = authn_response::make([
            'name_id' => 'ABC',
            'name_id_format' => 'ABC',
            'issuer' => 'ABC',
            'status' => 'ABC',
            'attributes' => [
                'f_name' => 'Bob',
                'l_name' => 'Example',
                'email' => 'BOB@example.com',
                'uname' => 'BExample', //Pass username in uppercase and lowercase
            ]
        ]);
        $method = $this->get_map_attributes_to_user_fields_method();
        $changed_count = $method->invoke(null, $idp, $response, $user, false);
        $this->assertEquals(4, $changed_count);

        $this->assertSame('BOB@example.com', $user->email);
        $this->assertSame('bexample', $user->username); // Username should always be in lowercase
        $this->assertSame('Bob', $user->firstname);
        $this->assertSame('Example', $user->lastname);
    }

    /**
     * Assert phone and mobile fields.
     *
     * @return void
     */
    public function test_phone_and_mobile_fields_are_cleaned(): void {
        // All field should have values.
        $user = new stdClass();
        $idp = idp::create([
            'debug' => true,
            'field_mapping_config' => [
                'delimiter' => ',',
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'f_name', 'update' => 'CREATE'],
                    ['internal' => 'lastname', 'external' => 'l_name', 'update' => 'CREATE'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => 'uname', 'update' => 'CREATE'],
                    ['internal' => 'phone1', 'external' => 'phone', 'update' => 'CREATE'],
                    ['internal' => 'phone2', 'external' => 'mobile', 'update' => 'CREATE'],
                ],
            ]
        ], []);
        $log_id = logger_factory::get_logger($idp)->log_request('test_id1', contract::TYPE_IDP_LOGIN, '<xml>Response here</xml>');

        $idp_data = [
            'name_id' => 'ABC',
            'name_id_format' => 'ABC',
            'issuer' => 'ABC',
            'status' => 'ABC',
            'attributes' => [
                'f_name' => 'Bob',
                'l_name' => 'Example',
                'email' => 'bob@example.com',
                'uname' => 'bexample',
                'mobile' => '+64 (999) 7777-8888',
                'phone' => '(012) [20]9-456-1234'
            ],
            'log_id' => $log_id,
        ];

        $response = authn_response::make($idp_data);
        $method = $this->get_map_attributes_to_user_fields_method();
        $changed_count = $method->invoke(null, $idp, $response, $user, false);
        $this->assertEquals(6, $changed_count);
        $user = (array) $user;
        $this->assertEqualsCanonicalizing([
            'f_name' => 'Bob',
            'l_name' => 'Example',
            'email' => 'bob@example.com',
            'uname' => 'bexample',
            'mobile' => '+64 (999) 7777-8888',
            'phone' => '(012) [20]9-456-1234'
        ], $user);

        // Set phone field to have a value of 20+ characters
        $idp_data['attributes']['phone'] = '(012) [20] 9-456-1234-1234';

        $user2 = new stdClass();
        $response2 = authn_response::make($idp_data);
        $method = $this->get_map_attributes_to_user_fields_method();
        $changed_count = $method->invoke(null, $idp, $response2, $user2, false);

        $this->assertEquals(6, $changed_count);
        $user2 = (array) $user2;
        $this->assertEqualsCanonicalizing([
            'f_name' => 'Bob',
            'l_name' => 'Example',
            'email' => 'bob@example.com',
            'uname' => 'bexample',
            'mobile' => '+64 (999) 7777-8888',
            'phone' => '' // phone field is empty as its exceeding 20 characters limit
        ], $user2);

        // Assert a notice is reported in the logs
        $log = saml_log_entry::load_by_id($log_id);
        $this->assertEquals(get_string('error:invalid_phone_field_value', 'auth_ssosaml', 'Phone'), $log->notice);
    }

    /**
     * Assert that users with blank passwords can still login.
     *
     * @return void
     */
    public function test_blank_passwords_are_ignored(): void {
        global $DB, $CFG;

        // Configure our IDP
        $idp = idp::create([
            'status' => 1,
            'field_mapping_config' => [
                'delimiter' => ',',
                'field_maps' => [
                    ['internal' => 'firstname', 'external' => 'f_name', 'update' => 'LOGIN'],
                    ['internal' => 'lastname', 'external' => 'l_name', 'update' => 'LOGIN'],
                    ['internal' => 'email', 'external' => 'email', 'update' => 'LOGIN'],
                    ['internal' => 'username', 'external' => 'uname', 'update' => 'LOGIN'],
                ],
                'create_users' => false,
                'idp_user_id_field' => 'uname',
                'totara_user_id_field' => 'username',
            ]
        ], []);

        // Set a password policy (min 10 chars, must have 5 digits (silly))
        $CFG->passwordpolicy = true;
        $CFG->minpasswordlength = 10;
        $CFG->minpassworddigits = 5;

        // Create a dummy user with no password
        $dummy_user = $this->getDataGenerator()->create_user([
            'firstname' => 'J',
            'lastname' => 'J',
            'email' => 'j@example.com',
            'uname' => 'jamie',
            'auth' => 'ssosaml',
        ]);
        $dummy_user->password = '';
        $DB->update_record('user', $dummy_user);
        $this->setUser($dummy_user);

        $this->validate_user_update([
            'f_name' => 'Jamie',
            'l_name' => 'Jones',
            'email' => 'jamie@example.com',
            'uname' => 'jamie',
        ], $idp->id, $dummy_user->id);

        // Set a invalid password
        $dummy_user->password = hash_internal_user_password('abc');
        $DB->update_record('user', $dummy_user);
        $this->validate_user_update([
            'f_name' => 'Jamie2',
            'l_name' => 'Jones2',
            'email' => 'jamie2@example.com',
            'uname' => 'jamie',
        ], $idp->id, $dummy_user->id);

        // Set a valid password
        $dummy_user->password = hash_internal_user_password('abcEF12345');
        $DB->update_record('user', $dummy_user);
        $this->validate_user_update([
            'f_name' => 'Jamie2',
            'l_name' => 'Jones2',
            'email' => 'jamie2@example.com',
            'uname' => 'jamie',
        ], $idp->id, $dummy_user->id);
    }

    /**
     * Helper function to call the user update functions in user manager.
     * @see test_blank_passwords_are_ignored
     *
     * @param array $user_attributes
     * @param int $idp_id
     * @param int $dummy_user_id
     * @return void
     */
    private function validate_user_update(array $user_attributes, int $idp_id, int $dummy_user_id): void {
        global $DB, $USER;

        $response = authn_response::make([
            'name_id' => 'ABC',
            'name_id_format' => 'ABC',
            'issuer' => 'ABC',
            'status' => 'ABC',
            'attributes' => $user_attributes
        ]);

        try {
            manager::complete_login_callback(false, $response, $idp_id);
        } catch (\moodle_exception $ex) {
            // We expect a redirect as the last line of complete_login_callback is a redirect
            if ($ex->errorcode !== 'redirecterrordetected') {
                throw $ex;
            }
        }

        $user = $DB->get_record('user', ['id' => $dummy_user_id]);
        $this->assertSame($user_attributes['f_name'], $user->firstname);
        $this->assertSame($user_attributes['l_name'], $user->lastname);
        $this->assertSame($user_attributes['email'], $user->email);

        // Confirm the session $USER also updated
        $this->assertSame($user_attributes['f_name'], $USER->firstname);
        $this->assertSame($user_attributes['l_name'], $USER->lastname);
        $this->assertSame($user_attributes['email'], $USER->email);
    }

    /**
     * @param idp $idp
     * @param stdClass $user
     * @param bool $is_login
     */
    private function invoke_for_user(idp $idp, stdClass &$user, bool $is_login): void {
        $response = authn_response::make([
            'name_id' => 'ABC',
            'name_id_format' => 'ABC',
            'issuer' => 'ABC',
            'status' => 'ABC',
            'attributes' => [
                'fname' => 'Bob',
                'lname' => 'Example',
                'email' => 'bob@example.com',
                'uname' => 'bexample',
                'custom' => 'Value'
            ]
        ]);

        $method = $this->get_map_attributes_to_user_fields_method();
        $method->invoke(null, $idp, $response, $user, $is_login);
    }

    /**
     * Get a reflection of map_attributes_to_user_fields method to be used in testing
     *
     * @return ReflectionMethod
     */
    private function get_map_attributes_to_user_fields_method(): ReflectionMethod {
        $class = new ReflectionClass(manager::class);
        $method = $class->getMethod('map_attributes_to_user_fields');
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param string $expected_error
     * @param mixed $result
     * @return void
     */
    private function assert_login_error(string $expected_error, $result): void {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('action', $result);
        $this->assertArrayHasKey('error', $result);

        $this->assertSame('error', $result['action']);
        $this->assertSame($expected_error, $result['error']);
    }

    /**
     * @param $result
     * @return void
     */
    private function assert_login_success($result): void {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('action', $result);
        $this->assertArrayHasKey('url', $result);

        $this->assertSame('login', $result['action']);
    }
}
