<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\testing\generator as core_generator;
use perform_goal\settings_helper;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal_raw_data;
use perform_goal\model\target_type\date;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;

require_once(__DIR__.'/external_api_phpunit_helper.php');
require_once(__DIR__.'/perform_goal_testcase.php');

/**
 * @group perform_goal
 */
class perform_goal_webapi_resolver_query_goal_external_api_test extends perform_goal_testcase {
    private const QUERY = "query {
        perform_goal_view_goal(
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
                plugin_name
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
        [$args, $exp_goal, $exp_raw, $exp_permissions] = $this->setup_env();

        $result = self::make_external_api_request(self::QUERY, $args);
        self::assert_external_operation_successful($result);

        [
            'goal' => $goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = self::get_operation_data($result);

        self::assert_goal($exp_goal, (object)$goal);
        self::assert_goal_raw_data($exp_raw, (object)$raw);
        self::assert_goal_permissions($exp_permissions, (object)$permissions);
    }

    public function test_failed_ajax_query(): void {
        [$args, ] = $this->setup_env(true);

        settings_helper::disable_perform_goals();
        self::assert_external_operation_failed(
            self::make_external_api_request(self::QUERY, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();
    }

    public function test_empty_result(): void {
        $empty = ['goal' => null, 'raw' => null, 'permissions' => null];

        // An unknown goal should return an empty result
        $args = ['goal_reference' => ['id' => 123]];
        $result = self::make_external_api_request(self::QUERY, $args);
        self::assertEquals($empty, self::get_operation_data($result));

        // Insufficient permissions should return an empty result.
        [$args, ] = $this->setup_env(true);
        $api_user = self::helper_set_auth_header_get_api_user(false);
        $result = self::make_external_api_request(self::QUERY, $args, $api_user);
        self::assertEquals($empty, self::get_operation_data($result));
    }

    /**
     * Generates test data.
     *
     * @param bool $in_user_context if true creates the goal with the subject's
     *        context. Otherwise the goal is created with the default context.
     *
     * @return mixed[] (graphql execution args, goal, goal raw data, interactor)
     *         tuple.
     */
    private function setup_env(bool $in_user_context = false): array {
        $this->setAdminUser();

        $core_generator  = core_generator::instance();
        $owner_id = $core_generator->create_user()->id;
        $subject_id = $core_generator->create_user()->id;
        $context = $in_user_context
            ? context_user::instance($subject_id)
            : context_system::instance();

        self::assign_as_staff_manager($owner_id, $context);

        $cfg = [
            'description' => self::jsondoc_description(
                '<h1>This is a <strong>test</strong> description</h1>'
            ),
            'owner_id' => $owner_id,
            'user_id' => $subject_id,
            'target_type' => date::get_type(),
            'context' => $context
        ];

        $goal = generator::instance()
            ->create_goal(goal_generator_config::new($cfg));

        return [
            ['goal_reference' => ['id' => $goal->id]],
            $goal,
            new goal_raw_data($goal),
            goal_interactor::from_goal($goal)
        ];
    }
}
