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

require_once(__DIR__.'/external_api_phpunit_helper.php');
require_once(__DIR__.'/perform_goal_update_goal_testcase.php');

/**
 * Unit tests for the perform_goal_update_goal mutation GraphQL API resolver.
 * For the External API.
 *
 * @group perform_goal
 */
class perform_goal_webapi_resolver_mutation_update_goal_external_api_test extends perform_goal_update_goal_testcase {
    private const MUTATION = "mutation {
        perform_goal_update_goal(
            %s
            %s
        ) {
            goal {
                id
                context_id
                owner {
                    id
                    username
                    firstname
                    lastname
                }
                user {
                    id
                    username
                    firstname
                    lastname
                }
                name
                id_number
                description
                assignment_type
                plugin_name
                start_date
                target_type
                target_date
                target_value
                current_value
                current_value_updated_at
                status {
                    id
                    label
                }
                status_updated_at
                closed_at
                created_at
                updated_at
            }
            raw {
                available_statuses {
                  id
                  label
                }
                description
                start_date {
                    iso
                }
                target_date {
                    iso
                }
            }
            permissions {
                can_manage
                can_update_status
            }
        }
    }";

    use external_api_phpunit_helper;

    public function test_with_valid_inputs_external_api(): void {
        [$args, $original_goal] = $this->setup_env();

        $result = self::make_external_api_request(self::MUTATION, $args);
        self::assert_external_operation_successful($result);

        [
            'goal' => $updated_goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = self::get_operation_data($result);

        [$exp_goal, $raw_data, $interactor] = self::expected_results(
            $original_goal, (object)$args['input']
        );

        self::assert_goal($exp_goal, (object)$updated_goal);
        self::assert_goal_raw_data($raw_data, (object)$raw);
        self::assert_goal_permissions($interactor, (object)$permissions);
    }

    public function test_with_missing_required_field_external_api(): void {
        [$args, ] = $this->setup_env();
        $args['goal_reference'] = [];

        self::assert_external_operation_failed(
            self::make_external_api_request(self::MUTATION, $args),
            'Argument "goal_reference" of required type "perform_goal_reference!" was not provided.'
        );
    }

    public function test_with_perform_goals_disabled_external_api(): void {
        [$args, ] = $this->setup_env();

        settings_helper::disable_perform_goals();
        self::assert_external_operation_failed(
            self::make_external_api_request(self::MUTATION, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();
    }

    public function test_with_invalid_manage_other_user_capability_external_api(): void {
        [$args, ] = $this->setup_env();
        $api_user = self::helper_set_auth_header_get_api_user(false);

        self::assert_external_operation_failed(
            self::make_external_api_request(self::MUTATION, $args, $api_user),
            'Sorry, but you do not currently have permissions to do that (edit a goal in this context).'
        );
    }
}
