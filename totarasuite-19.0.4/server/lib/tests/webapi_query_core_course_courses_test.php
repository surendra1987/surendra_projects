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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package core
 */

use core\collection;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \core\webapi\resolver\query\course_courses
 *
 * @group core_course
 */
class core_webapi_query_core_course_courses_test extends testcase {

    private const QUERY = 'core_course_courses';
    private const EMPTY_QUERY_ARGS = ['query' => []];

    use webapi_phpunit_helper;

    /**
     * Basic functionality test for the happy path when querying course_courses
     * @covers ::resolve
     */
    public function test_query_course_courses(): void {
        $this->create_api_user();
        $courses = $this->create_courses();

        $result = $this->resolve_graphql_query(self::QUERY, self::EMPTY_QUERY_ARGS);

        self::assertEquals(count($result['items']), $courses->count());
        foreach ($result['items'] as $item) {
            self::assertTrue($courses->has('idnumber', $item->idnumber));
        }
    }

    /**
     * A tenant API user cannot see courses that belong to a different tenancy
     * @covers ::resolve
     */
    public function test_tenant_user_can_only_see_own_tenant_courses(): void {
        $gen = self::getDataGenerator();

        // A tenant user does not have permission to view system-level courses
        $system_course = $gen->create_course();

        /** @var \totara_tenant\testing\generator $tenancy_generator */
        $tenancy_generator = $gen->get_plugin_generator('totara_tenant');
        $tenancy_generator->enable_tenants();

        $first_tenancy = $tenancy_generator->create_tenant();
        $first_tenancy_course = $gen->create_course(['category' => $first_tenancy->categoryid]);
        $first_tenancy_hidden_course = $gen->create_course(['category' => $first_tenancy->categoryid, 'visible' => 0]);

        $second_tenancy = $tenancy_generator->create_tenant();
        $second_tenancy_course = $gen->create_course(['category' => $second_tenancy->categoryid]);

        $tenancy_manager = $gen->create_user(
            ['tenantid' => $first_tenancy->id, 'tenantdomainmanager' => $first_tenancy->idnumber]
        );
        $this->create_api_user($tenancy_manager);

        $result = $this->resolve_graphql_query(self::QUERY, self::EMPTY_QUERY_ARGS);
        self::assertEquals(2, $result['total']);

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertTrue(in_array($first_tenancy_course->id, $ids, true));
        self::assertTrue(in_array($first_tenancy_hidden_course->id, $ids, true));
        self::assertFalse(in_array($system_course->id, $ids, true));
        self::assertFalse(in_array($second_tenancy_course->id, $ids, true));
    }

    /**
     * An API user with appropriate permission should be able to see all courses, including hidden ones
     * @covers ::resolve
     */
    public function test_can_view_hidden_courses(): void {
        $this->create_api_user();
        $course = $this->getDataGenerator()->create_course(['visible' => 0]);
        $result = $this->resolve_graphql_query(self::QUERY, self::EMPTY_QUERY_ARGS);
        self::assertEquals(1, count($result['items']));
        self::assertEquals($course->idnumber, $result['items'][0]->idnumber);
    }

    /**
     * A user without the correct permissions cannot view a hidden course
     * @covers ::resolve
     */
    public function test_cannot_view_hidden_courses_without_permission(): void {
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $capability = 'moodle/course:view';
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        role_change_permission($roleid, $context, $capability, CAP_ALLOW);
        self::setUser($user);

        $this->getDataGenerator()->create_course(['visible' => 0]);
        $result = $this->resolve_graphql_query(self::QUERY, self::EMPTY_QUERY_ARGS);
        self::assertEquals(0, count($result['items']));
    }


    /**
     * @param int $count
     * @return collection
     */
    private function create_courses(int $count = 10): collection {
        $courses = [];
        foreach (range(0, $count - 1) as $i) {
            $fullname = sprintf('Test course #%02d', $i);
            $shortname = sprintf('testcourse%02d', $i);

            $courses[] = self::getDataGenerator()->create_course([
                'shortname' => $shortname,
                'fullname' => $fullname
            ]);
        }

        return collection::new($courses);
    }

