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

use perform_goal\model\goal_task;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;
use perform_goal\settings_helper;

require_once(__DIR__.'/perform_goal_task_testcase.php');

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_webapi_resolver_mutation_update_goal_task_test extends perform_goal_task_testcase {
    private const MUTATION = 'perform_goal_update_goal_task';

    use webapi_phpunit_helper;

    public function test_successful_ajax_call(): void {
        $task = self::create_test_goal_task();
        self::assertNotEmpty($task->description, 'task has no description');
        self::assertNull($task->resource, 'task has resource');

        $desc = 'updated description';
        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $task->id],
                'description' => $desc
            ]
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);
        $goal_task = $response['goal_task'];

        $context = $task->goal->context;
        self::assert_goal_task(
            $context, goal_task::load_by_id($task->id), (object)$goal_task
        );
        static::assertTrue($response['success']);
        static::assertNull($response['errors']['message']);

        $type = course_type::create(
            $this->getDataGenerator()->create_course()->id
        );

        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $task->id],
                'description' => $desc,
                'resource_type' => $type->code(),
                'resource_id' => $type->resource_id()
            ]
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);
        $goal_task = $response['goal_task'];

        self::assert_goal_task(
            $context, goal_task::load_by_id($task->id), (object)$goal_task
        );
        static::assertTrue($response['success']);
        static::assertNull($response['errors']['message']);
    }

    public function test_failed_ajax_call(): void {
        $try = function (array $parameters, string $err): void {
            try {
                $this->resolve_graphql_mutation(self::MUTATION, $parameters);
                self::fail('managed to execute op when it should have failed');
            } catch (moodle_exception $e) {
                self::assertStringContainsString($err, $e->getMessage());
            }
        };

        $task = self::create_test_goal_task();
        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $task->id],
                'description' => 'testing'
            ]
        ];

        settings_helper::disable_perform_goals();
        $try($args, 'Feature perform_goals is not available.');
        settings_helper::enable_perform_goals();

        self::setGuestUser();
        $try($args, 'Must be an authenticated user');

        $this->setAdminUser();
        $args['input']['goal_task_reference']['id'] = 101;
        $try($args, 'Unknown task');

        $args['input']['goal_task_reference'] = ['goal_id' => 123, 'ordinal' => 0];
        $try($args, 'Unknown task');

        $args['input']['goal_task_reference'] = ['goal_id' => $task->goal->id, 'ordinal' => 1];
        $try($args, 'Unknown task');
    }

    public function test_failed_call(): void {
        $try = function (array $parameters, string $err): void {
            $result = $this->parsed_graphql_operation(self::MUTATION, $parameters);
            self::assert_webapi_operation_successful($result);
            $response = $this->get_webapi_operation_data($result);

            static::assertNull($response['goal_task']);
            static::assertFalse($response['success']);
            static::assertStringContainsString(
                $err,
                $response['errors']['message']
            );
        };

        $task = self::create_test_goal_task();
        $args = [
            'input' => [
                'goal_task_reference' => ['id' => $task->id],
                'description' => 'testing'
            ]
        ];

        $user = self::getDataGenerator()->create_user();
        self::setUser($user);
        $try($args, 'Sorry, but you do not currently have permissions to do that (update a goal task in this context)');

        $this->setAdminUser();
        $args['input']['resource_type'] = course_type::TYPECODE;
        $try($args, 'Both resource type and resource id must be specified');

        unset($args['input']['resource_type']);
        $args['input']['resource_id'] = $this->getDataGenerator()->create_course()->id;
        $try($args, 'Both resource type and resource id must be specified');

        $args['input']['resource_type'] = course_type::TYPECODE;
        $args['input']['resource_id'] = 133;
        $try($args, 'Resource with specified id does not exist');
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