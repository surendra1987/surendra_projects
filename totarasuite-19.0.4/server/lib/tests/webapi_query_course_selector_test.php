<?php
/**
 * This file is part of Totara Learn
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
 * @author David Curry <david.curry@totaralearning.com>
 * @package core
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \core\webapi\resolver\query\course_selector
 *
 * @group core_user
 */
class core_webapi_query_course_selector_test extends testcase {

    private const QUERY = 'core_course_selector';

    use webapi_phpunit_helper;

    /**
     * Create some category data for testing.
     * This will recursively generate 1 top level parent category, with 2 children. Each with 10 searchable courses.
     *
     * @param string $cat_name - The name of the parent category (will be used for child categories)
     * @param int    $parent_id - For the recursive calls only
     * @return array
     */
    private function faux_category_data($cat_name, $parent_id = 0, $completion = true): array {
        $category = $this->getDataGenerator()->create_category([
            'name' => $cat_name,
            'parent' => $parent_id
        ]);

        // Note: to end up sorting alpha->omega I had to reverse the order. Never courses get a higher sortorder.
        $course_info = [
            'omega' => 'Owesome',
            'gamma three' => 'Course Grape Three',
            'gamma two' => 'Course Grape Two',
            'gamma one' => 'Course Grape One',
            'beta three' => 'Course Banana Three',
            'beta two' => 'Course Banana Two',
            'beta one' => 'Course Banana One',
            'alpha three' => 'Course Apple Three',
            'alpha two' => 'Course Apple two',
            'alpha one' => 'Course Apple One',
        ];

        $courses = [];
        foreach ($course_info as $shortname => $fullname) {
            $courses[] = $this->getDataGenerator()->create_course([
                'category' => $category->id,
                'shortname' => $shortname . ' ' . $cat_name,
                'fullname' => $fullname . ' ' . $cat_name,
                'enablecompletion' => $completion
            ]);
        }

        $children = [];
        if (empty($parent_id)) {
            // Now a couple of subcategories for good measure.
            $children[1] = $this->faux_category_data($cat_name . '_1', $category->id, $completion);
            $children[2] = $this->faux_category_data($cat_name . '_2', $category->id, $completion);
        }

        return ['category' => $category, 'courses' => $courses, 'children' => $children];
    }

    /**
     * Test the functionality of the course selector graphql without login.
     */
    public function test_webapi_query_course_selector_nologin() {
        $this->expectException(\require_login_exception::class);
        $this->expectExceptionMessage('Course or activity not accessible. (You are not logged in)');


        $items = $this->resolve_graphql_query(self::QUERY, []);
    }

