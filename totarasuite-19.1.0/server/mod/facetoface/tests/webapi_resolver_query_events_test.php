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

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use totara_webapi\client_aware_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_query_events_test extends testcase {
    use webapi_phpunit_helper;

    private const QUERY = "mod_facetoface_events";

    private function set_capability(string $capability, int $permission, int $userid, context $context): void {
        $roles = get_archetype_roles('user');
        foreach ($roles as $role) {
            role_assign($role->id, $userid, $context->id);
            assign_capability($capability, $permission, $role->id, $context, true);
        }
    }

    public function test_with_no_events() {
        $this->setAdminUser();

        $result = $this->resolve_graphql_query(static::QUERY);

        $this->assertCount(0, $result['items']);
        $this->assertEquals(0, $result['total']);
        $this->assertEmpty($result['next_cursor']);
    }

    /**
     * @throws coding_exception
     */
    public function test_with_one_event() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $event = $facetoface_generator->create_session_for_course($course);

        $result = $this->resolve_graphql_query(static::QUERY);

        $this->assertCount(1, $result['items']);
        $this->assertEquals(1, $result['total']);
        $this->assertEmpty($result['next_cursor']);

        $resolved_id = $this->resolve_graphql_type('mod_facetoface_event', 'id', $result['items'][0], [], null, $this->execution_context);
        $this->assertEquals($event->get_id(), $resolved_id);
    }

    /**
     * @throws coding_exception
     */
    public function test_with_many_events() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $amount_to_create = 40;
        for ($amount_created = 0; $amount_created < $amount_to_create; $amount_created++) {
            $facetoface_generator->create_session_for_course($course);
        }

        $result = $this->resolve_graphql_query(static::QUERY);

        // We expect the items returned be 20 as this is the default page size (\mod_facetoface\data_providers\events::DEFAULT_PAGE_SIZE)
        $this->assertCount(20, $result['items']);
        $this->assertEquals(40, $result['total']);
        $this->assertNotEmpty($result['next_cursor']);
    }

    public function test_capabilities_has_capability() {
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $this->set_capability(
            'mod/facetoface:viewallsessions',
            CAP_ALLOW,
            $user->id,
            context_system::instance()
        );

        $this->setUser($user->id);

        $result = $this->resolve_graphql_query(static::QUERY);
        $this->assertIsArray($result);
    }

    public function test_capabilities_insufficient_capabilities() {
        $core_generator = $this->getDataGenerator();

        $user = $core_generator->create_user();
        $this->setUser($user->id);

        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage("Sorry, but you do not currently have permissions to do that (View all seminar sessions)");

        $this->resolve_graphql_query(static::QUERY);
    }

    public function test_multiple_sort_columns_throws_exception() {
        $this->setAdminUser();

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage("Sorting by more than one column is not currently supported");

        $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'registration_time_start',
                            'direction' => 'asc',
                        ],
                        [
                            'column' => 'id',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * @throws coding_exception
     */
    public function test_filters_by_course_id() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course_1 = $core_generator->create_course();
        $course_2 = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $event_in_course_1 = $facetoface_generator->create_session_for_course($course_1);
        $event_in_course_2 = $facetoface_generator->create_session_for_course($course_2);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'course_id' => $course_1->id,
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($event_in_course_1->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'course_id' => $course_2->id,
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($event_in_course_2->get_id(), $result['items'][0]->get_id());
    }

    /**
     * @throws coding_exception
     */
    public function test_filters_by_seminar_id() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $event_1 = $facetoface_generator->create_session_for_course($course);
        $event_2 = $facetoface_generator->create_session_for_course($course);
        $facetoface_generator->create_session_for_course($course);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'seminar_id' => $event_1->get_seminar()->get_id(),
                    ]
                ]
            ]
        );

        $this->assertcount(1, $result['items']);
        $this->assertequals($event_1->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'seminar_id' => $event_2->get_seminar()->get_id(),
                    ]
                ]
            ]
        );

        $this->assertcount(1, $result['items']);
        $this->assertequals($event_2->get_id(), $result['items'][0]->get_id());
    }

    public function test_filters_by_event_ids() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $event_1 = $facetoface_generator->create_session_for_course($course);
        $event_2 = $facetoface_generator->create_session_for_course($course);
        $facetoface_generator->create_session_for_course($course);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'event_ids' => [$event_1->get_id()],
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($event_1->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'event_ids' => [$event_1->get_id(), $event_2->get_id()],
                    ]
                ]
            ]
        );

        $this->assertCount(2, $result['items']);
        foreach($result['items'] as $event) {
            $this->assertContains($event->get_id(), [$event_1->get_id(), $event_2->get_id()]);
        }
    }

    public function test_filters_by_status() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $cancelled_event = $facetoface_generator->create_session_for_course($course);
        $cancelled_event->cancel();
        $active_event = $facetoface_generator->create_session_for_course($course);


        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'status' => 'ACTIVE',
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($active_event->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'status' => 'CANCELLED',
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($cancelled_event->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'status' => 'BOTH',
                    ]
                ]
            ]
        );

        $this->assertCount(2, $result['items']);
        foreach($result['items'] as $event) {
            $this->assertContains($event->get_id(), [$active_event->get_id(), $cancelled_event->get_id()]);
        }
    }

    public function test_filters_by_tense() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $past_event = $facetoface_generator->create_session_for_course($course, -100);
        $future_event = $facetoface_generator->create_session_for_course($course, 200);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'tense' => 'PAST',
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($past_event->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'tense' => 'FUTURE',
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($future_event->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'tense' => 'BOTH',
                    ]
                ]
            ]
        );

        $this->assertCount(2, $result['items']);
        foreach($result['items'] as $event) {
            $this->assertContains($event->get_id(), [$future_event->get_id(), $past_event->get_id()]);
        }
    }

    public function test_filters_by_starts_after() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $past_event = $facetoface_generator->create_session_for_course($course, -100);
        $future_event = $facetoface_generator->create_session_for_course($course, 200);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'starts_after' => $future_event->get_mintimestart() + 100,
                    ]
                ]
            ]
        );

        $this->assertCount(0, $result['items']);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'starts_after' => $past_event->get_mintimestart() + 100,
                    ]
                ]
            ]
        );

        $this->assertEquals($future_event->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'starts_after' => $past_event->get_mintimestart() - 100,
                    ]
                ]
            ]
        );

        $this->assertCount(2, $result['items']);
        foreach($result['items'] as $event) {
            $this->assertContains($event->get_id(), [$future_event->get_id(), $past_event->get_id()]);
        }
    }

    public function test_filters_by_finishes_on_or_before() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $past_event = $facetoface_generator->create_session_for_course($course, -100);
        $future_event = $facetoface_generator->create_session_for_course($course, 200);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'finishes_on_or_before' => 0,
                    ]
                ]
            ]
        );

        $this->assertCount(0, $result['items']);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'finishes_on_or_before' => $past_event->get_maxtimefinish(),
                    ]
                ]
            ]
        );

        $this->assertEquals($past_event->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'finishes_on_or_before' => $future_event->get_maxtimefinish() + 100,
                    ]
                ]
            ]
        );

        $this->assertCount(2, $result['items']);
        foreach($result['items'] as $event) {
            $this->assertContains($event->get_id(), [$future_event->get_id(), $past_event->get_id()]);
        }
    }

    public function test_filters_by_min_seats_available() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $event_with_5_seats_available = $facetoface_generator->create_session_for_course($course);
        $amount_of_users_to_signup = 5;
        for ($users_signed_up = 0; $users_signed_up < $amount_of_users_to_signup; $users_signed_up++) {
            $user = $core_generator->create_user();
            $core_generator->enrol_user($user->id, $course->id);
            $facetoface_generator->create_signup($user, $event_with_5_seats_available);
        }

        $event_with_1_seat_available = $facetoface_generator->create_session_for_course($course);
        $amount_of_users_to_signup = 9;
        for ($users_signed_up = 0; $users_signed_up < $amount_of_users_to_signup; $users_signed_up++) {
            $user = $core_generator->create_user();
            $core_generator->enrol_user($user->id, $course->id);
            $facetoface_generator->create_signup($user, $event_with_1_seat_available);
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'min_seats_available' => 10,
                    ]
                ]
            ]
        );

        $this->assertCount(0, $result['items']);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'min_seats_available' => 5,
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($event_with_5_seats_available->get_id(), $result['items'][0]->get_id());


        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'min_seats_available' => 1,
                    ]
                ]
            ]
        );

        $this->assertCount(2, $result['items']);
        foreach($result['items'] as $event) {
            $this->assertContains($event->get_id(), [$event_with_5_seats_available->get_id(), $event_with_1_seat_available->get_id()]);
        }
    }

    public function test_filters_by_since_timemodified() {
        global $DB;

        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $event_1 = $facetoface_generator->create_session_for_course($course);
        $DB->update_record('facetoface_sessions',
            [
                'id' => $event_1->get_id(),
                'timemodified' => 100,
            ]
        );

        $event_2 = $facetoface_generator->create_session_for_course($course);
        $DB->update_record('facetoface_sessions',
            [
                'id' => $event_2->get_id(),
                'timemodified' => 1000,
            ]
        );

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'since_timemodified' => 1001,
                    ]
                ]
            ]
        );

        $this->assertCount(0, $result['items']);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'since_timemodified' => 1000,
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($event_2->get_id(), $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'since_timemodified' => 90,
                    ]
                ]
            ]
        );

        $this->assertCount(2, $result['items']);
        foreach($result['items'] as $event) {
            $this->assertContains($event->get_id(), [$event_1->get_id(), $event_2->get_id()]);
        }
    }

    public function test_sort_by_empty_string() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $facetoface_generator->create_session_for_course($course);
        $facetoface_generator->create_session_for_course($course);


        $no_sort_result = $this->resolve_graphql_query(static::QUERY);
        $empty_sort_result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => '',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(2, $no_sort_result['items']);
        $this->assertCount(2, $empty_sort_result['items']);

        $this->assertEqualsCanonicalizing($empty_sort_result['items'][0], $no_sort_result['items'][0]);
        $this->assertEqualsCanonicalizing($empty_sort_result['items'][1], $no_sort_result['items'][1]);
    }

    public function test_sort_by_id() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $events = [
            $facetoface_generator->create_session_for_course($course),
            $facetoface_generator->create_session_for_course($course),
            $facetoface_generator->create_session_for_course($course),
            $facetoface_generator->create_session_for_course($course),
            $facetoface_generator->create_session_for_course($course),
        ];

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'id',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(5, $result['items']);
        foreach($events as $index => $event) {
            $this->assertequals($event->get_id(), $result['items'][$index]->get_id());
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'id',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(5, $result['items']);
        foreach(array_reverse($events) as $index => $event) {
            $this->assertequals($event->get_id(), $result['items'][$index]->get_id());
        }
    }

    public function test_sort_by_timecreated() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $events = [];

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_timecreated(1)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_timecreated(2)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_timecreated(3)->save();
        $events[] = $event;

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'timecreated',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach($events as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'timecreated',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach(array_reverse($events) as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }
    }

    public function test_sort_by_timemodified() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $events = [];

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_timemodified(1)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_timemodified(2)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_timemodified(3)->save();
        $events[] = $event;

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'timemodified',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach($events as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'timemodified',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach(array_reverse($events) as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }
    }

    public function test_sort_by_seminar_id() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $events = [
            $facetoface_generator->create_session_for_course($course),
            $facetoface_generator->create_session_for_course($course),
            $facetoface_generator->create_session_for_course($course),
            $facetoface_generator->create_session_for_course($course),
            $facetoface_generator->create_session_for_course($course),
        ];

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'seminar_id',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(5, $result['items']);
        foreach($events as $index => $event) {
            $this->assertequals($event->get_id(), $result['items'][$index]->get_id());
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'seminar_id',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(5, $result['items']);
        foreach(array_reverse($events) as $index => $event) {
            $this->assertequals($event->get_id(), $result['items'][$index]->get_id());
        }
    }

    public function test_sort_by_cost_normal() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $events = [];

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_normalcost(1)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_normalcost(2)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_normalcost(3)->save();
        $events[] = $event;

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'cost_normal',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach($events as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'cost_normal',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach(array_reverse($events) as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }
    }

    public function test_sort_by_cost_discount() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $events = [];

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_discountcost(1)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_discountcost(2)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_discountcost(3)->save();
        $events[] = $event;

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'cost_discount',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach($events as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'cost_discount',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach(array_reverse($events) as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }
    }

    public function test_sort_by_registration_time_start() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $events = [];

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_registrationtimestart(1)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_registrationtimestart(2)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_registrationtimestart(3)->save();
        $events[] = $event;

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'registration_time_start',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach($events as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'registration_time_start',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach(array_reverse($events) as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }
    }

    public function test_sort_by_registration_time_end() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $events = [];

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_registrationtimefinish(1)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_registrationtimefinish(2)->save();
        $events[] = $event;

        $event = $facetoface_generator->create_session_for_course($course);
        $event->set_registrationtimefinish(3)->save();
        $events[] = $event;

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'registration_time_end',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach($events as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'sort' => [
                        [
                            'column' => 'registration_time_end',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);

        foreach(array_reverse($events) as $index => $event) {
            $this->assertEquals($event->get_id(), $result['items'][$index]->get_id());
        }
    }

    public function test_custom_fields_are_included_in_execution_context() {
        global $DB;
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        
        $room_1 = $facetoface_generator->add_site_wide_room([]);
        $room_1_custom_fields = $custom_field_generator->create_text('facetoface_room', ['room_1_text_one', 'room_1_text_two']);
        $custom_field_generator->set_text($room_1, $room_1_custom_fields['room_1_text_one'], 'text', 'facetofaceroom', 'facetoface_room');
        
        $event_1_id = $facetoface_generator->add_session(['facetoface' => $facetoface->id, 'sessiondates' => [
            (object) [
                'roomids' => [$room_1->id],
                'timestart' => 0,
                'timefinish' => 1,
            ]
        ]]);
        $event_1 = $DB->get_record('facetoface_sessions', ['id' => $event_1_id]);
        $event_1_custom_fields = $custom_field_generator->create_text('facetoface_session', ['event_1_text_one', 'event_1_text_two']);
        $custom_field_generator->set_text($event_1, $event_1_custom_fields['event_1_text_one'], 'text', 'facetofacesession', 'facetoface_session');

        $room_2 = $facetoface_generator->add_site_wide_room([]);
        $room_2_custom_fields = $custom_field_generator->create_text('facetoface_room', ['room_2_text_one', 'room_2_text_two']);
        $custom_field_generator->set_text($room_2, $room_2_custom_fields['room_2_text_one'], 'text', 'facetofaceroom', 'facetoface_room');

        $event_2_id = $facetoface_generator->add_session(['facetoface' => $facetoface->id, 'sessiondates' => [
            (object) [
                'roomids' => [$room_2->id],
                'timestart' => 0,
                'timefinish' => 2,
            ]
        ]]);
        $event_2 = $DB->get_record('facetoface_sessions', ['id' => $event_2_id]);
        $event_2_custom_fields = $custom_field_generator->create_text('facetoface_session', ['event_2_text_one', 'event_2_text_two']);
        $custom_field_generator->set_text($event_2, $event_2_custom_fields['event_2_text_one'], 'text', 'facetofacesession', 'facetoface_session');

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'query' => [
                    'filters' => [
                        'course_id' => $course->id,
                    ]
                ]
            ]
        );
        $this->assertEquals(2, $result['total']);
        $this->assertCount(2, $this->execution_context->get_variable('custom_fields_facetofacesession'));
        $this->assertCount(2, $this->execution_context->get_variable('custom_fields_facetofaceroom'));
        $this->assertNotEmpty($this->execution_context->get_variable('context'));
    }
}
