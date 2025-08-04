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

use perform_goal\model\goal_task;
use perform_goal\model\goal_task_resource;
use perform_goal\model\goal_task_type\type;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\settings_helper;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;

require_once(__DIR__.'/external_api_phpunit_helper.php');
require_once(__DIR__.'/perform_goal_task_testcase.php');

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_webapi_resolver_query_get_goal_tasks_external_api_test extends perform_goal_task_testcase {
    private const QUERY = "query {
        perform_goal_get_goal_tasks(
            %s
        ) {
            goal_id
            tasks {
                id
                goal_id
                description
                completed_at
                created_at
                updated_at
                resource_exists
                resource_can_view
                resource {
                    id
                    task_id
                    resource_id
                    resource_type
                    created_at
                    resource_custom_data
                }
            }
            total
            completed 
        }
    }";

    use external_api_phpunit_helper;

    public function test_resolver(): void {
        $no_of_tasks = 20;
        [$args, $goal] = $this->create_test_data($no_of_tasks);

        $result = self::make_external_api_request(self::QUERY, $args);
        self::assert_external_operation_successful($result);

        [
            'goal_id' => $actual_goal_id,
            'tasks' => $actual_tasks,
            'total' => $actual_total,
            'completed' => $actual_completed
        ] = self::get_operation_data($result);

        self::assertEquals($goal->id, $actual_goal_id);
        self::assertEquals(2, $actual_completed);
        self::assertEquals($no_of_tasks, $actual_total);
        self::assertEquals($no_of_tasks, count($actual_tasks));

        $expected_tasks = $goal->tasks->key_by('id');

        foreach ($actual_tasks as $actual_task) {
            $id = $actual_task['id'];
            $expected_task = $expected_tasks->item($id);
            self::assertNotNull($expected_task, "unknown actual task id $id");
        }
    }

    public function test_failed_query(): void {
        [$args, ] = $this->create_test_data(1);

        settings_helper::disable_perform_goals();
        self::assert_external_operation_failed(
            self::make_external_api_request(self::QUERY, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();

        $api_user = self::helper_set_auth_header_get_api_user(false);
        self::assert_external_operation_failed(
            self::make_external_api_request(self::QUERY, $args, $api_user),
            'you do not currently have permissions to do that (view goal tasks in this context)'
        );

        $args['goal_reference']['id'] = 101;
        self::assert_external_operation_failed(
            self::make_external_api_request(self::QUERY, $args),
            'Can not find data record in database'
        );
    }

    /**
     * Creates test data.
     *
     * @param int $no_of_tasks no of tasks to generate.
     *
     * @return a goal populated with the specified number of tasks. The first
     *         and last task are marked as completed and have task resources.
     */
    private function create_test_data(int $no_of_tasks): array {
        $this->setAdminUser();

        $cfg = goal_generator_config::new(
            [
                'number_of_tasks' => $no_of_tasks,
                'user_id' => self::getDataGenerator()->create_user()->id
            ]
        );
        $goal = generator::instance()->create_goal($cfg);

        $tasks = $goal->tasks;

        $gen = $this->getDataGenerator();
        array_map(
            function (goal_task $task) use ($gen): goal_task {
                $course = $gen->create_course();
                goal_task_resource::create($task, type::by_code(course_type::TYPECODE, $course->id));
                return $task->set_completed(true);
            },
            [$tasks->first(), $tasks->last()]
        );
        $this->executeAdhocTasks();
        return [
            ['goal_reference' => ['id' => $goal->id]], $goal->refresh()
        ];
    }
}
