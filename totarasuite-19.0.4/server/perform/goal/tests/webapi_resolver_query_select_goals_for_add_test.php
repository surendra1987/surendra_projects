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
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;
use perform_goal\settings_helper;
use perform_goal\model\target_type\date as target_type_date;
use core\entity\user as user_entity;
use totara_webapi\graphql;
use core\webapi\execution_context;
use totara_webapi\request;
use totara_webapi\server;
use GraphQL\Error\DebugFlag;

/**
 * Unit tests for the perform_goal_select_goals_for_add query resolver with the AJAX API.
 */
class perform_goal_webapi_resolver_query_select_goals_for_add_test extends testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'perform_goal_select_goals_for_add';

    /**
     * Helper method to generate some test goals.
     * @param int $number_of_goals
     * @return array
     */
    private function create_test_goals(int $number_of_goals, bool $use_same_goal_names = false): array {
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
        $data_config = ['user_id' => $goal_subject_user1->id, 'target_type' => target_type_date::get_type()];
        if ($use_same_goal_names) {
            $data_config['name'] = 'test goal_' . time() . '_' . rand();
        }
        for ($i = 0; $i < $number_of_goals; $i++) {
            $data = goal_generator_config::new($data_config);
            $test_goals[] = goal_generator::instance()->create_goal($data);
        }

        return $test_goals;
    }

    public function test_with_user_id_filter(): void {
        // Create 3 test goals.
        $test_goals = $this->create_test_goals(2);
        $goal_owner_user = user_entity::repository()->find($test_goals[0]->owner_id);
        $goal_subject_user1 = user_entity::repository()->find($test_goals[0]->user_id);
        // This 1 goal has a different subject user.
        $goal_subject_user2 = self::getDataGenerator()->create_user();
        $data2 = goal_generator_config::new(['user_id' => $goal_subject_user2->id, 'target_type' => target_type_date::get_type()]);
        goal_generator::instance()->create_goal($data2);

        self::setUser($goal_owner_user);
        $args = [
            'input' => [
                'filters' => [
                    'user_id' => $goal_subject_user1->id, // Filter goals on just $goal_subject_user1.
                ],
                'cursor' => null
            ]
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);

        self::assert_webapi_operation_successful($result);
        self::assertCount(2, $result[0]["items"]); // Expect only goals to be returned for $goal_subject_user1.
    }

    public function test_with_name_filter(): void {
        // Create 2 test goals with the same name for the same subject user.
        $test_goals = $this->create_test_goals(2, true);
        $goal_owner_user = user_entity::repository()->find($test_goals[0]->owner_id);
        $goal_subject_user1 = user_entity::repository()->find($test_goals[0]->user_id);
        $desired_goal_name =  $test_goals[0]->name;

        // Create another goal for the same subject user with a different name.
        $data2 = goal_generator_config::new(['user_id' => $goal_subject_user1->id, 'target_type' => target_type_date::get_type()]);
        goal_generator::instance()->create_goal($data2);

        // Create another goal with the same name for a different subject user.
        $goal_subject_user2 = self::getDataGenerator()->create_user();
        $data3 = goal_generator_config::new(['user_id' => $goal_subject_user2->id, 'target_type' => target_type_date::get_type(),
            'name' => $desired_goal_name
        ]);
        goal_generator::instance()->create_goal($data3);

        self::setUser($goal_owner_user);

        $args = [
            'input' => [
                'filters' => [
                    'user_id' => $goal_subject_user1->id,
                    'name' => $desired_goal_name // Filter with goal name.
                ],
                'cursor' => null
            ]
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);

        self::assert_webapi_operation_successful($result);
        self::assertCount(2, $result[0]["items"]);
    }

    public function test_with_goals_filter(): void {
        // Create 4 test goals.
        $test_goals = $this->create_test_goals(3);
        $goal_owner_user = user_entity::repository()->find($test_goals[0]->owner_id);
        $goal_subject_user1 = user_entity::repository()->find($test_goals[0]->user_id);
        // This 1 goal has a different subject user.
        $goal_subject_user2 = self::getDataGenerator()->create_user();
        $data2 = goal_generator_config::new(['user_id' => $goal_subject_user2->id, 'target_type' => target_type_date::get_type()]);
        goal_generator::instance()->create_goal($data2);

        $desired_goal_ids = [$test_goals[0]->id, $test_goals[2]->id];

        self::setUser($goal_owner_user);
        $args = [
            'input' => [
                'filters' => [
                    'user_id' => $goal_subject_user1->id,
                    'goals' => $desired_goal_ids // Filter on a list of goal IDs.
                ],
                'cursor' => null
            ]
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);

        self::assert_webapi_operation_successful($result);
        $items = $result[0]["items"];
        self::assertCount(2, $items); // Expect only goals that match our desired goal IDs.
        $fetched_goal_ids = [$items[0]['id'], $items[1]['id']];
        sort($fetched_goal_ids);
        self::assertEquals($desired_goal_ids, $fetched_goal_ids);
    }

    public function test_for_user_permissions(): void {
        $test_goals = $this->create_test_goals(1);
        $goal_subject_user1 = user_entity::repository()->find($test_goals[0]->user_id);
        $goal_subject_user2 = self::getDataGenerator()->create_user();
        self::setUser($goal_subject_user2);

        // $goal_subject_user2 does not have permissions to view goals for $goal_subject_user1.
        $args = [
            'input' => [
                'filters' => [
                    'user_id' => $goal_subject_user1->id,
                ],
                'cursor' => null
            ]
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);

        self::assert_webapi_operation_successful($result);
        self::assertCount(0, $result[0]["items"]);
    }

    public function test_for_tenant_users(): void {
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

        // $tenant1_member_user does have permissions to view goals for $subject_user_tenant1.
        self::setUser($tenant1_member_user);
        $args = [
            'input' => [
                'filters' => [
                    'user_id' => $subject_user_tenant1 ->id,
                ],
                'cursor' => null
            ]
        ];
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        self::assert_webapi_operation_successful($result);
        self::assertCount(1, $result[0]["items"]);

        // $tenant1_member_user does NOT have permissions to view goals for $subject_user_tenant2.
        $args['input']['filters']['user_id'] = $subject_user_tenant2 ->id;
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        self::assert_webapi_operation_successful($result);
        self::assertCount(0, $result[0]["items"]);

        // Tear down.
        set_config('tenantsisolated', $original_config_setting);
    }

    public function test_with_no_filter(): void {
        // We need to test this with the Developer API, since this query is not available through the External API.
        $test_goals = $this->create_test_goals(1);
        $goal_owner_user = user_entity::repository()->find($test_goals[0]->owner_id);
        self::setUser($goal_owner_user);
        $query = 'query {
            perform_goal_select_goals_for_add(
                input: {} 
            ) {
                items {
                    id
                    name
                    target_date
                }
                total
                next_cursor
            }
        }';

        $result = $this->make_dev_api_request($query);

        self::assertNotEmpty($result['errors']);
        self::assertEquals('Argument "input" has invalid value {  }.', $result['errors'][0]['message']);
    }

    /**
     * Helper method to call the Developer API with a GraphQL query string.
     * @param string $query
     * @return array
     */
    public function make_dev_api_request(string $query): array {
        ob_start();

        // Make a request for the Dev API in a different way, for the test environment runner to succeed.
        $exec_context = execution_context::create(graphql::TYPE_DEV);
        $request = new request(
            $exec_context->get_endpoint_type(),
            [
                'operationName' => null,
                'query' => $query
            ]
        );
        $server = new server($exec_context, DebugFlag::INCLUDE_DEBUG_MESSAGE);
        $result = $server->handle_request($request);
        $server->send_response($result, false);

        $response = ob_get_clean();
        return json_decode($response, true);
    }
}
