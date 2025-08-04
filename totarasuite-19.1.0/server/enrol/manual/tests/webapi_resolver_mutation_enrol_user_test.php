<?php
/**
 * This file is part of Totara Core
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package enrol_manual
 */

use core\event\base;
use core\event\user_enrolment_created;
use core\orm\query\builder;
use core\webapi\execution_context;
use core_phpunit\testcase;
use enrol_manual\exception\enrol_user_exception;
use totara_webapi\graphql;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_oauth2\testing\generator as oauth2_generator;
use totara_webapi\request;
use totara_webapi\server;

/**
 * enrol_manual_webapi_resolver_mutation_enrol_user_test
 */
class enrol_manual_webapi_resolver_mutation_enrol_user_test extends testcase {
    private const MUTATION = 'enrol_manual_enrol_user';

    use webapi_phpunit_helper;

    /**
     * @return void
     * @covers ::resolve
     */
    public function test_enrol_manual_enrol_user(): void {
        global $DB;

        $gen = self::getDataGenerator();
        $api_user = $gen->create_user();

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        $learner_role = $DB->get_record('role', ['shortname' => 'student']);
        allow_assign($role->id, $learner_role->id);
        role_assign($role->id, $api_user->id, context_system::instance());

        self::setUser($api_user);
        $user = $gen->create_user();

        $course = $gen->create_course();

        // Enable the event sink.
        $sink = $this->redirectEvents();

        $time = time();
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user->id
                    ],
                    'course' => [
                        'id' => $course->id
                    ],
                    'role' => [
                        'id' => $learner_role->id
                    ],
                    'timestart' => $time - 86400,
                    'timeend' => $time + 186400,
                ]
            ]
        );

        // Collect any events triggered.
        $events = $sink->get_events();
        $sink->close();

        // Check that we get the expected number of events.
        $has_event_triggered = array_filter($events, function (base $event) {
            return $event instanceof user_enrolment_created;
        });
        $this->assertCount(1, $has_event_triggered);


        self::assertEquals($user->id, $result['user']->id);
        self::assertEquals($course->id, $result['course']->id);
        self::assertTrue($result['success']);
        self::assertFalse($result['was_already_enrolled']);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user->id
                    ],
                    'course' => [
                        'id' => $course->id
                    ],
                    'role' => [
                        'id' => $learner_role->id
                    ],
                    'timestart' => time() + 86400,
                ]
            ]
        );

        self::assertEquals($user->id, $result['user']->id);
        self::assertEquals($course->id, $result['course']->id);
        self::assertTrue($result['success']);
        self::assertTrue($result['was_already_enrolled']);
        self::assertTrue(
            builder::get_db()->record_exists('user_enrolments', ['userid' => $user->id])
        );
    }

    /**
     * @return void
     * @covers ::resolve
     */
    public function test_enrol_user_with_plugin_disabled(): void {
        global $CFG, $DB;

        self::setAdminUser();
        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        $course = $gen->create_course();
        $learner_role = $DB->get_record('role', ['shortname' => 'student']);

        $enabled_plugins = $CFG->enrol_plugins_enabled;
        set_config('enrol_plugins_enabled', '');

        try {
            $this->resolve_graphql_mutation(
                self::MUTATION,
                [
                    'input' => [
                        'user' => [
                            'id' => $user->id
                        ],
                        'course' => [
                            'id' => $course->id
                        ],
                        'role' => [
                            'id' => $learner_role->id
                        ],
                        'timestart' => time() + 86400,
                    ]
                ]
            );
            self::fail('enrol_user_exception expected');
        } catch (enrol_user_exception $exception) {
            self::assertStringContainsString('Manual enrolment plugin is not enabled.', $exception->getMessage());
        }
        set_config('enrol_plugins_enabled', $enabled_plugins);
    }

    /**
     * @return void
     * @covers ::resolve
     */
    public function test_enrol_manual_enrol_user_with_restricted_role(): void {
        global $DB;

        $gen = self::getDataGenerator();
        $api_user = $gen->create_user();

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        $workspacecreator_role = $DB->get_record('role', ['shortname' => 'workspacecreator']);
        role_assign($role->id, $api_user->id, context_system::instance());

        self::setUser($api_user);
        $user = $gen->create_user();

        $course = $gen->create_course();

        try {
            $this->resolve_graphql_mutation(
                self::MUTATION,
                [
                    'input' => [
                        'user' => [
                            'id' => $user->id
                        ],
                        'course' => [
                            'id' => $course->id
                        ],
                        'role' => [
                            'id' => $workspacecreator_role->id
                        ],
                        'timestart' => time() + 86400,
                    ]
                ]
            );
            self::fail('enrol_user_exception expected');
        } catch (enrol_user_exception $exception) {
            self::assertStringContainsString('This role can not be assigned to the target user on this course.', $exception->getMessage());
        }
    }

    /**
     * @return void
     * @covers ::resolve
     */
    public function test_enrol_user_with_timeend_before_timestart(): void {
        global $DB;

        self::setAdminUser();
        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        $course = $gen->create_course();
        $learner_role = $DB->get_record('role', ['shortname' => 'student']);

        $time = time();
        try {
            $this->resolve_graphql_mutation(
                self::MUTATION,
                [
                    'input' => [
                        'user' => [
                            'id' => $user->id
                        ],
                        'course' => [
                            'id' => $course->id
                        ],
                        'role' => [
                            'id' => $learner_role->id
                        ],
                        'timestart' => $time + 86400,
                        'timeend' => $time,
                    ]
                ]
            );
            self::fail('enrol_user_exception expected');
        } catch (enrol_user_exception $exception) {
            self::assertStringContainsString('Enrolment end date can not be before the start date.', $exception->getMessage());
        }
    }

    /**
     * @return void
     * @covers ::resolve
     */
    public function test_enrol_manual_enrol_user_with_suspend_and_time_restriction(): void {
        global $DB;

        $gen = self::getDataGenerator();
        $api_user = $gen->create_user();

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        $learner_role = $DB->get_record('role', ['shortname' => 'student']);
        allow_assign($role->id, $learner_role->id);
        role_assign($role->id, $api_user->id, context_system::instance());

        self::setUser($api_user);
        $user = $gen->create_user();

        $course = $gen->create_course();
        $gen->enrol_user($user->id, $course->id);

        $time = time();

        // Enrol again with suspend field.
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user->id
                    ],
                    'course' => [
                        'id' => $course->id
                    ],
                    'role' => [
                        'id' => $learner_role->id
                    ],
                    'timestart' => $time - 86400,
                    'timeend' => $time + 186400,
                    'suspend' => false
                ]
            ]
        );

        self::assertEquals($user->id, $result['user']->id);
        self::assertEquals($course->id, $result['course']->id);
        self::assertTrue($result['success']);
        self::assertTrue($result['was_already_enrolled']);
    }

    /**
     * @covers ::resolve
     */
    public function test_system_api_user_enrol_user_under_multitancy(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();

        $generator = \totara_cohort\testing\generator::instance();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();
        $user_t1 = $gen->create_user(['tenantid' => $tenant1->id]);
        $user_t2 = $gen->create_user(['tenantid' => $tenant2->id]);

        $course_t1 = $gen->create_course(['category' => $tenant1->categoryid]);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user_t1->id
                    ],
                    'course' => [
                        'id' => $course_t1->id
                    ],
                ]
            ]
        );

        self::assertEquals($user_t1->id, $result['user']->id);
        self::assertEquals($course_t1->id, $result['course']->id);
        self::assertTrue($result['success']);

        self::expectExceptionMessage('Can not manual enrol a target user into a target course.');
        self::expectException(enrol_user_exception::class);
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user_t2->id
                    ],
                    'course' => [
                        'id' => $course_t1->id
                    ],
                ]
            ]
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_enrol_user_with_external_api_endpoint(): void {
        global $DB;

        $generator = oauth2_generator::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        role_assign($role->id, $user->id, context_system::instance());
        $api_client = \totara_api\model\client::create(
            '123',
            $user->id,
            null,
            null,
            1,
            ['create_client_provider' => true]
        );
        /** @var \totara_oauth2\model\client_provider $client_provider */
        $client_provider = $api_client->oauth2_client_providers->first();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64; rv:100.0) Gecko/20100101 Firefox/100.0';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['CONTENT_LENGTH'] = '101';
        $access_token = $generator->create_access_token_from_client_provider(
            $client_provider->get_entity_copy(),
            time() + HOURSECS
        );
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer " . $access_token;

        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $course = $gen->create_course();

        $request = new request(
            \totara_webapi\endpoint_type\factory::get_instance(graphql::TYPE_EXTERNAL),
            [
                'operationName' => null,
                'query' => '
                    mutation {
                        enrol_manual_enrol_user(input: { user: { id: '. $user->id .' }, course: { id: '. $course->id.' } })
                        {
                            user {
                              id
                            }
                            course {
                              id
                            }
                            role {
                              id
                            }
                            success
                            was_already_enrolled
                            enrolment {
                              id
                            }
                       }
                   }
                '
            ]
        );
        $result = (new server(execution_context::create(graphql::TYPE_EXTERNAL)))->handle_request($request);

        $data = $result->data;
        $enrol_manual_enrol_user = $data['enrol_manual_enrol_user'];
        self::assertEquals($user->id, $enrol_manual_enrol_user['user']['id']);
        self::assertEquals($course->id, $enrol_manual_enrol_user['course']['id']);
    }

    /**
     * When a user is already enrolled in a course and the API user sends
     * another manual enrolment request, if the request does not provide
     * the 'timestart' value, we do not want to overwrite it with time()
     * @return void
     */
    public function test_manual_enrolment_do_not_update_timestart(): void {
        global $DB;

        $gen = self::getDataGenerator();
        $api_user = $gen->create_user();

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        $learner_role = $DB->get_record('role', ['shortname' => 'student']);
        allow_assign($role->id, $learner_role->id);
        role_assign($role->id, $api_user->id, context_system::instance());

        self::setUser($api_user);
        $user = $gen->create_user();

        $course = $gen->create_course();

        // Enrol the user for the first time to create the record
        $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [ 'id' => $user->id ],
                    'course' => [ 'id' => $course->id ],
                ]
            ]
        );

        // Create the event sink
        $sink = $this->redirectEvents();
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [ 'id' => $user->id ],
                    'course' => [ 'id' => $course->id ],
                ]
            ]
        );

        // Collect any events triggered.
        $events = $sink->get_events();
        $sink->close();
        // We do not expect events, because there is no information to update
        $this->assertCount(0, $events);
    }

}