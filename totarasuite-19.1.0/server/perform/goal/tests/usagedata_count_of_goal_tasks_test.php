<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License or
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\entity\goal_task_resource;
use perform_goal\model\goal_task;
use perform_goal\model\goal_task_resource as goal_task_resource_model;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\testing\goal_task_generator_config;
use perform_goal\usagedata\count_of_goal_tasks;

defined('MOODLE_INTERNAL') || die();

/**
 * @group perform
 */
class perform_goal_usagedata_count_of_goal_tasks_test extends testcase {

    public function test_empty_result(): void {
        static::assertEquals([
            'total_goal_tasks_count' => 0,
            'completed_goal_tasks_count' => 0,
            'goals_with_at_least_one_task_count' => 0,
            'goals_with_at_least_one_task_resource_count' => 0,
            'total_task_resources_count' => 0,
        ], (new count_of_goal_tasks())->export());
    }

    public function test_default_data(): void {
        $this->create_goals_with_tasks();

        static::assertEquals([
            'total_goal_tasks_count' => 4,
            'completed_goal_tasks_count' => 0,
            'goals_with_at_least_one_task_count' => 3,
            'goals_with_at_least_one_task_resource_count' => 0,
            'total_task_resources_count' => 0,
        ], (new count_of_goal_tasks())->export());
    }

    public function test_completed_goal_tasks_count(): void {
        [$goal1_task1, $goal1_task2, $goal2_task1, $goal3_task1] = $this->create_goals_with_tasks();

        $goal2_task1->set_completed(true);

        static::assertEquals([
            'total_goal_tasks_count' => 4,
            'completed_goal_tasks_count' => 1,
            'goals_with_at_least_one_task_count' => 3,
            'goals_with_at_least_one_task_resource_count' => 0,
            'total_task_resources_count' => 0,
        ], (new count_of_goal_tasks())->export());

        $goal1_task2->set_completed(true);

        static::assertEquals([
            'total_goal_tasks_count' => 4,
            'completed_goal_tasks_count' => 2,
            'goals_with_at_least_one_task_count' => 3,
            'goals_with_at_least_one_task_resource_count' => 0,
            'total_task_resources_count' => 0,
        ], (new count_of_goal_tasks())->export());

        $goal1_task1->set_completed(true);

        static::assertEquals([
            'total_goal_tasks_count' => 4,
            'completed_goal_tasks_count' => 3,
            'goals_with_at_least_one_task_count' => 3,
            'goals_with_at_least_one_task_resource_count' => 0,
            'total_task_resources_count' => 0,
        ], (new count_of_goal_tasks())->export());
    }

    public function test_task_resource_counts(): void {
        [$goal1_task1, $goal1_task2, $goal2_task1, $goal3_task1] = $this->create_goals_with_tasks();

        $course = static::getDataGenerator()->create_course();
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1_task1->get_goal()->id,
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        $goal_task_resource = goal_generator::instance()->create_goal_task_resource($goal1_task1, $goal_task_config);

        static::assertEquals([
            'total_goal_tasks_count' => 4,
            'completed_goal_tasks_count' => 0,
            'goals_with_at_least_one_task_count' => 3,
            'goals_with_at_least_one_task_resource_count' => 1,
            'total_task_resources_count' => 1,
            'resource_type_count_course_type' => 1,
        ], (new count_of_goal_tasks())->export());

        // Another task resource for the same goal.
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1_task2->get_goal()->id,
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        $goal_task_resource = goal_generator::instance()->create_goal_task_resource($goal1_task2, $goal_task_config);

        static::assertEquals([
            'total_goal_tasks_count' => 4,
            'completed_goal_tasks_count' => 0,
            'goals_with_at_least_one_task_count' => 3,
            'goals_with_at_least_one_task_resource_count' => 1,
            'total_task_resources_count' => 2,
            'resource_type_count_course_type' => 2,
        ], (new count_of_goal_tasks())->export());

        // Another task resource for a different goal.
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal2_task1->get_goal()->id,
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        $goal_task_resource = goal_generator::instance()->create_goal_task_resource($goal2_task1, $goal_task_config);

        static::assertEquals([
            'total_goal_tasks_count' => 4,
            'completed_goal_tasks_count' => 0,
            'goals_with_at_least_one_task_count' => 3,
            'goals_with_at_least_one_task_resource_count' => 2,
            'total_task_resources_count' => 3,
            'resource_type_count_course_type' => 3,
        ], (new count_of_goal_tasks())->export());
    }

    public function test_task_resource_counts_invalid_resource_type(): void {
        [$goal1_task1, $goal1_task2, $goal2_task1, $goal3_task1] = $this->create_goals_with_tasks();

        $course = static::getDataGenerator()->create_course();
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1_task1->get_goal()->id,
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        /** @var goal_task_resource_model $goal_task_resource1 */
        $goal_task_resource1 = goal_generator::instance()->create_goal_task_resource($goal1_task1, $goal_task_config);

        // Another task resource for the same goal.
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1_task2->get_goal()->id,
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        /** @var goal_task_resource_model $goal_task_resource2 */
        $goal_task_resource2 = goal_generator::instance()->create_goal_task_resource($goal1_task2, $goal_task_config);

        // Another task resource for a different goal.
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal2_task1->get_goal()->id,
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        $goal_task_resource3 = goal_generator::instance()->create_goal_task_resource($goal2_task1, $goal_task_config);

        // Set invalid type code for two resources.
        goal_task_resource::repository()
            ->where_in('id', [$goal_task_resource1->id, $goal_task_resource2->id])
            ->update(['resource_type' => -1]);

        static::assertEquals([
            'total_goal_tasks_count' => 4,
            'completed_goal_tasks_count' => 0,
            'goals_with_at_least_one_task_count' => 3,
            'goals_with_at_least_one_task_resource_count' => 2,
            'total_task_resources_count' => 3,
            'resource_type_count_course_type' => 1,
        ], (new count_of_goal_tasks())->export());
    }

    /**
     * @return array|goal_task[]
     * @throws coding_exception
     */
    private function create_goals_with_tasks(): array {
        static::setAdminUser();

        $generator = static::getDataGenerator();
        $goal_generator = goal_generator::instance();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();

        $config1 = goal_generator_config::new(['user_id' => $user1->id, 'id_number' => 'goal1']);
        $config2 = goal_generator_config::new(['user_id' => $user1->id, 'id_number' => 'goal2']);
        $config3 = goal_generator_config::new(['user_id' => $user2->id, 'id_number' => 'goal3']);
        $config4 = goal_generator_config::new(['user_id' => $user2->id, 'id_number' => 'goal4']);
        $goal1 = $goal_generator->create_goal($config1);
        $goal2 = $goal_generator->create_goal($config2);
        $goal3 = $goal_generator->create_goal($config3);
        $goal4 = $goal_generator->create_goal($config4);

        // Two tasks for goal1
        $goal_task_config = goal_task_generator_config::new(['goal_id' => $goal1->id]);
        $goal1_task1 = $goal_generator->create_goal_task($goal_task_config);
        $goal1_task2 = $goal_generator->create_goal_task($goal_task_config);

        // One task for goal2
        $goal_task_config = goal_task_generator_config::new(['goal_id' => $goal2->id]);
        $goal2_task1 = $goal_generator->create_goal_task($goal_task_config);

        // One task for goal3
        $goal_task_config = goal_task_generator_config::new(['goal_id' => $goal3->id]);
        $goal3_task1 = $goal_generator->create_goal_task($goal_task_config);

        return [$goal1_task1, $goal1_task2, $goal2_task1, $goal3_task1];
    }
}
