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

use perform_goal\settings_helper;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_goal_update_goal_testcase.php');

/**
 * Unit tests for the perform_goal_update_goal mutation GraphQL API resolver.
 * For the AJAX API.
 *
 * @group perform_goal
 */
class perform_goal_webapi_resolver_mutation_update_goal_test extends perform_goal_update_goal_testcase {
    private const MUTATION = 'perform_goal_update_goal';

    use webapi_phpunit_helper;

    public function test_with_valid_inputs_ajax_api(): void {
        [$args, $original_goal] = $this->setup_env();

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);

        [
            'goal' => $updated_goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = $this->get_webapi_operation_data($result);

        [$exp_goal, $raw_data, $interactor] = self::expected_results(
            $original_goal, (object)$args['input']
        );

        self::assert_goal($exp_goal, (object)$updated_goal);
        self::assert_goal_raw_data($raw_data, (object)$raw);
        self::assert_goal_permissions($interactor, (object)$permissions);
    }

    public function test_with_required_fields_missing_ajax_api(): void {
        [$args, ] = $this->setup_env();
        unset($args['goal_reference']);

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'variable "$goal_reference" which was not provided'
        );
    }

    public function test_with_perform_goals_disabled_ajax_api(): void {
        [$args, ] = $this->setup_env();

        settings_helper::disable_perform_goals();
        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();
    }

    public function test_with_for_goal_reference_ajax_api(): void {
        [$args, $original_goal] = $this->setup_env();

        // Finding a perform_goal by 'id_number' (not 'id') should find the same
        // goal as we used 'id'.
        $args['goal_reference'] = ['id_number' => $original_goal->id_number];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);

        [
            'goal' => $updated_goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = $this->get_webapi_operation_data($result);

        [$exp_goal, $raw_data, $interactor] = self::expected_results(
            $original_goal, (object)$args['input']
        );

        self::assert_goal($exp_goal, (object)$updated_goal);
        self::assert_goal_raw_data($raw_data, (object)$raw);
        self::assert_goal_permissions($interactor, (object)$permissions);
    }

    public function test_tenant_user_can_update_goal_for_user_in_own_tenant(): void {
        [$owner, $subject, ] = self::create_tenant_and_users();
        [$args, $original_goal] = $this->setup_env($owner, $subject);

        self::setUser($owner);
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);

        [
            'goal' => $updated_goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = $this->get_webapi_operation_data($result);

        [$exp_goal, $raw_data, $interactor] = self::expected_results(
            $original_goal, (object)$args['input']
        );

        self::assert_goal($exp_goal, (object)$updated_goal);
        self::assert_goal_raw_data($raw_data, (object)$raw);
        self::assert_goal_permissions($interactor, (object)$permissions);

        set_config('tenantsisolated', 0);
    }

    public function test_tenant_user_cannot_update_goal_for_user_in_different_tenant(): void {
        [$owner1, , $tenant1] = self::create_tenant_and_users();
        [, $subject2, $tenant2] = self::create_tenant_and_users();

        self::assign_as_staff_manager(
            $owner1->id, context_tenant::instance($tenant1->id)
        );

        [$args, ] = $this->setup_env(
            $owner1, $subject2, context_tenant::instance($tenant2->id)
        );

        self::setUser($owner1);
        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Sorry, but you do not currently have permissions to do that (edit a goal in this context).'
        );

        set_config('tenantsisolated', 0);
    }

    public function test_validate_start_and_target_dates(): void {
        [$args, $original_goal] = $this->setup_env();
        // Test it is all valid for target date only.
        $target_date = $args['input']['target_date'];
        unset($args['input']);
        $args['input']['target_date'] = $target_date;

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);
    }

    public function test_invalid_start_and_target_dates(): void {
        [$args, ] = $this->setup_env();
        $target_date = $args['input']['target_date'];
        unset($args['input']);
        // Test it is not a valid target date because it's earlier than original start date
        $args['input']['target_date'] = $target_date - self::MONTHSECS * 3;

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'The goal start date must before the target_date'
        );
    }
}