    /**
     * Create an API user with permission to view courses and hidden courses
     * @return stdClass
     */
    private function create_api_user($premade_user = null): stdClass {
        $user = $premade_user;
        if (!$premade_user) {
            $gen = $this->getDataGenerator();
            $user = $gen->create_user();
        }

        $capability = 'moodle/course:view';
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        role_change_permission($roleid, $context, $capability, CAP_ALLOW);

        $capability = 'moodle/course:viewhiddencourses';
        role_change_permission($roleid, $context, $capability, CAP_ALLOW);
        role_assign($roleid, $user->id, $context);

        self::setUser($user);

        return $user;
    }

    /**
     * @return void
     */
    public function test_with_time_created_filter(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $course1 = $gen->create_course(['timecreated' =>  $time + 10]);
        $course2 = $gen->create_course(['timecreated' =>  $time + 5]);
        $course3 = $gen->create_course(['timecreated' =>  $time - 10]);
        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'query' => [
                    'filters' => [
                        'since_timecreated' => $time,
                    ]
                ]
            ]
        );

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertEquals(2, $result['total']);
        self::assertTrue(in_array($course1->id, $ids, true));
        self::assertTrue(in_array($course2->id, $ids, true));
        self::assertFalse(in_array($course3->id, $ids, true));
    }

    /**
     * @return void
     */
    public function test_with_time_modified_filter(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $course1 = $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $course2 = $gen->create_course(['timecreated' =>  $time + 5, 'timemodified' => $time + 5]);
        $course3 = $gen->create_course(['timecreated' =>  $time - 10]);
        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'query' => [
                    'filters' => [
                        'since_timecreated' => $time,
                        'since_timemodified' => $time,
                    ]
                ]
            ]
        );

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertEquals(2, $result['total']);
        self::assertTrue(in_array($course1->id, $ids, true));
        self::assertTrue(in_array($course2->id, $ids, true));
        self::assertFalse(in_array($course3->id, $ids, true));
    }

    /**
     * @return void
     */
    public function test_with_tag_filter(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $course1 = $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $course2 = $gen->create_course(['timecreated' =>  $time - 5]);
        $course3 = $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 2]);

        core_tag_tag::set_item_tags('core', 'course', $course1->id, context_course::instance($course1->id), ['A random tag']);
        core_tag_tag::set_item_tags('core', 'course', $course3->id, context_course::instance($course3->id), ['A random tag']);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'filters' => [
                    'since_timecreated' => $time,
                    'since_timemodified' => $time,
                    'tag' => 'A random tag'
                ]
            ]
        ]);

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertEquals(2, $result['total']);
        self::assertTrue(in_array($course1->id, $ids, true));
        self::assertFalse(in_array($course2->id, $ids, true));
        self::assertTrue(in_array($course3->id, $ids, true));
    }

    /**
     * @return void
     */
    public function test_custom_fields_added_to_execution_context(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $course1 = $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $course2 = $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $course3 = $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $course4 = $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);

        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('course', ['text_one', 'text_two']);
        $custom_field_generator->set_text($course1, $custom_fields['text_one'], 'course_1_text', 'course', 'course');
        $custom_field_generator->set_text($course2, $custom_fields['text_two'], 'course_2_text', 'course', 'course');
        $custom_field_generator->set_text($course3, $custom_fields['text_one'], 'course_3_text', 'course', 'course');
        $custom_field_generator->set_text($course3, $custom_fields['text_two'], 'course_3_text', 'course', 'course');

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => [
            ]
        ]);

        $course_custom_fields = $this->execution_context->get_variable('custom_fields');
        $this->assertCount(4, $course_custom_fields);
        // ensure each course has 2 custom fields each - regardless of a value overriding or not
        foreach ($course_custom_fields as $course_custom_field) {
            $this->assertCount(2, $course_custom_field);
        }
    }

    /**
     * @return void
     */
    public function test_custom_fields_not_added_to_execution_context(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $gen->create_course(['timecreated' =>  $time + 1, 'timemodified' => $time + 10]);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => [
            ]
        ]);

        $course_custom_fields = $this->execution_context->get_variable('custom_fields');
        $this->assertIsArray($course_custom_fields);
        $this->assertEmpty($course_custom_fields);
    }
}