<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;

require_once(__DIR__.'/external_api_phpunit_helper.php');

class perform_goal_webapi_resolver_mutation_create_goal_task_external_api_test  extends testcase {
    private const MUTATION = "mutation {
        perform_goal_create_goal_task(
          %s
        ) {
            goal_task {
              id
              goal_id
              description
              completed_at
              created_at
              updated_at
              resource_can_view
              resource_exists
              resource {
                id
                task_id
                resource_id
                resource_type
                created_at
                resource_custom_data
                
              }
            }
            success
            errors {
              code
              message
            }
          }
        }";

    use external_api_phpunit_helper;
    use perform_goal\testing\traits\goal_task_trait;

    public function test_execute_query_successful(): void {
        [$goal, ] = $this->setup_env();

        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => 'Check <a href="https://example.com?id=123">My course</a> for more information & other things',
        ];

        $result = self::make_external_api_request(self::MUTATION, $args);
        self::assert_external_operation_successful($result);
        $response = self::get_operation_data($result);
        static::assertTrue($response['success']);
        static::assertNull($response['errors']['message']);

        $goal_task = $response['goal_task'];
        [$exp_goal_task] = self::expected_results(
            $goal_task ['id'], (object)[
                'goal_id' => $goal->id,
                // Tags should be removed as default is FORMAT_PLAIN & we use PARAM_TEXT for input.
                'description' => 'Check My course for more information & other things',
            ]
        );

        self::assert_goal_task($exp_goal_task, (object)$goal_task);
    }
}
