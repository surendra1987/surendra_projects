<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\goal as goal_model;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\usagedata\count_of_goals_by_status;

defined('MOODLE_INTERNAL') || die();

/**
 * @group perform
 */
class perform_goal_usagedata_count_of_goals_by_status_test extends testcase {

    public function test_empty_result(): void {
        static::assertEquals([
            'total_goals_count' => 0,
            'goals_recently_updated_count' => 0,
            'status_recently_updated_count' => 0,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 0,
            'status_count_not_started' => 0,
        ], (new count_of_goals_by_status())->export());
    }

    public function test_status_counts_fresh_goals(): void {
        $this->create_goal_data();

        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 4,
            'status_recently_updated_count' => 4,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 0,
            'status_count_not_started' => 4,
        ], (new count_of_goals_by_status())->export());
    }

    public function test_status_counts_for_all_status(): void {
        [$goal1, $goal2, $goal3, $goal4] = $this->create_goal_data();

        /** @var goal_model $goal1 */
        $goal1->update_progress('in_progress', 1);

        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 4,
            'status_recently_updated_count' => 4,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 1,
            'status_count_not_started' => 3,
        ], (new count_of_goals_by_status())->export());

        /** @var goal_model $goal2 */
        $goal2->update_progress('completed', 1);
        /** @var goal_model $goal3 */
        $goal3->update_progress('cancelled', 1);

        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 4,
            'status_recently_updated_count' => 4,
            'status_count_cancelled' => 1,
            'status_count_completed' => 1,
            'status_count_in_progress' => 1,
            'status_count_not_started' => 1,
        ], (new count_of_goals_by_status())->export());

        /** @var goal_model $goal4 */
        $goal4->update_progress('cancelled', 1);
        $goal2->update_progress('cancelled', 1);

        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 4,
            'status_recently_updated_count' => 4,
            'status_count_cancelled' => 3,
            'status_count_completed' => 0,
            'status_count_in_progress' => 1,
            'status_count_not_started' => 0,
        ], (new count_of_goals_by_status())->export());
    }

    public function test_status_recently_updated_counts(): void {
        [$goal1, $goal2, $goal3, $goal4] = $this->create_goal_data();

        $thirty_two_days_ago = time() - 32 * DAYSECS;
        $twenty_eight_days_ago = time() - 28 * DAYSECS;

        goal_entity::repository()
            ->where_in('id', [$goal1->id, $goal2->id, $goal3->id, $goal3->id])
            ->update(['status_updated_at' => $twenty_eight_days_ago]);

        // Should still count as 'recently updated' (limit is 30 days ago).
        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 4,
            'status_recently_updated_count' => 4,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 0,
            'status_count_not_started' => 4,
        ], (new count_of_goals_by_status())->export());

        goal_entity::repository()
            ->where_in('id', [$goal1->id, $goal3->id])
            ->update(['status_updated_at' => $thirty_two_days_ago]);

        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 4,
            'status_recently_updated_count' => 2,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 0,
            'status_count_not_started' => 4,
        ], (new count_of_goals_by_status())->export());


        goal_entity::repository()
            ->where_in('id', [$goal2->id, $goal4->id])
            ->update(['status_updated_at' => $thirty_two_days_ago]);

        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 4,
            'status_recently_updated_count' => 0,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 0,
            'status_count_not_started' => 4,
        ], (new count_of_goals_by_status())->export());
    }

    public function test_recently_updated_counts(): void {
        [$goal1, $goal2, $goal3, $goal4] = $this->create_goal_data();

        $thirty_two_days_ago = time() - 32 * DAYSECS;
        $twenty_eight_days_ago = time() - 28 * DAYSECS;

        goal_entity::repository()
            ->where_in('id', [$goal1->id, $goal2->id, $goal3->id, $goal3->id])
            ->update(['updated_at' => $twenty_eight_days_ago]);

        // Should still count as 'recently updated' (limit is 30 days ago).
        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 4,
            'status_recently_updated_count' => 4,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 0,
            'status_count_not_started' => 4,
        ], (new count_of_goals_by_status())->export());

        goal_entity::repository()
            ->where('id', $goal1->id)
            ->update(['updated_at' => $thirty_two_days_ago]);

        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 3,
            'status_recently_updated_count' => 4,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 0,
            'status_count_not_started' => 4,
        ], (new count_of_goals_by_status())->export());


        goal_entity::repository()
            ->where_in('id', [$goal2->id, $goal3->id, $goal4->id])
            ->update(['updated_at' => $thirty_two_days_ago]);

        static::assertEquals([
            'total_goals_count' => 4,
            'goals_recently_updated_count' => 0,
            'status_recently_updated_count' => 4,
            'status_count_cancelled' => 0,
            'status_count_completed' => 0,
            'status_count_in_progress' => 0,
            'status_count_not_started' => 4,
        ], (new count_of_goals_by_status())->export());
    }

    private function create_goal_data(): array {
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

        return [$goal1, $goal2, $goal3, $goal4];
    }
}
