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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package enrol_manual
 */

use core\event\base;
use core\event\user_enrolment_deleted;
use core\exception\unresolved_record_reference;
use core_phpunit\testcase;
use enrol_manual\exception\unenrol_user_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class enrol_manual_webapi_resolver_mutation_unenrol_user_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'enrol_manual_unenrol_user';

    /**
     * @covers ::resolve
     */
    public function test_unenrol_user(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $user1 = $gen->create_user();

        $course = $gen->create_course();
        $gen->enrol_user($user->id, $course->id);

        // Enable the event sink.
        $sink = $this->redirectEvents();

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
                ]
            ]
        );

        // Collect any events triggered.
        $events = $sink->get_events();
        $sink->close();

        // Check that we get the expected number of events.
        $has_event_triggered = array_filter($events, function (base $event) {
            return $event instanceof user_enrolment_deleted;
        });
        $this->assertCount(1, $has_event_triggered);

        self::assertTrue($result['success']);
        self::assertTrue($result['user_had_manual_enrolment']);
        self::assertNotEmpty($result['user']);
        self::assertNotEmpty($result['course']);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user1->id
                    ],
                    'course' => [
                        'id' => $course->id
                    ],
                ]
            ]
        );

        self::assertTrue($result['success']);
        self::assertFalse($result['user_had_manual_enrolment']);
        self::assertNotEmpty($result['user']);
        self::assertNotEmpty($result['course']);

        // User is enrolled by other enrolment methods.
        $user2 = $gen->create_user();
        $gen->enrol_user($user2->id, $course->id, null, 'self');
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user1->id
                    ],
                    'course' => [
                        'id' => $course->id
                    ],
                ]
            ]
        );

        self::assertTrue($result['success']);
        self::assertFalse($result['user_had_manual_enrolment']);
        self::assertNotEmpty($result['user']);
        self::assertNotEmpty($result['course']);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_for_systemuser(): void {
        $gen = self::getDataGenerator();
        $user1 = $gen->create_user();
        $user = $gen->create_user();
        $course = $gen->create_course();

        self::setUser($user);

        self::expectExceptionMessage('You do not have capabilities to view a target user');
        self::expectException(core\exception\unresolved_record_reference::class);
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user1->id
                    ],
                    'course' => [
                        'id' => $course->id
                    ],
                ]
            ]
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_unenrol_user_with_multitancy(): void {
        $gen = self::getDataGenerator();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();
        self::setup_apiuser($tenant1->id);

        // tennant manager.
        $user_t1 = $gen->create_user(['tenantid' => $tenant1->id]);
        $user_t2 = $gen->create_user(['tenantid' => $tenant2->id]);


        $course_t1 = $gen->create_course(['category' => $tenant1->categoryid]);
        $course_t2 = $gen->create_course(['category' => $tenant2->categoryid]);
        $course_t3 = $gen->create_course();

        $gen->enrol_user($user_t1->id, $course_t1->id);

        // Same tenant
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

        self::assertTrue($result['success']);
        self::assertTrue($result['user_had_manual_enrolment']);
        self::assertNotEmpty($result['user']);
        self::assertNotEmpty($result['course']);

        // Course under child category.
        $child_category = $gen->create_category(['parent' => $tenant1->categoryid]);
        $child_course = $gen->create_course(['category' => $child_category->id]);
        $gen->enrol_user($user_t1->id, $child_course->id);

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [
                        'id' => $user_t1->id
                    ],
                    'course' => [
                        'id' => $child_course->id
                    ],
                ]
            ]
        );

        self::assertTrue($result['success']);
        self::assertTrue($result['user_had_manual_enrolment']);
        self::assertNotEmpty($result['user']);
        self::assertNotEmpty($result['course']);

        try {
            // Different tenant
            $result = $this->resolve_graphql_mutation(
                self::MUTATION,
                [
                    'input' => [
                        'user' => [
                            'id' => $user_t1->id
                        ],
                        'course' => [
                            'id' => $course_t2->id
                        ],
                    ]
                ]
            );
            self::fail('unresolved_record_reference exception expected');
        } catch (unresolved_record_reference $exception) {
            self::assertStringContainsString('You do not have capabilities to view a target course.', $exception->getMessage());
        }
    }

    /**
     * @covers ::resolve
     */
    public function test_manual_plugin_disabled_in_system_level(): void {
        global $CFG;

        $enabled_plugins = $CFG->enrol_plugins_enabled;
        $this->setup_apiuser();
        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        $course = $gen->create_course();
        $gen->enrol_user($user->id, $course->id);

        // Disabled the manual plugin on global settings.
        set_config('enrol_plugins_enabled', 'guest');

        try {
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
                    ]
                ]
            );
            self::fail('unenrol_user_exception exception expected');
        } catch (unenrol_user_exception $exception) {
            self::assertStringContainsString('Manual enrolment plugin is not enabled.', $exception->getMessage());
        }

        set_config('enrol_plugins_enabled',  $enabled_plugins);
    }

    /**
     * @covers ::resolve
     */
    public function test_manual_plugin_disabled_in_course_level(): void {
        $this->setup_apiuser();
        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        $course = $gen->create_course();
        $gen->enrol_user($user->id, $course->id);

        $manual_plugin = enrol_get_plugin('manual');
        $instances = enrol_get_instances($course->id, true);
        $target_instance = null;
        foreach ($instances as $instance) {
            if ($instance->enrol === 'manual') {
                $target_instance = $instance;
                break;
            }
        }

        // Disabled the plugin
        $manual_plugin->update_status($target_instance, ENROL_INSTANCE_DISABLED);

        self::expectExceptionMessage('Manual enrolment is not enabled in this course.');
        self::expectException(unenrol_user_exception::class);
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
                ]
            ]
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_param_exception(): void {
        self::setAdminUser();

        self::expectExceptionMessage('User reference is required.');
        self::expectException(unenrol_user_exception::class);
        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            [
                'input' => [
                    'user' => [],
                    'course' => [],
                ]
            ]
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_unenrol_user_for_apiuser(): void {
        $this->setup_apiuser();

        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        $course = $gen->create_course();
        $gen->enrol_user($user->id, $course->id);

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
                ]
            ]
        );

        self::assertTrue($result['user_had_manual_enrolment']);
        self::assertTrue($result['success']);
        self::assertNotEmpty($result['user']);
        self::assertNotEmpty($result['course']);
    }

    /**
     * @param int|null $tenant_id
     * @return array|stdClass
     */
    private function setup_apiuser(int $tenant_id = null) {
        $gen = self::getDataGenerator();

        $options = [];
        if ($tenant_id) {
            $options['tenantid'] = $tenant_id;
        }
        $api_user = $gen->create_user($options);

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $api_user->id, context_system::instance());

        self::setUser($api_user);
        return $api_user;
    }
}