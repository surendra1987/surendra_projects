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
 * @package core
 */

use core\collection;
use core_phpunit\testcase;
use totara_cohort\exception\cohort_static_cohorts_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;
use core\entity\cohort as cohort_entity;

class core_webapi_query_cohort_static_cohorts_test extends testcase {

    private const QUERY = 'core_cohort_static_cohorts';

    use webapi_phpunit_helper;

    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');
    }

    /**
     * @covers ::resolve
     */
    public function test_query_cohort_static_cohorts(): void {
        self::setAdminUser();

        $collection = $this->create_cohorts()->filter(function ($item) {
            return $item->cohorttype == cohort::TYPE_STATIC;
        });

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        self::assertEquals(count($result['items']), $collection->count());
        foreach ($result['items'] as $item) {
            self::assertTrue($collection->has('idnumber', $item->idnumber));
        }
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_child_categories_under_same_tenancy(): void {
        $gen = self::getDataGenerator();
        /** @var \totara_cohort\testing\generator $generator */
        $generator = $gen->get_plugin_generator('totara_cohort');

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();

        // tennant manager.
        $user1 = $gen->create_user(['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]);
        $tenant1_category_context = context_coursecat::instance($tenant1->categoryid);

        $child_category = $gen->create_category(['parent' => $tenant1->categoryid]);
        $child_category_context = context_coursecat::instance($child_category->id);
        // Create 1 cohorts in tenant1
        $cohort1 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);
        $cohort2 = $generator->create_cohort(['contextid' => $child_category_context->id]);

        self::setUser($user1);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        self::assertEquals(2, $result['total']);

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertTrue(in_array($cohort1->id, $ids, true));
        self::assertTrue(in_array($cohort2->id, $ids, true));
    }

    /**
     * @covers ::resolve
     */
    public function test_query_cohort_static_cohorts_with_tenancy(): void {
        $gen = self::getDataGenerator();
        /** @var \totara_cohort\testing\generator $generator */
        $generator = $gen->get_plugin_generator('totara_cohort');

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        // tennant manager.
        $user1 = $gen->create_user(['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]);
        $user2 = $gen->create_user(['tenantid' => $tenant2->id, 'tenantdomainmanager' => $tenant2->idnumber]);
        $tenant1_category_context = context_coursecat::instance($tenant1->categoryid);
        $tenant2_category_context = context_coursecat::instance($tenant2->categoryid);

        // Create 3 cohorts in tenant1
        $cohort1 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);
        $cohort2 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);
        $cohort3 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);

        // Create 1 cohorts in tenant2
        $cohort_t2 = $generator->create_cohort(['contextid' => $tenant2_category_context->id]);

        // Login as user1 with tenant1
        self::setUser($user1);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        // 3 cohorts.
        self::assertEquals(3, $result['total']);

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertTrue(in_array($cohort1->id, $ids, true));
        self::assertTrue(in_array($cohort2->id, $ids, true));
        self::assertTrue(in_array($cohort3->id, $ids, true));

        self::assertFalse(in_array($cohort_t2->id, $ids, true));


        // Login as admin
        self::setAdminUser();

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        // He must view all.
        self::assertEquals(4, $result['total']);

        // Login as user2 with tenant2
        self::setUser($user2);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        // 1 cohort.
        self::assertEquals(1, $result['total']);
        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertFalse(in_array($cohort1->id, $ids, true));
        self::assertFalse(in_array($cohort2->id, $ids, true));
        self::assertFalse(in_array($cohort3->id, $ids, true));

        self::assertTrue(in_array($cohort_t2->id, $ids, true));

        $user3 = $gen->create_user(['tenantid' => $tenant2->id]);
        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $user3->id, context_system::instance());

        assign_capability('moodle/cohort:view', CAP_ALLOW, $role->id, SYSCONTEXTID);

        self::setUser($user3);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        // 1 cohort.
        self::assertEquals(1, $result['total']);
        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertFalse(in_array($cohort1->id, $ids, true));
        self::assertFalse(in_array($cohort2->id, $ids, true));
        self::assertFalse(in_array($cohort3->id, $ids, true));

        self::assertTrue(in_array($cohort_t2->id, $ids, true));
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_apiuser(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $user->id, context_system::instance());

        assign_capability('moodle/cohort:view', CAP_ALLOW, $role->id, SYSCONTEXTID);

        self::setUser($user);

        $this->create_cohorts();
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        self::assertNotEmpty($result['items']);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_only_return_static_cohorts(): void {
        self::setAdminUser();
        $collection = $this->create_cohorts();

        self::assertCount(10, $collection);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        self::assertEquals(6, $result['total']);

        $collection = $collection->filter(function ($item) {
            return $item->cohorttype == cohort::TYPE_DYNAMIC;
        });

        self::assertCount(4, $collection);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_mutiplesort_exception(): void {
        self::setAdminUser();

        $this->create_cohorts();

        self::expectException(cohort_static_cohorts_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'sort' => [
                    [
                        'column' => 'id'
                    ],
                    [
                        'column' => 'timecreated'
                    ]
                ]
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_authenticated_user(): void {
        $user = self::getDataGenerator()->create_user();
        $this->create_cohorts();
        self::setUser($user);

        self::expectExceptionMessage('You do not have capabilities to view cohorts.');
        self::expectException(cohort_static_cohorts_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'query' => [
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_pagination(): void {
        self::setAdminUser();

        $this->create_cohorts();

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'pagination' => [
                    'limit' => 2,
                    'page' => 0,
                    'next_cursor' => ''
                ]
            ]
        ]);

        self::assertEquals(6, $result['total']);
        self::assertEquals(2, count($result['items']));
        self::assertNotEmpty($result['next_cursor']);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_sort(): void {
        self::setAdminUser();

        $this->create_cohorts();

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'sort' => [
                    [
                        'column' => 'idnumber',
                        'direction' => 'DESC'
                    ]
                ]
            ]
        ]);

        $items = $result['items'];

        $idnumbers = [];
        foreach ($items as $item) {
            $idnumbers[] = $item->idnumber;
        }

        $expected = [8, 7, 5, 4, 2, 1];
        self::assertEquals($expected, $idnumbers);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_non_support_sort_exception(): void {
        self::setAdminUser();

        $this->create_cohorts();

        self::expectException(coding_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'sort' => [
                    [
                        'column' => 'sortme'
                    ]
                ]
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_with_required_param_exception(): void {
        self::setAdminUser();

        $this->create_cohorts();

        self::expectExceptionMessage('Required parameter \'column\' not being passed.');
        self::expectException(cohort_static_cohorts_exception::class);
        $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'sort' => [
                    [
                        'direction' => 'ASC'
                    ]
                ]
            ]
        ]);
    }

    /**
     * @covers ::resolve
     */
    public function test_query_for_tag_filter(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();


        $time = time();
        $cohort1 = $gen->create_cohort(['timecreated' => $time + 6, 'timemodified' => $time + 10]);
        $cohort2 = $gen->create_cohort(['timecreated' => $time + 10, 'timemodified' => $time + 6]);
        $cohort3 = $gen->create_cohort(['timecreated' => $time, 'timemodified' => $time - 5]);
        $cohort4 = $gen->create_cohort(['timecreated' => $time, 'timemodified' => $time + 5]);
        set_config('usetags', 1);

        $tags = core_tag_tag::create_if_missing(1, ['rawname' => 'Tag1'], true);
        $tag = reset($tags);

        $this->creat_tag_for_cohort($cohort1->id, $tag->id);
        $this->creat_tag_for_cohort($cohort2->id, $tag->id);
        $this->creat_tag_for_cohort($cohort3->id, $tag->id);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'filter' => [
                    'tag_filter' => $tag->rawname,
                    'since_timecreated' => $time,
                    'since_timemodified' => $time
                ]
            ]
        ]);

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        // Cohort1 and  Cohort2 expected
        self::assertEquals(2, $result['total']);
        self::assertTrue(in_array($cohort1->id, $ids, true));
        self::assertTrue(in_array($cohort2->id, $ids, true));
        self::assertFalse(in_array($cohort3->id, $ids, true));
        self::assertFalse(in_array($cohort4->id, $ids, true));
    }

    /**
     * @param int $cohort_id
     * @param int $tag_id
     * @return \core_tag\entity\tag_instance
     */
    private function creat_tag_for_cohort(int $cohort_id, int $tag_id): \core_tag\entity\tag_instance {
        $instance = new \core_tag\entity\tag_instance();
        $instance->tagid = $tag_id;
        $instance->component = 'core';
        $instance->itemid = $cohort_id;
        $instance->itemtype = 'cohort';
        $instance->contextid = context_system::instance()->id;
        $instance = $instance->create();

        return $instance;
    }

    /**
     * @covers ::resolve
     */
    public function test_query_for_timecreated_filter(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $cohort1 = $gen->create_cohort(['timecreated' => $time + 6]);
        $cohort2 = $gen->create_cohort(['timecreated' => $time + 10]);
        $cohort3 = $gen->create_cohort(['timecreated' => $time]);
        $cohort4 = $gen->create_cohort(['timecreated' => $time - 1]);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'filter' => [
                    'since_timecreated' => $time,
                ]
            ]
        ]);

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertEquals(3, $result['total']);
        self::assertTrue(in_array($cohort1->id, $ids, true));
        self::assertTrue(in_array($cohort2->id, $ids, true));
        self::assertTrue(in_array($cohort3->id, $ids, true));
        self::assertFalse(in_array($cohort4->id, $ids, true));
    }

    /**
     * @covers ::resolve
     */
    public function test_query_for_timemodified_filter(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $cohort1 = $gen->create_cohort(['timecreated' => $time + 6, 'timemodified' => $time + 10]);
        $cohort2 = $gen->create_cohort(['timecreated' => $time + 10, 'timemodified' => $time + 6]);
        $cohort3 = $gen->create_cohort(['timecreated' => $time, 'timemodified' => $time - 5]);
        $cohort4 = $gen->create_cohort(['timecreated' => $time - 1]);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => [
                'filter' => [
                    'since_timecreated' => $time,
                    'since_timemodified' => $time
                ]
            ]
        ]);

        $ids = array_map(function ($item) {
            return (string)$item->id;
        }, $result['items']);

        self::assertEquals(2, $result['total']);
        self::assertTrue(in_array($cohort1->id, $ids, true));
        self::assertTrue(in_array($cohort2->id, $ids, true));
        self::assertFalse(in_array($cohort3->id, $ids, true));
        self::assertFalse(in_array($cohort4->id, $ids, true));
    }

    /**
     * @param int $count
     * @return collection
     */
    private function create_cohorts(int $count = 10): collection {
        $generator = self::getDataGenerator();

        $cohorts = [];
        foreach (range(0, $count - 1) as $i) {
            $cohort_type = $i % 3 === 0 ? cohort::TYPE_DYNAMIC : cohort::TYPE_STATIC;
            $base = sprintf('Test cohort #%02d', $i);

            $cohort = $generator->create_cohort([
                'active' => true,
                'component' => '',
                'contextid' => context_system::instance()->id,
                'description' => "$base description",
                'descriptionformat' => FORMAT_MOODLE,
                'idnumber' => "$i",
                'name' => $base,
                'cohorttype' => $cohort_type,
                'visible' => true
            ]);

            $cohorts[] = new cohort_entity($cohort);
        }

        return collection::new($cohorts);
    }

    /**
     * Test when assigning API user role in the category context to service account.
     *
     * @covers ::resolve
     */
    public function test_api_user_under_category_context(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        $category = $gen->create_category();
        $category_context = context_coursecat::instance($category->id);
        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);
        role_assign($role->id, $user->id, $category_context);

        // Create two cohorts(one is under parent category, the other one is under child category)
        $cohort1 = $gen->create_cohort(['contextid' => $category_context->id]);
        $child_category = $gen->create_category(['parent' => $category->id]);
        $user1 = $gen->create_user();
        $child_category_context = context_coursecat::instance($child_category->id);
        role_assign($role->id, $user1->id, $child_category_context);
        $cohort2 = $gen->create_cohort(['contextid' => $child_category_context->id]);

        // Login as user assigned API user role under parent child category
        self::setUser($user);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        // Expected to see 2 cohorts
        self::assertEquals(2, count($result['items']));

        // Login as user 1 assigned API user role under child category
        self::setUser($user1);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);
        self::assertEquals(1, count($result['items']));
        $item = reset($result['items']);
        self::assertEquals($cohort2->id, $item->id);

        assign_capability('moodle/cohort:view', CAP_PROHIBIT, $role->id, $child_category_context->id);

        self::expectExceptionMessage('You do not have capabilities to view cohorts.');
        self::expectException(cohort_static_cohorts_exception::class);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);
    }

    /**
     *
     * @covers ::resolve
     */
    public function test_tenant_api_user_under_category_context(): void {
        $gen = self::getDataGenerator();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();
        $tenant1 = $tenant_generator->create_tenant();
        $user = $gen->create_user(['tenantid' => $tenant1->id]);

        $child_category = $gen->create_category(['parent' => $tenant1->categoryid]);
        $child_category_context = context_coursecat::instance($child_category->id);
        $roles = get_archetype_roles('apiuser');
        $role = reset($roles);

        // Assign api role under child tenant category context
        role_assign($role->id, $user->id, $child_category_context);

        $category_context = context_coursecat::instance($tenant1->categoryid);
        $cohort = $gen->create_cohort(['contextid' => $category_context->id]);
        $child_cohort = $gen->create_cohort(['contextid' => $child_category_context->id]);

        self::setUser($user);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        // Expected only see 1 cohort that created under child tenant category context.
        self::assertEquals(1, count($result['items']));
        $item = reset($result['items']);
        self::assertEquals($child_cohort->id, $item->id);

        $user1 = $gen->create_user(['tenantid' => $tenant1->id]);

        // Assign api role under tenant category context.
        role_assign($role->id, $user1->id, $category_context);

        self::setUser($user1);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'query' => []
        ]);

        // Expected see 2 cohorts including one from child tenant context.
        self::assertEquals(2, count($result['items']));
    }
}