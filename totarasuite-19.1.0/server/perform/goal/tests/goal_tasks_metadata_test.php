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
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\model\goal_tasks_metadata;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\testing\goal_task_generator_config;

/**
 * @group perform_goal
 */
class perform_goal_goal_tasks_metadata_test extends testcase {

    public function test_construct(): void {
        self::setAdminUser();
        $goal_generator = goal_generator::instance();

        $subject_user = self::getDataGenerator()->create_user();

        $goal1 = $goal_generator->create_goal(
            goal_generator_config::new([
                'user_id' => $subject_user->id,
                'context' => context_user::instance($subject_user->id),
                'name' => 'Test goal 1',
            ])
        );

        $goal2 = $goal_generator->create_goal(
            goal_generator_config::new([
                'user_id' => $subject_user->id,
                'context' => context_user::instance($subject_user->id),
                'name' => 'Test goal 2',
            ])
        );

        $goal1_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1->id,
            'description' => 'test task'
        ]);
        $goal2_task_config = goal_task_generator_config::new([
            'goal_id' => $goal2->id,
            'description' => 'test task'
        ]);
        $task1 = $goal_generator->create_goal_task($goal1_task_config);
        $task2 = $goal_generator->create_goal_task($goal1_task_config);
        $task3 = $goal_generator->create_goal_task($goal1_task_config);

        $task4 = $goal_generator->create_goal_task($goal2_task_config);
        $task5 = $goal_generator->create_goal_task($goal2_task_config);

        $goal1_tasks_metadata = new goal_tasks_metadata($goal1);
        $goal2_tasks_metadata = new goal_tasks_metadata($goal2);

        self::assertSame($goal1->id, $goal1_tasks_metadata->get_goal_id());
        self::assertSame($goal2->id, $goal2_tasks_metadata->get_goal_id());

        self::assertSame(3, $goal1_tasks_metadata->get_total_count());
        self::assertSame(0, $goal1_tasks_metadata->get_completed_count());
        self::assertSame(2, $goal2_tasks_metadata->get_total_count());
        self::assertSame(0, $goal2_tasks_metadata->get_completed_count());

        $task1->set_completed(true);
        $task3->set_completed(true);
        $task5->set_completed(true);

        $goal1->refresh(true);
        $goal2->refresh(true);

        $goal1_tasks_metadata = new goal_tasks_metadata($goal1);
        $goal2_tasks_metadata = new goal_tasks_metadata($goal2);

        self::assertSame(3, $goal1_tasks_metadata->get_total_count());
        self::assertSame(2, $goal1_tasks_metadata->get_completed_count());
        self::assertSame(2, $goal2_tasks_metadata->get_total_count());
        self::assertSame(1, $goal2_tasks_metadata->get_completed_count());
    }
}
