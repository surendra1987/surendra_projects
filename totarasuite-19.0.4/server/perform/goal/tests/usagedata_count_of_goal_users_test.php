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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

use core\entity\user;
use core_phpunit\testcase;
use perform_goal\model\goal as goal_model;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\usagedata\count_of_goal_users;

defined('MOODLE_INTERNAL') || die();

/**
 * @group perform
 */
class perform_goal_usagedata_count_of_goal_users_test extends testcase {

    public function test_empty_result(): void {
        $pre_existing_user_count = user::repository()->where('deleted', 0)->count();

        static::assertEquals([
            'users_with_goals_count' => 0,
            'users_without_goals_count' => $pre_existing_user_count,
            'max_goals_for_a_user' => 0,
        ], (new count_of_goal_users())->export());
    }

    public function test_users_with_and_without_goals_count(): void {
        $generator = static::getDataGenerator();

        /** @var goal_model[] $goals */
        [
            $pre_existing_user_count,
            $users,
            $goals,
        ] = $this->create_goal_data();

        static::assertEquals([
            'users_with_goals_count' => 10,
            'users_without_goals_count' => $pre_existing_user_count,
            'max_goals_for_a_user' => 1,
        ], (new count_of_goal_users())->export());

        // Add some more users.
        $extra_user1 = $generator->create_user();
        $extra_user2 = $generator->create_user();

        static::assertEquals([
            'users_with_goals_count' => 10,
            'users_without_goals_count' => $pre_existing_user_count + 2,
            'max_goals_for_a_user' => 1,
        ], (new count_of_goal_users())->export());

        $this->add_goal_for_user($extra_user1);

        static::assertEquals([
            'users_with_goals_count' => 11,
            'users_without_goals_count' => $pre_existing_user_count + 1,
            'max_goals_for_a_user' => 1,
        ], (new count_of_goal_users())->export());

        // Delete some goals.
        $goals[1]->delete();
        $goals[2]->delete();
        $goals[3]->delete();

        static::assertEquals([
            'users_with_goals_count' => 8,
            'users_without_goals_count' => $pre_existing_user_count + 4,
            'max_goals_for_a_user' => 1,
        ], (new count_of_goal_users())->export());
    }

    public function test_max_goals_for_a_user(): void {
        [
            $pre_existing_user_count,
            $users,
            $goals,
        ] = $this->create_goal_data();

        static::assertEquals([
            'users_with_goals_count' => 10,
            'users_without_goals_count' => $pre_existing_user_count,
            'max_goals_for_a_user' => 1,
        ], (new count_of_goal_users())->export());

        // Add some goals.
        $this->add_goal_for_user($users[3]);
        $this->add_goal_for_user($users[3]);
        $this->add_goal_for_user($users[4]);

        static::assertEquals([
            'users_with_goals_count' => 10,
            'users_without_goals_count' => $pre_existing_user_count,
            'max_goals_for_a_user' => 3,
        ], (new count_of_goal_users())->export());

        // Add more goals.
        $this->add_goal_for_user($users[5]);
        $this->add_goal_for_user($users[5]);
        $this->add_goal_for_user($users[5]);
        $this->add_goal_for_user($users[6]);

        static::assertEquals([
            'users_with_goals_count' => 10,
            'users_without_goals_count' => $pre_existing_user_count,
            'max_goals_for_a_user' => 4,
        ], (new count_of_goal_users())->export());
    }

    private function add_goal_for_user(stdClass $user): void {
        $goal_generator = goal_generator::instance();
        $goal_config = goal_generator_config::new(['user_id' => $user->id, 'id_number' => 'added goal ' . uniqid()]);
        $goal_generator->create_goal($goal_config);
    }

    private function create_goal_data(): array {
        static::setAdminUser();
        $pre_existing_user_count = user::repository()->where('deleted', 0)->count();

        $generator = static::getDataGenerator();
        $goal_generator = goal_generator::instance();

        $users = [];
        $goals = [];
        for ($i = 0; $i < 10; $i++) {
            $users[$i] = $generator->create_user();
            $goal_config = goal_generator_config::new(['user_id' => $users[$i]->id, 'id_number' => 'goal' . $i]);
            $goals[$i] = $goal_generator->create_goal($goal_config);
        }

        return [
            $pre_existing_user_count,
            $users,
            $goals,
        ];
    }
}
