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
use perform_goal\settings_helper;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;
use perform_goal\entity\goal_task as goal_task_entity;

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_webapi_resolver_mutation_delete_goal_task_test extends testcase {

    private const MUTATION = 'perform_goal_delete_goal_task';

    use webapi_phpunit_helper;

    public function test_execute_query_successful_with_id(): void {
        settings_helper::enable_perform_goals();

        [$goal_task, $user] = $this->create_test_goal_task();
        self::setUser($user);

        // Test the goal task exists before deleting it.
        self::assertNotNull(goal_task_entity::repository()->find($goal_task->id));

        $args['goal_task_reference'] = [
            'id' => $goal_task->id
        ];

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertTrue($result['success']);
        self::assertNull(goal_task_entity::repository()->find($goal_task->id));
        self::assertGreaterThan(0, goal_task_entity::repository()->count());
    }

    public function test_execute_query_successful_with_goal_id(): void {
        settings_helper::enable_perform_goals();

        [$goal_task, $user] = $this->create_test_goal_task();
        self::setUser($user);

        // Test the goal task exists before deleting it.
        self::assertNotNull(goal_task_entity::repository()->find($goal_task->id));

        $args['goal_task_reference'] = [
            // $goal_task is the first task in the goal task list.
            'goal_id' => $goal_task->goal->id, 'ordinal' => 0
        ];

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertTrue($result['success']);
        self::assertNull(goal_task_entity::repository()->find($goal_task->id));
        self::assertGreaterThan(0, goal_task_entity::repository()->count());
    }

    public function test_with_invalid_fields(): void {
        settings_helper::enable_perform_goals();

        [$goal_task, $user] = $this->create_test_goal_task();
        self::setUser($user);

        // Test the goal task exists before deleting it.
        self::assertNotNull(goal_task_entity::repository()->find($goal_task->id));

        $args['goal_task_reference'] = [
            'id_number' => $goal_task->id
        ];

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Field "id_number" is not defined by type "perform_goal_task_reference".'
        );
    }

    public function test_with_perform_goals_disabled(): void {
        settings_helper::enable_perform_goals();

        [$goal_task, $user] = $this->create_test_goal_task();


        // Test the goal task exists before deleting it.
        self::assertNotNull(goal_task_entity::repository()->find($goal_task->id));

        $args['goal_task_reference'] = [
            'id' => $goal_task->id
        ];

        settings_helper::disable_perform_goals();

        self::setUser($user);

        $this->assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Feature perform_goals is not available.'
        );
    }

    public function test_delete_activity_without_capability(): void {
        settings_helper::enable_perform_goals();

        [$goal_task, $user] = $this->create_test_goal_task();

        $user2 = self::getDataGenerator()->create_user();
        self::setUser($user2);

        // Test the goal task exists before deleting it.
        self::assertNotNull(goal_task_entity::repository()->find($goal_task->id));

        $args['goal_task_reference'] = [
            'id' => $goal_task->id
        ];

        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Sorry, but you do not currently have permissions to do that (delete a goal task in this context)'
        );
    }

    private function create_test_goal_task(int $number_of_tasks = 2): array {
        $this->setAdminUser();
        $user = self::getDataGenerator()->create_user();
        $cfg = goal_generator_config::new(
            [
                'user_id' => $user->id,
                'owner_id' => $user->id,
                'number_of_tasks' => $number_of_tasks
            ]
        );
        return [
            generator::instance()->create_goal($cfg)->tasks->first(),
            $user
        ];
    }
}
