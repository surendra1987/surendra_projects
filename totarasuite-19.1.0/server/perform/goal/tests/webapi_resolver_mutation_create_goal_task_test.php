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
use totara_webapi\phpunit\webapi_phpunit_helper;
use perform_goal\settings_helper;
use perform_goal\model\goal_task_type\course_type;
use mod_perform\testing\generator as perform_generator;

class perform_goal_webapi_resolver_mutation_create_goal_task_test extends testcase {

    private const MUTATION = 'perform_goal_create_goal_task';

    use webapi_phpunit_helper;
    use perform_goal\testing\traits\goal_task_trait;

    public function test_execute_query_successful(): void {
        [$goal, ] = $this->setup_env();

        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => 'Check <a href="https://example.com?id=123">My course</a> for more information & so on',
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);

        self::assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);
        $goal_task = $response['goal_task'];

        [$exp_goal_task] = self::expected_results(
            $goal_task['id'], (object)$args['input']
        );

        // We expect HTML tags to be removed because we use PARAM_TEXT for input.
        $exp_goal_task->description = 'Check My course for more information & so on';

        static::assert_goal_task($exp_goal_task, (object)$goal_task);
        static::assertTrue($response['success']);
        static::assertNull($response['errors']['message']);
    }

    public function test_with_required_fields_missing(): void {
        [$goal, ] = $this->setup_env();

        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => 'Check <a href="https://example.com?id=123">My course</a> for more information',
        ];
        unset($args['input']['goal_id']);

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Field "goal_id" of required type "core_id!" was not provided'
        );
    }

    public function test_invalid_goal_id_parameter(): void {
        [$goal, ] = $this->setup_env();

        $args['input'] = [
            'goal_id' => 99911,
            'description' => 'Check <a href="https://example.com?id=123">My course</a> for more information',
        ];

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Can not find data record in database'
        );
    }

    public function test_with_perform_goals_disabled(): void {
        [$goal, ] = $this->setup_env();

        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => 'Check <a href="https://example.com?id=123">My course</a> for more information',
        ];

        settings_helper::disable_perform_goals();

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();
    }

    public function test_new_task_without_capability(): void {
        settings_helper::enable_perform_goals();

        [$goal, ] = $this->setup_env();

        $user2 = self::getDataGenerator()->create_user();
        self::setUser($user2);

        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => 'Check <a href="https://example.com?id=123">My course</a> for more information',
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);

        static::assertNull($response['goal_task']);
        static::assertFalse($response['success']);
        static::assertStringContainsString(
            'Sorry, but you do not currently have permissions to do that (add a goal task in this context).',
            $response['errors']['message']
        );
    }

    public function test_add_task_with_task_resource(): void {
        settings_helper::enable_perform_goals();

        [$goal, $course] = $this->setup_env();
        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => '',
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);
        $goal_task = $response['goal_task'];

        [$exp_goal_task] = self::expected_results(
            $goal_task ['id'], (object)$args['input']
        );

        self::assert_goal_task($exp_goal_task, (object)$goal_task);
        static::assertTrue($response['success']);
        static::assertNull($response['errors']['message']);
    }

    public function test_invalid_resource_type_parameter(): void {
        settings_helper::enable_perform_goals();

        [$goal, $course] = $this->setup_env();
        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => '',
            'resource_id' => $course->id,
            'resource_type' => 99999999
        ];

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Unknown goal task resource code: 99999999'
        );
    }

    public function test_invalid_resource_id_parameter(): void {
        settings_helper::enable_perform_goals();

        [$goal, $course] = $this->setup_env();
        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => '',
            'resource_id' => 9999999,
            'resource_type' => course_type::TYPECODE
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);

        static::assertNull($response['goal_task']);
        static::assertFalse($response['success']);
        static::assertStringContainsString(
            'Resource with specified id does not exist.',
            $response['errors']['message']
        );
    }

    public function test_missing_resource_id_or_resource_type(): void {
        settings_helper::enable_perform_goals();

        [$goal, $course] = $this->setup_env();
        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => 'testing',
            'resource_type' => course_type::TYPECODE
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);

        static::assertNull($response['goal_task']);
        static::assertFalse($response['success']);
        static::assertStringContainsString(
            'Both resource type and resource id must be specified.',
            $response['errors']['message']
        );

        unset($args['input']['resource_type']);
        $args['input']['resource_id'] = $course->id;
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);

        static::assertNull($response['goal_task']);
        static::assertFalse($response['success']);
        static::assertStringContainsString(
            'Both resource type and resource id must be specified.',
            $response['errors']['message']
        );
    }

    public function test_resource_id_belongs_to_other_container(): void {
        settings_helper::enable_perform_goals();

        [$goal, ] = $this->setup_env();
        $perform_generator = perform_generator::instance();
        $activity = $perform_generator->create_activity_in_container();
        $this->executeAdhocTasks();

        $args['input'] = [
            'goal_id' => $goal->id,
            'description' => '',
            'resource_id' => $activity->course, // container type is 'container_perform' and exists under 'course' table
            'resource_type' => course_type::TYPECODE
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);
        $response = $this->get_webapi_operation_data($result);

        static::assertNull($response['goal_task']);
        static::assertFalse($response['success']);
        static::assertStringContainsString(
            'Resource with specified id does not exist.',
            $response['errors']['message']
        );
    }
}
