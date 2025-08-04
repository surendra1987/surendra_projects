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
use perform_goal\entity\goal_task as goal_task_entity;

require_once(__DIR__.'/external_api_phpunit_helper.php');

class perform_goal_webapi_resolver_mutation_delete_goal_task_external_api_test extends testcase {

    private const MUTATION = "mutation {
        perform_goal_delete_goal_task(
            %s
        ) {
            success
        }
    }";

    use external_api_phpunit_helper;

    public function test_execute_query_successful(): void {
        settings_helper::enable_perform_goals();

        [$goal_task, $user] = $this->create_test_goal_task();
        self::setUser($user);

        // Test the goal task exists before deleting it.
        self::assertNotNull(goal_task_entity::repository()->find($goal_task->id));

        $args['goal_task_reference'] = [
            'id' => $goal_task->id
        ];

        $result = self::make_external_api_request(self::MUTATION, $args);
        self::assert_external_operation_successful($result);
        ['success' => $deleted]= self::get_operation_data($result);

        self::assertTrue($deleted);
        self::assertNull(goal_task_entity::repository()->find($goal_task->id));
        self::assertGreaterThan(0, goal_task_entity::repository()->count());
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
