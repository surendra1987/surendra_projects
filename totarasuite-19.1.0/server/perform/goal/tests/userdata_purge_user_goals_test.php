<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
use totara_userdata\userdata\item;
use totara_userdata\userdata\target_user;
use perform_goal\settings_helper;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_activity as goal_activity_entity;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_activity_generator_config;
use perform_goal\testing\goal_generator_config;
use perform_goal\userdata\purge_user_goals;
use perform_goal\testing\goal_task_generator_config;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\entity\goal_task as goal_task_entity;
use perform_goal\entity\goal_task_resource as goal_task_resource_entity;

class perform_goal_userdata_purge_user_goals_test extends testcase {
    public function test_purge_user_goals() {

        $fs = get_file_storage();
        $subject1 = self::getDataGenerator()->create_user();
        $subject2 = self::getDataGenerator()->create_user();
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $goal_test_config = goal_generator_config::new([
            'context' => context_user::instance($subject1->id),
            'name' => 'Test goal 1',
            'owner_id' => $owner->id,
            'user_id' => $subject1->id,
            'id_number' => 'goal_1',
            'description' => 'Test goal 1 description',
        ]);
        $goal1 = goal_generator::instance()->create_goal($goal_test_config);
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1->id,
            'description' => "Just task 1 goal 1, no resource"
        ]);
        $goal_task1 = goal_generator::instance()->create_goal_task($goal_task_config);
        $course = $this->getDataGenerator()->create_course();
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1->id,
            'description' => "Task 2 goal 1 with resource",
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        $goal_task2 = goal_generator::instance()->create_goal_task($goal_task_config);
        goal_generator::instance()->create_goal_task_resource($goal_task2, $goal_task_config);

        $file = [
            'component' => settings_helper::get_component(),
            'filearea' => settings_helper::get_filearea(),
            'filepath' => '/',
            'filename' => 'test.txt'
        ];
        $subject1_file = $fs->create_file_from_string(
            array_merge(
                $file,
                [
                    'contextid' => context_user::instance($subject1->id)->id,
                    'itemid' => $goal1->id,
                ]
            ),
            $goal_test_config->description
        );

        // Goal 2
        $goal_test_config = goal_generator_config::new([
            'context' => context_user::instance($subject1->id),
            'name' => 'Test goal 2',
            'owner_id' => $owner->id,
            'user_id' => $subject1->id,
            'id_number' => 'goal_2',
            'description' => 'Test goal 2 description',
        ]);
        $goal2 = goal_generator::instance()->create_goal($goal_test_config);

        // Goal 3
        $goal_test_config = goal_generator_config::new([
            'context' => context_user::instance($subject2->id),
            'name' => 'Test goal 3',
            'owner_id' => $owner->id,
            'user_id' => $subject2->id,
            'id_number' => 'goal_3',
            'description' => 'Test goal 3 description',
        ]);
        $goal3 = goal_generator::instance()->create_goal($goal_test_config);
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal3->id,
            'description' => "Just task 1 goal 3, no resource"
        ]);
        $goal_task1 = goal_generator::instance()->create_goal_task($goal_task_config);
        $course = $this->getDataGenerator()->create_course();
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal3->id,
            'description' => "Task 2 goal 3 with resource",
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        $goal_task2 = goal_generator::instance()->create_goal_task($goal_task_config);
        goal_generator::instance()->create_goal_task_resource($goal_task2, $goal_task_config);

        $subject2_file = $fs->create_file_from_string(
            array_merge(
                $file,
                [
                    'contextid' => context_user::instance($subject2->id)->id,
                    'itemid' => $goal3->id,
                ]
            ),
            $goal_test_config->description
        );

        // Goal activity 1
        $goal_activity_test_config = goal_activity_generator_config::new([
            'goal_id' => $goal1->id,
            'user_id' => $subject1->id,
            'activity_type' => 'type1',
            'activity_info' => 'info1'
        ]);
        $goal1_activity1 = goal_generator::instance()->create_goal_activity($goal_activity_test_config);

        // Goal activity 2
        $goal_activity_test_config = goal_activity_generator_config::new([
            'goal_id' => $goal1->id,
            'user_id' => $subject1->id,
            'activity_type' => 'type2',
            'activity_info' => 'info2'
        ]);
        $goal1_activity2 = goal_generator::instance()->create_goal_activity($goal_activity_test_config);

        // Goal activity 3
        $goal_activity_test_config = goal_activity_generator_config::new([
            'goal_id' => $goal2->id,
            'user_id' => $subject1->id,
            'activity_type' => 'type3',
            'activity_info' => 'info3'
        ]);
        $goal2_activity1 = goal_generator::instance()->create_goal_activity($goal_activity_test_config);

        // Goal activity 4
        $goal_activity_test_config = goal_activity_generator_config::new([
            'goal_id' => $goal3->id,
            'user_id' => $subject2->id,
            'activity_type' => 'type4',
            'activity_info' => 'info4'
        ]);
        $goal3_activity1 = goal_generator::instance()->create_goal_activity($goal_activity_test_config);

        /**
         * Note the expected pre purge counts:
         * - subject users = 2
         * - goals = 3 (two is for subject user 1 and one for subject user 2)
         * - goal activities = 4 (three is for subject user 1 and one for subject user 2)
         * - file = 2 (one is for subject user 1 and one for subject user 2)
         */
        $this->assertEquals(3, (goal_entity::repository())->count());
        $this->assertEquals(4, (goal_activity_entity::repository())->count());
        $this->assertEquals(4, (goal_task_entity::repository())->count());
        $this->assertEquals(2, (goal_task_resource_entity::repository())->count());

        $this->assertTrue($fs->file_exists_by_hash($subject1_file->get_pathnamehash()));
        $this->assertTrue($fs->file_exists_by_hash($subject2_file->get_pathnamehash()));

        // Execute userdata purge
        $targetuser1 = new target_user($subject1);
        $status = purge_user_goals::execute_purge($targetuser1, context_user::instance($subject1->id));

        // Test
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);
        $this->assertEquals(1, (goal_entity::repository())->count());
        $this->assertEquals(1, (goal_activity_entity::repository())->count());
        $this->assertEquals(2, (goal_task_entity::repository())->count());
        $this->assertEquals(1, (goal_task_resource_entity::repository())->count());
        $this->assertFalse($fs->file_exists_by_hash($subject1_file->get_pathnamehash()));
        $this->assertTrue($fs->file_exists_by_hash($subject2_file->get_pathnamehash()));
    }
}
