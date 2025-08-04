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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\model\goal_task;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\settings_helper;

require_once(__DIR__.'/external_api_phpunit_helper.php');

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_webapi_resolver_mutation_update_goal_task_external_api_test extends testcase {
    private const MUTATION = "mutation {
        perform_goal_update_goal_task(
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
            success
            errors {
                code
                message
            }
        }
    }";

    use external_api_phpunit_helper;

    public function test_update(): void {
        $task = self::create_test_goal_task();
        self::assertNotEmpty($task->description, 'task has no description');
        self::assertNull($task->resource, 'task has no resources');

        $desc = 'updated description';
        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $task->id],
                'description' => $desc
            ]
        ];

        $result = self::make_external_api_request(self::MUTATION, $args);
        self::assert_external_operation_successful($result);
        $response = self::get_operation_data($result);
        static::assertTrue($response['success']);
        static::assertNull($response['errors']['message']);

        $updated = $response['goal_task'];
        self::assertEquals($task->id, $updated['id'], 'wrong task id');
        self::assertEquals($task->goal_id, $updated['goal_id'], 'wrong goal id');
        self::assertEquals($desc, $updated['description'], 'wrong description');
        self::assertNull($updated['resource'], 'non null resource');

        $type = course_type::create(
            $this->getDataGenerator()->create_course()->id
        );
        $desc = 'updated NEW description';
        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $task->id],
                'description' => $desc,
                'resource_type' => $type->code(),
                'resource_id' => $type->resource_id()
            ]
        ];

        $result = self::make_external_api_request(self::MUTATION, $args);
        self::assert_external_operation_successful($result);
        $response = self::get_operation_data($result);
        static::assertTrue($response['success']);
        static::assertNull($response['errors']['message']);

        $updated = $response['goal_task'];
        self::assertEquals($task->id, $updated['id'], 'wrong task id');
        self::assertEquals($task->goal_id, $updated['goal_id'], 'wrong goal id');
        self::assertEquals($desc, $updated['description'], 'wrong description');

        $updated_resource = $updated['resource'] ?? null;
        self::assertNotNull($updated_resource, 'null resource');
        self::assertEquals($task->id, $updated_resource['task_id'], 'wrong resource task id');
        self::assertEquals(
            $type->resource_id(),
            $updated_resource['resource_id'],
            'wrong resource id'
        );
        self::assertEquals(
            $type->code(),
            $updated_resource['resource_type'],
            'wrong resource type'
        );
    }

    public function test_failed_op(): void {
        $task = self::create_test_goal_task();
        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $task->id],
                'description' => 'testing'
            ]
        ];

        settings_helper::disable_perform_goals();
        self::assert_external_operation_failed(
            self::make_external_api_request(self::MUTATION, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();

        $api_user = self::helper_set_auth_header_get_api_user(false);
        $result = self::make_external_api_request(self::MUTATION, $args, $api_user);
        $response = self::get_operation_data($result);
        static::assertNull($response['goal_task']);
        static::assertFalse($response['success']);
        static::assertStringContainsString(
            'Sorry, but you do not currently have permissions to do that (update a goal task in this context)',
            $response['errors']['message']
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