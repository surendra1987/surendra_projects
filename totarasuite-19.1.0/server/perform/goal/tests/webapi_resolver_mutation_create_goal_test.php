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
 * @author Murali Nair <murali.nair@totara.com>
 *
 * @package perform_goal
 */

use perform_goal\entity\goal as goal_entity;
use perform_goal\model\status\in_progress;
use perform_goal\model\target_type\date;
use totara_webapi\phpunit\webapi_phpunit_helper;
use perform_goal\settings_helper;

require_once(__DIR__.'/perform_goal_create_goal_testcase.php');

/**
 * Unit tests for the perform_goal_create_goal mutation GraphQL API resolver.
 * For the AJAX API.
 *
 * @group perform_goal
 */
class perform_goal_webapi_resolver_mutation_create_goal_test extends perform_goal_create_goal_testcase {
    private const MUTATION = 'perform_goal_create_goal';

    use webapi_phpunit_helper;

    public function test_with_valid_inputs_ajax_api(): void {
        $args = $this->setup_env();

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);

        [
            'goal' => $created_goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = $this->get_webapi_operation_data($result);

        [$exp_goal, $raw_data, $interactor] = self::expected_results(
            $created_goal['id'], (object)$args['input']
        );

        self::assert_goal($exp_goal, (object)$created_goal);
        self::assert_goal_raw_data($raw_data, (object)$raw);
        self::assert_goal_permissions($interactor, (object)$permissions);
    }

    public function test_with_required_fields_missing_ajax_api(): void {
        $args = $this->setup_env();
        unset($args['input']['name']);

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Field "name" of required type "String!" was not provided'
        );
    }

    public function test_with_perform_goals_disabled_ajax_api(): void {
        $args = $this->setup_env();

        settings_helper::disable_perform_goals();
        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();
    }

    public function test_tenant_user_can_create_goal_for_user_in_own_tenant(): void {
        [$owner, $subject, ] = self::create_tenant_and_users();
        $args = $this->setup_env($owner, $subject);

        self::setUser($owner);
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);

        [
            'goal' => $created_goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = $this->get_webapi_operation_data($result);

        [$exp_goal, $raw_data, $interactor] = self::expected_results(
            $created_goal['id'], (object)$args['input']
        );

        self::assert_goal($exp_goal, (object)$created_goal);
        self::assert_goal_raw_data($raw_data, (object)$raw);
        self::assert_goal_permissions($interactor, (object)$permissions);

        set_config('tenantsisolated', 0);
    }

    public function test_tenant_user_cannot_create_goal_for_user_in_different_tenant(): void {
        [$owner1, , $tenant1] = self::create_tenant_and_users();
        [, $subject2, $tenant2] = self::create_tenant_and_users();

        self::assign_as_staff_manager(
            $owner1->id, context_tenant::instance($tenant1->id)
        );

        $args = $this->setup_env(
            $owner1, $subject2, context_tenant::instance($tenant2->id)
        );

        self::setUser($owner1);

        $this->assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Sorry, but you do not currently have permissions to do that (create a goal in this context).'
        );

        set_config('tenantsisolated', 0);
    }

    /**
     * Make sure tenancy checks for owner are in place.
     * More detailed tests for this are in the goal_interactor test.
     *
     * @return void
     */
    public function test_tenant_user_cannot_create_goal_for_owner_in_different_tenant(): void {
        [$owner1, $subject1, $tenant1] = self::create_tenant_and_users();
        [$owner2, $subject2, $tenant2] = self::create_tenant_and_users();

        $args = [
            'input' => [
                'context_id' => context_user::instance($subject2->id)->id,
                'current_value' => 0.0,
                'description' => '',
                'id_number' => '12346',
                'name' => 'test_goal2',
                'owner' => [
                    'id' => $owner1->id,
                ],
                'start_date' => time(),
                'status' => in_progress::get_code(),
                'target_date' => time() + (self::MONTHSECS * 3),
                'target_type' => date::get_type(),
                'target_value' => 100.0,
                'user' => [
                    'id' => $subject2->id,
                ]
            ]
        ];

        self::setUser($subject2);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Sorry, but you do not currently have permissions to do that (set owner id in this context).');
        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_can_create_goal_passing_empty_context_id() {
        $subject_user = self::getDataGenerator()->create_user();

        $args = $this->setup_env(null, $subject_user);
        $this->assertEquals($subject_user->id, $args['input']['user']['id']);

        // The front end usually doesn't pass in a context_id, so the mutation must work without it.
        $args['input']['context_id'] = null;

        self::setUser($subject_user);

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        $new_goal_id = $result['goal']->id;
        $goal_entity = new goal_entity($new_goal_id);

        // Goal must be created in the user context of the subject user.
        $this->assertEquals(context_user::instance($subject_user->id)->id, $goal_entity->context_id);
    }
}
