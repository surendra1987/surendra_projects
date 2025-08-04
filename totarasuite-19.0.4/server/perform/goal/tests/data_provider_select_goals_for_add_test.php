<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\data_provider\select_goals_for_add_data_provider;
use perform_goal\settings_helper;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\model\goal as goal_model;
use perform_goal\model\target_type\date as target_type_date;

/**
 * Unit tests for the goal_data_provider class.
 */
class perform_goal_data_provider_select_goals_for_add_test extends testcase {
    /**
     * Helper method to generate some test goals.
     * @param int $number_of_goals
     * @return array
     */
    private function create_test_goals(int $number_of_goals): array {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.
        $goal_subject_user1 = self::getDataGenerator()->create_user();
        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);
        // Create some test goals.
        $test_goals = [];
        for ($i = 0; $i < $number_of_goals; $i++) {
            $data = goal_generator_config::new(['user_id' => $goal_subject_user1->id, 'target_type' => target_type_date::get_type()]);
            $test_goals[] = goal_generator::instance()->create_goal($data);
        }
        return $test_goals;
    }

    public function test_get_results(): void {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.
        $goal_subject_user1 = self::getDataGenerator()->create_user();
        $goal_subject_user2 = self::getDataGenerator()->create_user();
        $goal_subject_user3 = self::getDataGenerator()->create_user();

        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);
        // Create some goals for user 1
        $date_target_type = target_type_date::get_type();
        $data1 = goal_generator_config::new(['user_id' => $goal_subject_user1->id, 'target_type' => $date_target_type]);
        $goal1 = goal_generator::instance()->create_goal($data1);
        $data2 = goal_generator_config::new(['user_id' => $goal_subject_user1->id, 'target_type' => $date_target_type]);
        $goal2 = goal_generator::instance()->create_goal($data2);
        // Create a goal for user 2
        $data3 = goal_generator_config::new(['user_id' => $goal_subject_user2->id, 'target_type' => $date_target_type]);
        $goal3 = goal_generator::instance()->create_goal($data3);
        // Create a goal for user 3
        $data4 = goal_generator_config::new(['user_id' => $goal_subject_user3->id, 'target_type' => $date_target_type]);
        $goal4 = goal_generator::instance()->create_goal($data4);

        $data_provider = new select_goals_for_add_data_provider();
        $items = $data_provider->get_results()->transform_to([goal_model::class, 'load_by_entity']);

        $expected_goal_ids = [$goal1->id, $goal2->id, $goal3->id, $goal4->id];
        $result_goal_ids = [];
        foreach ($items as $goal_result) {
            if (!in_array($goal_result->id, $result_goal_ids)) {
                $result_goal_ids[] = $goal_result->id;
            }
        }
        sort($result_goal_ids);
        $this->assertInstanceOf(goal_model::class, $items->first());
        self::assertEquals($expected_goal_ids, $result_goal_ids);
    }

    public function test_offset_pagination(): void {
        $number_of_goals = 14;
        $test_goals = $this->create_test_goals($number_of_goals);

        $data_provider = new select_goals_for_add_data_provider();
        $result1 = $data_provider->get_offset_page(5, 1);
        $result2 = $data_provider->get_offset_page(5, 2);
        $result3 = $data_provider->get_offset_page(5, 3);

        // Expected page sizes.
        $this->assertCount(5, $result1->items);
        $this->assertCount(5, $result2->items);
        $this->assertCount(4, $result3->items);

        // Expected next cursors (not strictly necessary)
        $this->assertNotEmpty($result1->next_cursor);
        $this->assertNotEmpty($result2->next_cursor);
        $this->assertEmpty($result3->next_cursor);

        // Expected totals.
        $this->assertEquals($number_of_goals, $result1->total);
        $this->assertEquals($number_of_goals, $result2->total);
        $this->assertEquals($number_of_goals, $result3->total);

        // Test with no arguments.
        $result = $data_provider->get_offset_page();
        $this->assertCount($number_of_goals, $result->items);
        $this->assertEquals($number_of_goals, $result->total);
        $this->assertEmpty($result->next_cursor);
    }

    public function test_cursor_pagination(): void {
        $number_of_goals = 14;
        $test_goals = $this->create_test_goals($number_of_goals);

        $data_provider = new select_goals_for_add_data_provider();
        $result1 = $data_provider->get_page_results(null, 5);
        $result2 = $data_provider->get_page_results($result1->next_cursor, 5);
        $result3 = $data_provider->get_page_results($result2->next_cursor, 5);

        // Expected page sizes.
        $this->assertCount(5, $result1->items);
        $this->assertCount(5, $result2->items);
        $this->assertCount(4, $result3->items);

        // Expected next cursors (not strictly necessary)
        $this->assertNotEmpty($result1->next_cursor);
        $this->assertNotEmpty($result2->next_cursor);
        $this->assertEmpty($result3->next_cursor);

        // Expected totals.
        $this->assertEquals($number_of_goals, $result1->total);
        $this->assertEquals($number_of_goals, $result2->total);
        $this->assertEquals($number_of_goals, $result3->total);

        // Test with no arguments.
        $result = $data_provider->get_page_results();
        $this->assertCount($number_of_goals, $result->items);
        $this->assertEquals($number_of_goals, $result->total);
        $this->assertEmpty($result->next_cursor);
    }

    public function test_sort_by_id(): void {
        $number_of_goals = 5;
        $test_goals = $this->create_test_goals($number_of_goals);

        $data_provider = new select_goals_for_add_data_provider();
        $items = $data_provider->get_results();
        $expected_goal_ids = array_map(function($goal) {
            return $goal->id;
        }, $test_goals);
        $result_goal_ids = [];
        foreach ($items as $item) {
            $result_goal_ids[] = $item->id;
        }
        $this->assertEquals($expected_goal_ids, $result_goal_ids);

        // Sort by id descending.
        $data_provider = new select_goals_for_add_data_provider();
        $data_provider->add_sort_by([['column' => 'id', 'direction' => 'DESC']]);
        $items = $data_provider->get_results();
        $result_goal_ids = [];
        foreach ($items as $item) {
            $result_goal_ids[] = $item->id;
        }
        $this->assertEquals(array_reverse($expected_goal_ids), $result_goal_ids);

        // Sort by id ascending.
        $data_provider = new select_goals_for_add_data_provider();
        $data_provider->add_sort_by([['column' => 'id', 'direction' => 'ASC']]);
        $items = $data_provider->get_results();
        $result_goal_ids = [];
        foreach ($items as $item) {
            $result_goal_ids[] = $item->id;
        }
        $this->assertEquals($expected_goal_ids, $result_goal_ids);
    }

    public function test_invalid_sorting(): void {
        $data_provider = new select_goals_for_add_data_provider();

        // No column.
        try {
            $data_provider->add_sort_by([['direction' => 'ASC']]);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString("Sort parameter must have a 'column' key", $e->getMessage());
        }

        // Invalid column.
        try {
            $data_provider->add_sort_by([['column' => 'totara']]);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('Unknown sort column', $e->getMessage());
        }

        // Invalid direction.
        try {
            $data_provider->add_sort_by([['column' => 'id', 'direction' => 'FOO']]);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString("Invalid sort direction", $e->getMessage());
        }
    }

    public function test_name_filter_with_valid_input(): void {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.
        $goal_subject_user1 = self::getDataGenerator()->create_user();
        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);
        // Create some test goals.
        $date_target_type = target_type_date::get_type();
        $test_goal1_name = 'goal with name A';
        $test_goal2_name = 'goal with name B';
        $data = goal_generator_config::new(['user_id' => $goal_subject_user1->id, 'name' => $test_goal1_name, 'target_type' => $date_target_type]);
        $test_goal1 = goal_generator::instance()->create_goal($data);
        $data = goal_generator_config::new(['user_id' => $goal_subject_user1->id, 'name' => $test_goal2_name, 'target_type' => $date_target_type]);
        $test_goal2 = goal_generator::instance()->create_goal($data);

        $filters = ['name' => 'name B'];
        $data_provider = new select_goals_for_add_data_provider();

        // Operate.
        $results = $data_provider->add_filters($filters)
            ->get_page_results(null, 12);

        // Assert.
        self::assertCount(1, $results->items);
        self::assertEquals($results->items->first()->name, $test_goal2_name);
    }

    public function test_with_goal_ids_filter(): void {
        $number_of_goals = 3;
        $test_goals = $this->create_test_goals($number_of_goals);

        $data_provider = new select_goals_for_add_data_provider();
        $test_goal_ids = [$test_goals[0]->id, $test_goals[2]->id];

        // Operate
        $filters = ['goals' => $test_goal_ids];
        $results = $data_provider->add_filters($filters)
            ->get_page_results(null, 12);

        // Assert
        $result_goal_ids = [];
        foreach ($results->items as $result) {
            $result_goal_ids[] = $result->id;
        }
        sort($result_goal_ids);
        self::assertEquals($test_goal_ids, $result_goal_ids);
    }

    public function test_user_id_filter_with_valid_input(): void {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.
        $goal_subject_user1 = self::getDataGenerator()->create_user();
        $goal_subject_user2 = self::getDataGenerator()->create_user();
        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);
        // Create some test goals.
        $date_target_type = target_type_date::get_type();
        $data = goal_generator_config::new(['user_id' => $goal_subject_user1->id, 'name' => 'test goal 1', 'target_type' => $date_target_type]);
        $test_goal1 = goal_generator::instance()->create_goal($data);
        $data = goal_generator_config::new(['user_id' => $goal_subject_user2->id, 'name' => 'test goal 2', 'target_type' => $date_target_type]);
        $test_goal2 = goal_generator::instance()->create_goal($data);

        $filters = ['user_id' => $goal_subject_user1->id];
        $data_provider = new select_goals_for_add_data_provider();

        // Operate.
        $results = $data_provider->add_filters($filters)
            ->get_page_results(null, 12);

        // Assert.
        self::assertCount(1, $results->items);
        self::assertEquals($goal_subject_user1->id, $results->items->first()->user_id);
    }

    public function test_user_id_filter_with_invalid_input(): void {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.
        $goal_subject_user1 = self::getDataGenerator()->create_user();
        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);
        // Create some test goals.
        $data = goal_generator_config::new(['user_id' => $goal_subject_user1->id, 'name' => 'test goal 1', 'target_type' => target_type_date::get_type()]);
        $test_goal1 = goal_generator::instance()->create_goal($data);

        $filters = ['name' => 'test goal 1', 'user_id' => 0];
        $data_provider = new select_goals_for_add_data_provider();

        // Operate.
        self::expectExceptionMessage('Goal user_id filter must have a value.');
        $results = $data_provider->add_filters($filters)
            ->get_page_results(null, 12);
    }

    public function test_view_permissions_on_fetching_results(): void {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.
        $goal_subject_user1 = self::getDataGenerator()->create_user();
        $goal_subject_user2 = self::getDataGenerator()->create_user();

        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);
        // Create a goal for user 2
        $data1 = goal_generator_config::new(['user_id' => $goal_subject_user2->id, 'target_type' => target_type_date::get_type()]);
        $goal1 = goal_generator::instance()->create_goal($data1);

        self::setUser($goal_subject_user1);
        $data_provider = new select_goals_for_add_data_provider();

        // Let's check with each of the 3 public fetch methods that user1 cannot view another user's goals (without
        // the needed capability).
        $expected_msg = 'You do not have permission to ' . get_string('goal_view_personal_goals', 'perform_goal') . '.';
        try {
            $items = $data_provider->get_results();
        } catch (\coding_exception $exc) {
            self::assertStringContainsString($expected_msg, $exc->getMessage());
        }

        $data_provider = new select_goals_for_add_data_provider();
        try {
            $items = $data_provider->get_page_results();
        } catch (\coding_exception $exc) {
            self::assertStringContainsString($expected_msg, $exc->getMessage());
        }

        $data_provider = new select_goals_for_add_data_provider();
        try {
            $result1 = $data_provider->get_offset_page(5, 1);
        } catch (\coding_exception $exc) {
            self::assertStringContainsString($expected_msg, $exc->getMessage());
        }
    }

    public function test_tenant_permissions_on_view_results(): void {
        global $DB, $CFG;
        // Set up.
        self::setAdminUser();
        settings_helper::enable_perform_goals();
        // Create some tenants.
        $tenant_generator = self::getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $original_config_setting = $CFG->tenantsisolated;
        set_config('tenantsisolated', '1');
        $tenant1 = $tenant_generator->create_tenant();
        $context_tenant1 = context_tenant::instance($tenant1->id);
        $tenant2 = $tenant_generator->create_tenant();

        // Create some tenant subject users.
        $subject_user_tenant1 = $this->getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        $subject_user_tenant2 = $this->getDataGenerator()->create_user(['tenantid' => $tenant2->id]);

        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);
        // Create some tenant user goals.
        $date_target_type = target_type_date::get_type();
        $user_context1 = context_user::instance($subject_user_tenant1->id);
        $data1 = goal_generator_config::new(['user_id' => $subject_user_tenant1->id, 'context' => $user_context1, 'target_type' => $date_target_type]);
        $goal_tenant1 = goal_generator::instance()->create_goal($data1);

        $user_context2 = context_user::instance($subject_user_tenant2->id);
        $data2 = goal_generator_config::new(['user_id' => $subject_user_tenant2->id, 'context' => $user_context2, 'target_type' => $date_target_type]);
        $goal_tenant2 = goal_generator::instance()->create_goal($data2);

        $test_capability = 'perform/goal:viewpersonalgoals';
        // Create a tenant member user - a staff manager.
        $tenant1_member_user = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        // Create a new test role. Assign the role in tenant context to the staffmanager_user.
        $role_id = self::getDataGenerator()->create_role();
        assign_capability($test_capability, CAP_ALLOW, $role_id, $context_tenant1);
        // Note that role is assigned in tenant context.
        role_assign($role_id, $tenant1_member_user->id, $context_tenant1);

        self::setUser($tenant1_member_user);
        $data_provider = new select_goals_for_add_data_provider();

        // Expect to succeed when the query goal's context has the same tenant as the current user.
        $filters = ['goals' => [$goal_tenant1->id]];
        $results = $data_provider->add_filters($filters)
            ->get_page_results(null,12);
        self::assertEquals($goal_tenant1->id, $results->items->first()->id);

        // Expect to fail when the query goal's context has a different tenant to the current user.
        $expected_msg = 'You do not have permission to ' . get_string('goal_view_personal_goals', 'perform_goal') . '.';
        try {
            $filters = ['goals' => [$goal_tenant2->id]];
            $results = $data_provider->add_filters($filters)
                ->get_page_results(null,12);
        } catch (\coding_exception $exc) {
            self::assertStringContainsString($expected_msg, $exc->getMessage());
        }

        // Tear down.
        set_config('tenantsisolated', $original_config_setting);
    }

    public function test_validate_items(): void {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.
        $goal_subject_user1 = self::getDataGenerator()->create_user();
        $goal_subject_user2 = self::getDataGenerator()->create_user();

        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);

        $test_goal_name = 'goal name not unique_' . time();
        // Create some goals.
        $date_target_type = target_type_date::get_type();
        $data1 = goal_generator_config::new(
            [
                'user_id' => $goal_subject_user1->id,
                'context' => context_user::instance($goal_subject_user1->id),
                'name' => $test_goal_name,
                'target_type' => $date_target_type
            ]
        );
        $goal1 = goal_generator::instance()->create_goal($data1);
        $data2 = goal_generator_config::new(
            [
                'user_id' => $goal_subject_user2->id,
                'context' => context_user::instance($goal_subject_user2->id),
                'name' => $test_goal_name,
                'target_type' => $date_target_type
            ]
        );
        $goal2 = goal_generator::instance()->create_goal($data2);

        // Operate. goal_subject_user1 should only be able to find the goal with the name that they are allowed to view.
        self::setUser($goal_subject_user1);
        $data_provider = new select_goals_for_add_data_provider();
        $results = $data_provider->add_filters(['name' => $test_goal_name])
            ->get_page_results_with_permissions();

        // Assert.
        self::assertCount(1, $results->items);
        self::assertEquals($results->items->first()->name, $test_goal_name);
    }
}
