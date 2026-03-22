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
 * @author Mark Metcalfe <mark.metcalfe@totara.com>
 * @package mod_facetoface
 */

use core\exception\unresolved_record_reference;
use core_phpunit\testcase;
use mod_facetoface\seminar_event_list;
use totara_webapi\client_aware_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_query_seminars_in_course_test extends testcase {
    use webapi_phpunit_helper;

    private const QUERY = "mod_facetoface_seminars_in_course";

    public function test_with_no_seminars(): void {
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $result = $this->resolve_graphql_query(static::QUERY, ['course' => ['id' => $course->id]]);

        $this->assertCount(0, $result['items']);
        $this->assertEquals(0, $result['total']);
    }

    public function test_with_one_seminar(): void {
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        $result = $this->resolve_graphql_query(static::QUERY, ['course' => ['id' => $course->id]]);

        $this->assertCount(1, $result['items']);
        $this->assertEquals(1, $result['total']);

        $resolved_id = $this->resolve_graphql_type('mod_facetoface_seminar', 'id', $result['items'][0], [], null, $this->execution_context);
        $this->assertEquals($facetoface->id, $resolved_id);
    }

    public function test_with_multiple_seminars(): void {
        $this->setAdminUser();

        $course1 = $this->getDataGenerator()->create_course();
        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course1->id]);
        $facetoface2 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course1->id]);

        $course2 = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('facetoface', ['course' => $course2->id]);

        $result = $this->resolve_graphql_query(static::QUERY, ['course' => ['id' => $course1->id]]);

        $this->assertCount(2, $result['items']);
        $this->assertEquals(2, $result['total']);

        $this->assertEquals($facetoface1->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface2->id, $result['items'][1]->get_id());
    }

    public function test_with_returned_event_and_session_fields(): void {
        $this->setAdminUser();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $course = $this->getDataGenerator()->create_course();
        $facetoface = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        // create event and session inside seminar
        $room = $facetoface_generator->add_site_wide_room([]);
        $sessiondate = new stdClass();
        $sessiondate->timestart = time() + DAYSECS;
        $sessiondate->timefinish = $sessiondate->timestart + (DAYSECS * 2);
        $sessiondate->sessiontimezone = 'Pacific/Auckland';
        $sessiondate->roomids = [$room->id];
        $event_id = $facetoface_generator->add_session(['facetoface' => $facetoface->id, 'sessiondates' => [$sessiondate]]);

        $result = $this->resolve_graphql_query(static::QUERY, ['course' => ['id' => $course->id]]);

        $this->assertCount(1, $result['items']);
        $this->assertEquals(1, $result['total']);

        $the_facetoface = $result['items'][0];

        /** @var seminar_event_list $resolved_events */
        $resolved_events = $this->resolve_graphql_type('mod_facetoface_seminar', 'events', $the_facetoface, [], null, $this->execution_context);
        $the_event = $resolved_events->to_array(false)[0];

        $resolved_event_id = $this->resolve_graphql_type('mod_facetoface_event', 'id', $the_event, [], null, $this->execution_context);
        $this->assertEquals($event_id, $resolved_event_id);

        $resolved_sessions = $this->resolve_graphql_type('mod_facetoface_event', 'sessions', $the_event, [], null, $this->execution_context);
        $the_session = reset($resolved_sessions);
        $session_id = $the_session->get_id();

        $resolved_session_id = $this->resolve_graphql_type('mod_facetoface_session', 'id', $the_session, [], null, $this->execution_context);
        $this->assertEquals($session_id, $resolved_session_id);
    }

    public function test_when_not_logged_in(): void {
        $course = $this->getDataGenerator()->create_course();

        $this->expectException(require_login_exception::class);
        $this->expectExceptionMessage("Course or activity not accessible. (You are not logged in)");

        $this->resolve_graphql_query(static::QUERY, ['course' => ['id' => $course->id]]);
    }

    public function test_with_invalid_course(): void {
        $this->expectException(unresolved_record_reference::class);
        $this->expectExceptionMessage("Course reference not found");

        $this->resolve_graphql_query(static::QUERY, ['course' => ['id' => -1]]);
    }

    public function test_permissions(): void {
        global $DB;
        $view_capability = 'mod/facetoface:view';
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $role = $DB->get_record('role', ['shortname' => 'apiuser']);
        $course = $this->getDataGenerator()->create_course();
        role_assign($role->id, $user->id, context_course::instance($course->id));

        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);
        $facetoface2 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);
        $facetoface3 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        // By default the apiuser role has the view capability on all facetoface modules, so override that for the first module.
        $cm_1 = get_coursemodule_from_instance('facetoface', $facetoface1->id, $course->id);
        assign_capability($view_capability, CAP_PROHIBIT, $role->id, context_module::instance($cm_1->id));

        $result = $this->resolve_graphql_query(static::QUERY, ['course' => ['id' => $course->id]]);

        $this->assertCount(2, $result['items']);
        $this->assertEquals(2, $result['total']);

        $this->assertEquals($facetoface2->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface3->id, $result['items'][1]->get_id());

        // Ensure that even if the ID is in the list of IDs, it is not returned if the user does not have permission to view it.
        $result = $this->resolve_graphql_query(static::QUERY, [
            'course' => ['id' => $course->id],
            'query' => [
                'filters' => [
                    'ids' => [$facetoface1->id, $facetoface2->id],
                ]
            ]
        ]);
        $this->assertCount(1, $result['items']);
        $this->assertEquals(1, $result['total']);

        $this->assertEquals($facetoface2->id, $result['items'][0]->get_id());
    }

    public function test_multiple_sort_columns_throws_exception(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage("Sorting by more than one column is not currently supported");

        $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
                'query' => [
                    'sort' => [
                        [
                            'column' => 'shortname',
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

    public function test_filter_by_ids(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);
        $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);
        $facetoface3 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        $result = $this->resolve_graphql_query(static::QUERY, [
            'course' => ['id' => $course->id],
            'query' => [
                'filters' => [
                    'ids' => [$facetoface1->id, $facetoface3->id],
                ]
            ]
        ]);
        $this->assertCount(2, $result['items']);
        $this->assertEquals(2, $result['total']);
        $this->assertEquals($facetoface1->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface3->id, $result['items'][1]->get_id());
    }

    public function test_filters_by_since_timemodified(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'timemodified' => 100]);
        $facetoface2 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'timemodified' => 1000]);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
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
                'course' => ['id' => $course->id],
                'query' => [
                    'filters' => [
                        'since_timemodified' => 1000,
                    ]
                ]
            ]
        );

        $this->assertCount(1, $result['items']);
        $this->assertEquals($facetoface2->id, $result['items'][0]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
                'query' => [
                    'filters' => [
                        'since_timemodified' => 90,
                    ]
                ]
            ]
        );

        $this->assertCount(2, $result['items']);
        $this->assertEquals($facetoface1->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface2->id, $result['items'][1]->get_id());
    }

    public function test_sort_by_empty_string(): void {
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);
        $facetoface2 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
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

        $this->assertCount(2, $result['items']);
        $this->assertEqualsCanonicalizing($facetoface1->id, $result['items'][0]->get_id());
        $this->assertEqualsCanonicalizing($facetoface2->id, $result['items'][1]->get_id());
    }

    public function test_sort_by_id(): void {
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);
        $facetoface2 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);
        $facetoface3 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
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

        $this->assertCount(3, $result['items']);
        $this->assertequals($facetoface1->id, $result['items'][0]->get_id());
        $this->assertequals($facetoface2->id, $result['items'][1]->get_id());
        $this->assertequals($facetoface3->id, $result['items'][2]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
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

        $this->assertCount(3, $result['items']);
        $this->assertequals($facetoface3->id, $result['items'][0]->get_id());
        $this->assertequals($facetoface2->id, $result['items'][1]->get_id());
        $this->assertequals($facetoface1->id, $result['items'][2]->get_id());
    }

    public function test_sort_by_timecreated(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'timecreated' => 3]);
        $facetoface2 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'timecreated' => 2]);
        $facetoface3 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'timecreated' => 1]);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
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
        $this->assertEquals($facetoface3->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface2->id, $result['items'][1]->get_id());
        $this->assertEquals($facetoface1->id, $result['items'][2]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
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
        $this->assertEquals($facetoface1->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface2->id, $result['items'][1]->get_id());
        $this->assertEquals($facetoface3->id, $result['items'][2]->get_id());
    }

    public function test_sort_by_timemodified(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'timemodified' => 3]);
        $facetoface2 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'timemodified' => 2]);
        $facetoface3 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'timemodified' => 1]);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
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
        $this->assertEquals($facetoface3->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface2->id, $result['items'][1]->get_id());
        $this->assertEquals($facetoface1->id, $result['items'][2]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
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
        $this->assertEquals($facetoface1->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface2->id, $result['items'][1]->get_id());
        $this->assertEquals($facetoface3->id, $result['items'][2]->get_id());
    }

    public function test_sort_by_shortname(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $facetoface1 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'shortname' => 'C']);
        $facetoface2 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'shortname' => 'B']);
        $facetoface3 = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id, 'shortname' => 'A']);

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
                'query' => [
                    'sort' => [
                        [
                            'column' => 'shortname',
                            'direction' => 'asc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);
        $this->assertEquals($facetoface3->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface2->id, $result['items'][1]->get_id());
        $this->assertEquals($facetoface1->id, $result['items'][2]->get_id());

        $result = $this->resolve_graphql_query(
            static::QUERY,
            [
                'course' => ['id' => $course->id],
                'query' => [
                    'sort' => [
                        [
                            'column' => 'shortname',
                            'direction' => 'desc',
                        ]
                    ]
                ]
            ]
        );

        $this->assertCount(3, $result['items']);
        $this->assertEquals($facetoface1->id, $result['items'][0]->get_id());
        $this->assertEquals($facetoface2->id, $result['items'][1]->get_id());
        $this->assertEquals($facetoface3->id, $result['items'][2]->get_id());
    }

}
