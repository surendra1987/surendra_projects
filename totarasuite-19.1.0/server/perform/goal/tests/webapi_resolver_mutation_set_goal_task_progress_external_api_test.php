<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\model\goal_task;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\settings_helper;

require_once(__DIR__.'/external_api_phpunit_helper.php');

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_webapi_resolver_mutation_set_goal_task_progress_external_api_test extends testcase {
    private const MUTATION = "mutation {
        perform_goal_set_goal_task_progress(
          %s
        ) {
            goal_task {
                id
                goal_id
                description
                completed_at
                created_at
                updated_at
                resource {
                    id
                    task_id
                    resource_id
                    resource_type
                    created_at
                }
            }
        }
    }";

    use external_api_phpunit_helper;

    public function test_set_completed(): void {
        $task = self::create_test_goal_task();
        self::assertNull($task->completed_at, 'task already completed');

        $expected_task_id = $task->id;
        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $expected_task_id],
                'completed' => 1
            ]
        ];

        // Complete task.
        $result = self::make_external_api_request(self::MUTATION, $args);
        self::assert_external_operation_successful($result);

        ['goal_task' => $task] = self::get_operation_data($result);
        self::assertEquals($expected_task_id, $task['id'], 'wrong task id');
        self::assertNotNull($task['completed_at'], 'task not completed');

        $task = goal_task::load_by_id($expected_task_id);
        self::assertTrue($task->completed, 'task not completed');

        // Uncomplete task.
        $args['input']['completed'] = 0;
        $result = self::make_external_api_request(self::MUTATION, $args);
        self::assert_external_operation_successful($result);

        ['goal_task' => $task] = self::get_operation_data($result);
        self::assertEquals($expected_task_id, $task['id'], 'wrong task id');
        self::assertNull($task['completed_at'], 'task completed');

        $task = goal_task::load_by_id($expected_task_id);
        self::assertFalse($task->completed, 'task completed');
    }

    public function test_failed_op(): void {
        $task_id = self::create_test_goal_task()->id;
        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $task_id],
                'completed' => 1
            ]
        ];

        settings_helper::disable_perform_goals();
        self::assert_external_operation_failed(
            self::make_external_api_request(self::MUTATION, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();

        $api_user = self::helper_set_auth_header_get_api_user(false);
        self::assert_external_operation_failed(
            self::make_external_api_request(self::MUTATION, $args, $api_user),
            'you do not currently have permissions to do that (change goal task progress in this context)'
        );

        $args['input']['goal_task_reference']['id'] = 101;
        self::assert_external_operation_failed(
            self::make_external_api_request(self::MUTATION, $args), 'Unknown task'
        );
    }

    /**
     * Generates test data.
     *
     * @return goal_task the created goal task.
     */
    private static function create_test_goal_task(): goal_task {
        self::setAdminUser();

        $cfg = goal_generator_config::new(
            [
                'number_of_tasks' => 1,
                'user_id' => self::getDataGenerator()->create_user()->id
            ]
        );

        return generator::instance()->create_goal($cfg)->tasks->first();
    }
}