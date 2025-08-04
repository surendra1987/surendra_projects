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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\usagedata\count_of_goal_comments;

defined('MOODLE_INTERNAL') || die();

/**
 * @group perform
 */
class perform_goal_usagedata_count_of_goal_comments_test extends testcase {

    public function test_goal_counts(): void {
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        self::setUser($user1);

        $goal_generator = goal_generator::instance();

        $data = goal_generator_config::new(['user_id' => $user1->id, 'name' => 'Goal 1']);
        $goal1 = goal_generator::instance()->create_goal($data);

        $data = goal_generator_config::new(['user_id' => $user1->id, 'name' => 'Goal 2']);
        $goal2 = goal_generator::instance()->create_goal($data);

        $data = goal_generator_config::new(['user_id' => $user2->id, 'name' => 'Goal 3']);
        $goal3 = goal_generator::instance()->create_goal($data);

        $comment1_goal1 = $goal_generator->create_goal_comment($user1->id, $goal1->id);
        $comment2_goal1 = $goal_generator->create_goal_comment($user1->id, $goal1->id);

        $goal_generator->create_goal_comment($user1->id, $goal2->id);
        $goal_generator->create_goal_comment($user1->id, $goal2->id);

        $goal_generator->create_goal_comment($user2->id, $goal3->id);
        $goal_generator->create_goal_comment($user2->id, $goal3->id);

        $result = (new count_of_goal_comments())->export();
        static::assertEquals(6, $result['total_goal_comments_count']);

        // Delete goal comment 1
        $comment1_goal1->delete();
        $result = (new count_of_goal_comments())->export();
        static::assertEquals(5, $result['total_goal_comments_count']);

        // Check that a goal comment with 'timedeleted' set is excluded.
        $comment2_goal1->soft_delete();
        $result = (new count_of_goal_comments())->export();
        static::assertEquals(4, $result['total_goal_comments_count']);
    }
}