    /**
     * Test the functionality of the course selector graphql with no courses.
     */
    public function test_webapi_query_course_selector_empty() {
        $this->setAdminUser();

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'pagination' => [
                    ]
                ]
            ]
        );
        self::assertSame(1, $result['page']);
        self::assertSame(0, $result['total']);
        self::assertEmpty($result['courses']);
    }

    /**
     * Test the default "return all" functionality of the course selector graphql
     */
    public function test_webapi_query_course_selector_basic() {
        $this->setAdminUser();

        $cat1 = $this->faux_category_data('Cat1');

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );
        self::assertSame(1, $result['page']);
        self::assertSame(30, $result['total']);
        self::assertSame("20", $result['next_cursor']);
        self::assertCount(20, $result['courses']);

        $index = 0;
        foreach ($result['courses'] as $item) {
            if ($index++ < 10) {
                // These should be all the items from cat1.
                $expected = $cat1['category']->name;
            } else {
                // Then all the items from cat1_1.
                $expected = $cat1['children'][1]['category']->name;
            }

            $spliced = explode(' ', $item->fullname);
            self::assertSame($expected, array_pop($spliced));
        }

        // Get the next page of items.
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'pagination' => [
                        'limit' => 20,
                        'page' => $result['page'],
                        'next_cursor' => $result['next_cursor']
                    ]
                ]
            ]
        );
        self::assertCount(10, $result['courses']);

        // These should be all the items from cat1_2.
        foreach ($result['courses'] as $item) {
            $expected = $cat1['children'][2]['category']->name;
            $spliced = explode(' ', $item->fullname);
            self::assertSame($expected, array_pop($spliced));
        }
    }

    /**
     * Test the search functionality of the course selector graphql on fullname
     */
    public function test_webapi_query_course_selector_search_fullname() {
        $this->setAdminUser();

        $cat1 = $this->faux_category_data('Cat1');
        $cat2 = $this->faux_category_data('Cat2');

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'filters' => [
                        'search' => 'apple'
                    ],
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );
        self::assertSame(1, $result['page']);
        self::assertSame(18, $result['total']);
        self::assertSame("20", $result['next_cursor']);
        self::assertCount(18, $result['courses']);

        // Just double check all the items are alpha.
        foreach ($result['courses'] as $item) {
            $fullname = explode(' ', $item->fullname);
            self::assertSame('Apple', $fullname[1]);
        }
    }

    /**
     * Test the search functionality of the course selector graphql
     */
    public function test_webapi_query_course_selector_category_filter() {
        $this->setAdminUser();

        $cats = [];
        $cats[] = $this->faux_category_data('Cat1');
        $cats[] = $this->faux_category_data('Cat2');

        // Run both categories and check they only see their own records.
        foreach ($cats as $cat) {
            $result = $this->resolve_graphql_query(
                self::QUERY,
                [
                    'input' => [
                        'filters' => [
                            'category_id' => $cat['category']->id
                        ],
                        'pagination' => [
                            'limit' => 20,
                            'page' => 0,
                            'next_cursor' => ''
                        ]
                    ]
                ]
            );
            self::assertSame(1, $result['page']);
            self::assertSame(30, $result['total']);
            self::assertSame("20", $result['next_cursor']);
            self::assertCount(20, $result['courses']);

            $index = 0;
            foreach ($result['courses'] as $item) {
                if ($index++ < 10) {
                    // These should be all the items from the parent cat.
                    $expected = $cat['category']->name;
                } else {
                    // Then all the items from subcat_1.
                    $expected = $cat['children'][1]['category']->name;
                }

                $spliced = explode(' ', $item->fullname);
                self::assertSame($expected, array_pop($spliced));
            }

            // Get the next page of items.
            $result = $this->resolve_graphql_query(
                self::QUERY,
                [
                    'input' => [
                        'filters' => [
                            'category_id' => $cat['category']->id
                        ],
                        'pagination' => [
                            'limit' => 20,
                            'page' => $result['page'],
                            'next_cursor' => $result['next_cursor']
                        ]
                    ]
                ]
            );
            self::assertCount(10, $result['courses']);

            // These should be all the items from subcat_2.
            foreach ($result['courses'] as $item) {
                $expected = $cat['children'][2]['category']->name;
                $spliced = explode(' ', $item->fullname);
                self::assertSame($expected, array_pop($spliced));
            }
        }
    }

    public function test_webapi_query_course_selector_completion_filter() {
        global $DB;

        $this->setAdminUser();

        // A little bit of set up.
        $misc = $DB->get_record('course_categories', ['name' => 'Miscellaneous']);
        $enabled = $this->faux_category_data('Cat1', $misc->id, true);
        $disabled = $this->faux_category_data('Cat2', $misc->id, false);

        // Test that completion_tracked => true works as expected.
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'filters' => [
                        'completion_tracked' => true
                    ],
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );
        self::assertSame(1, $result['page']);
        self::assertSame(10, $result['total']);
        self::assertSame("20", $result['next_cursor']);
        self::assertCount(10, $result['courses']);

        foreach ($result['courses'] as $course) {
            self::assertSame($enabled['category']->id, $course->category);
            self::assertEquals(COMPLETION_ENABLED, $course->enablecompletion);
        }

        // Now test that completion_tracked => false works as expected, by returning both cats.
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'filters' => [
                        'completion_tracked' => false
                    ],
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );
        self::assertSame(1, $result['page']);
        self::assertSame(10, $result['total']);
        self::assertSame("20", $result['next_cursor']);
        self::assertCount(10, $result['courses']);

        foreach ($result['courses'] as $course) {
            self::assertSame($disabled['category']->id, $course->category);
            self::assertEquals(COMPLETION_DISABLED, $course->enablecompletion);
        }

        // Now test that completion_tracked => null works as expected, by returning both cats.
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'filters' => [
                    ],
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );
        self::assertSame(1, $result['page']);
        self::assertSame(20, $result['total']);
        self::assertSame("20", $result['next_cursor']);
        self::assertCount(20, $result['courses']);
    }

    /**
     * Test a search with a category selected
     */
    public function test_webapi_query_course_selector_combined_category_search() {
        $this->setAdminUser();

        $cats = [];
        $cats[] = $this->faux_category_data('Cat1');
        $cats[] = $this->faux_category_data('Cat2');

        // Run both categories and check they only see their own records.
        foreach ($cats as $cat) {
            // Add a subcat full of non completion courses to make sure they don't show up.
            $cat_id = $cat['category']->id;
            $this->faux_category_data('noComp' . $cat_id, $cat_id, false);

            $result = $this->resolve_graphql_query(
                self::QUERY,
                [
                    'input' => [
                        'filters' => [
                            'search' => 'apple',
                            'category_id' => $cat_id,
                            'completion_tracked' => true
                        ],
                        'pagination' => [
                            'limit' => 20,
                            'page' => 0,
                            'next_cursor' => ''
                        ]
                    ]
                ]
            );
            self::assertSame(1, $result['page']);
            self::assertSame(9, $result['total']);
            self::assertSame("20", $result['next_cursor']);
            self::assertCount(9, $result['courses']);

            $index = 0;
            foreach ($result['courses'] as $item) {
                if ($index < 3) {
                    // The first 3 items are the parent category.
                    $expected = $cat['category']->name;
                } else if ($index < 6) {
                    // The next 3 are from the first child category.
                    $expected = $cat['children'][1]['category']->name;
                } else if ($index < 9) {
                    // The final three should be from the last child category.
                    $expected = $cat['children'][2]['category']->name;
                } else {
                    self::fail('Unexpected index found');
                }

                $index++;
                $spliced = explode(' ', $item->fullname);
                self::assertSame($expected, array_pop($spliced));
            }
        }
    }

    /**
     * @return void
     */
    public function test_webapi_query_course_selector_with_tenantid_filter(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
        ] = $this->create_tenant_data();

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'filters' => [
                        'tenant_id' => $tenant1->id
                    ],
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );
        self::assertSame(3, $result['total']);
        $course_ids = array_map(
            function ($course) {
                return $course->id;
            },
            $result['courses']
        );

        self::assertTrue(in_array($course_t1->id, $course_ids));
        self::assertTrue(in_array($course_system->id, $course_ids));
        self::assertTrue(in_array($course_t1_child->id, $course_ids));

        // Tenant 2
        self::assertFalse(in_array($course_t2->id, $course_ids));

        // Turn isolation on
        set_config('tenantsisolated', 1);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'filters' => [
                        'tenant_id' => $tenant1->id
                    ],
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );

        self::assertSame(2, $result['total']);
        $course_ids = array_map(
            function ($course) {
                return $course->id;
            },
            $result['courses']
        );

        self::assertTrue(in_array($course_t1->id, $course_ids));
        self::assertTrue(in_array($course_t1_child->id, $course_ids));

        // Tenant2 and system category
        self::assertFalse(in_array($course_t2->id, $course_ids));
        self::assertFalse(in_array($course_system->id, $course_ids));

        // Mock system category.
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'filters' => [
                        'tenant_id' => null
                    ],
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );

        self::assertSame(1, $result['total']);
        $course_ids = array_map(
            function ($course) {
                return $course->id;
            },
            $result['courses']
        );

        self::assertTrue(in_array($course_system->id, $course_ids));

        // Tenant 2 and tenant 1
        self::assertFalse(in_array($course_t2->id, $course_ids));
        self::assertFalse(in_array($course_t1->id, $course_ids));
        self::assertFalse(in_array($course_t1_child->id, $course_ids));
    }

    public function test_query_with_tenant_member_and_isolation_on_tenant1(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
        ] = $this->create_tenant_data(true);

        static::setUser($user_t1);

        $this->assert_courses_in_result([$course_t1->id, $course_t1_child->id]);
    }

    public function test_query_with_tenant_member_and_isolation_on_tenant2(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
        ] = $this->create_tenant_data(true);

        static::setUser($user_t2);

        $this->assert_courses_in_result([$course_t2->id]);
    }

    public function test_query_with_system_user_and_isolation_on(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
        ] = $this->create_tenant_data(true);

        static::setUser($user_system);

        // System user can see all courses regardless of isolation.
        $this->assert_courses_in_result([$course_t1->id, $course_t1_child->id, $course_t2->id, $course_system->id]);
    }

    public function test_query_with_tenant_member_and_isolation_off_tenant1(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
        ] = $this->create_tenant_data();

        static::setUser($user_t1);

        $this->assert_courses_in_result([$course_t1->id, $course_t1_child->id, $course_system->id]);
    }

    public function test_query_with_tenant_member_and_isolation_off_tenant2(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
        ] = $this->create_tenant_data();

        static::setUser($user_t2);

        $this->assert_courses_in_result([$course_t2->id, $course_system->id]);
    }


    public function test_query_with_system_user_and_isolation_off(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
            $user_t1_participant,
        ] = $this->create_tenant_data();

        static::setUser($user_system);

        // System user can see all courses regardless of isolation.
        $this->assert_courses_in_result([$course_t1->id, $course_t1_child->id, $course_t2->id, $course_system->id]);
    }

    public function test_query_with_tenant_participant_and_isolation_on(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
            $user_t1_participant,
        ] = $this->create_tenant_data(true);

        static::setUser($user_t1_participant);

        // Participant is basically a system user, so can see all courses regardless of isolation.
        $this->assert_courses_in_result([$course_t1->id, $course_t1_child->id, $course_t2->id, $course_system->id]);
    }

    public function test_query_with_tenant_participant_and_isolation_off(): void {
        [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
            $user_t1_participant,
        ] = $this->create_tenant_data();

        static::setUser($user_t1_participant);

        // Participant is basically a system user, so can see all courses regardless of isolation.
        $this->assert_courses_in_result([$course_t1->id, $course_t1_child->id, $course_t2->id, $course_system->id]);
    }

    private function assert_courses_in_result(array $expected_course_ids): void {
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'filters' => [
                    ],
                    'pagination' => [
                        'limit' => 20,
                        'page' => 0,
                        'next_cursor' => ''
                    ]
                ]
            ]
        );
        static::assertSame(count($expected_course_ids), $result['total']);
        $actual_course_ids = array_column($result['courses'], 'id');
        static::assertEqualsCanonicalizing($expected_course_ids, $actual_course_ids);
    }

    private function create_tenant_data(bool $enable_tenant_isolation = false): array {
        static::setAdminUser();

        $gen = static::getDataGenerator();
        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        $course_t1 = $gen->create_course(['category' => $tenant1->categoryid]);
        $course_t2 = $gen->create_course(['category' => $tenant2->categoryid]);
        $course_system = $gen->create_course();

        $child_t1 = $gen->create_category(['parent' => $tenant1->categoryid]);
        $course_t1_child = $gen->create_course(['category' => $child_t1->id]);

        if ($enable_tenant_isolation) {
            $tenant_generator->enable_tenant_isolation();
        }

        $user_t1 = $gen->create_user();
        $user_t2 = $gen->create_user();
        $user_system = $gen->create_user();
        $tenant_generator->migrate_user_to_tenant($user_t1->id, $tenant1->id);
        $tenant_generator->migrate_user_to_tenant($user_t2->id, $tenant2->id);

        $user_t1_participant = $gen->create_user();
        $tenant_generator->set_user_participation($user_t1_participant->id, [$tenant1->id]);

        return [
            $tenant1,
            $tenant2,
            $course_t1,
            $course_t2,
            $course_t1_child,
            $course_system,
            $user_t1,
            $user_t2,
            $user_system,
            $user_t1_participant
        ];
    }
}
