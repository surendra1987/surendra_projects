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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\orm\query\builder;
use core_my\models\perform_overview\state;
use core_phpunit\testcase;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_repository;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;

require_once(__DIR__.'/perform_overview_helper.php');

/**
 * @group perform_goal
 */
class perform_goal_goal_repository_test extends testcase {
    use perform_overview_helper;

    /**
     * Data provider for test_overview().
     */
    public static function td_not_started(): array {
        $state = state::not_started();

        return [
            'not started, ov=1yr, cutoff=2yr' => [$state, 365, 2],
            'not started, ov=6mth, cutoff=2yr' => [$state, 180, 2],
            'not started, ov=3mth, cutoff=2yr' => [$state, 90, 2],
            'not started, ov=7days, cutoff=2yr' => [$state, 7, 2]
        ];
    }

    /**
     * Data provider for test_overview().
     */
    public static function td_not_progressed(): array {
        $state = state::not_progressed();

        return [
            'not progressed, ov=1yr, cutoff=2yr' => [$state, 365, 2],
            'not progressed, ov=6mth, cutoff=2yr' => [$state, 180, 2],
            'not progressed, ov=3mth, cutoff=2yr' => [$state, 90, 2],
            'not progressed, ov=7days, cutoff=2yr' => [$state, 7, 2]
        ];
    }

    /**
     * Data provider for test_overview().
     */
    public static function td_progressed(): array {
        $state = state::progressed();

        return [
            'progressed, ov=1yr, cutoff=2yr' => [$state, 365, 2],
            'progressed, ov=6mth, cutoff=2yr' => [$state, 180, 2],
            'progressed, ov=3mth, cutoff=2yr' => [$state, 90, 2],
            'progressed, ov=7days, cutoff=2yr' => [$state, 7, 2]
        ];
    }

    /**
     * Data provider for test_overview().
     */
    public static function td_achieved(): array {
        $state = state::achieved();

        return [
            'achieved, ov=1yr, cutoff=2yr' => [$state, 365, 2],
            'achieved, ov=6mth, cutoff=2yr' => [$state, 180, 2],
            'achieved, ov=3mth, cutoff=2yr' => [$state, 90, 2],
            'achieved, ov=7days, cutoff=2yr' => [$state, 7, 2]
        ];
    }

    /**
     * Test perform goal overviews.
     *
     * @dataProvider td_not_started
     * @dataProvider td_not_progressed
     * @dataProvider td_progressed
     * @dataProvider td_achieved
     */
    public function test_overview(
        state $state,
        int $days_ago,
        int $from_years_ago
    ): void {
        $now = time();
        $start = $now - $days_ago * DAYSECS;
        $cutoff = $now - $from_years_ago * YEARSECS;
        [$goals_by_state, $subject] = self::setup_env($start, $cutoff);

        $exp_goals = $goals_by_state[$state->name];

        $act_goals = goal_repository::personal_goal_overview_for(
            $days_ago, $from_years_ago, $state, $subject
        )->get();

        // Uncomment to do debugging.
        //self::print($start, $cutoff, $subject, $state, $exp_goals, $act_goals);

        $this->assertEquals(
            $exp_goals->count(), $act_goals->count(), 'wrong count'
        );

        $this->assertEqualsCanonicalizing(
            $exp_goals->pluck('id'),
            $act_goals->pluck('id'),
            "wrong '{$state->name}' goals"
        );
    }

    public function test_add_select_completion_percent(): void {
        self::setAdminUser();

        $subject_user1 = self::getDataGenerator()->create_user();

        // Create goals
        foreach ([
            [1000, 234],
            [0.5, 0.025],
            [0, 0.025],
            [100, 12.236],
            [100, 110],
        ] as $target_and_current_value) {
            goal_generator::instance()->create_goal(
                goal_generator_config::new([
                    'user_id' => $subject_user1->id,
                    'context' => context_user::instance($subject_user1->id),
                    'target_value' => $target_and_current_value[0],
                    'current_value' => $target_and_current_value[1],
                ])
            );
        }

        /** @var goal_repository $repository */
        $repository = goal_entity::repository();

        $actual_result = $repository
            ->add_select_completion_percent()
            ->order_by('id')
            ->get()
            ->pluck('completion_percent');

        // Results are rounded to two decimal places
        if (builder::get_db() instanceof sqlsrv_native_moodle_database) {
            // Slight difference for sqlsrv: leading zero is dropped.
            $expected_result = ['23.40', '5.00', '.00', '12.24', '110.00'];
        } else {
            $expected_result = ['23.40', '5.00', '0.00', '12.24', '110.00'];
        }

        $this->assertSame(
            $expected_result,
            $actual_result
        );
    }

    public function test_with_comment_count(): void {
        self::setAdminUser();

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $goal_generator = goal_generator::instance();

        // Create three goals for user 1
        $goal1 = $goal_generator->create_goal(goal_generator_config::new([
            'user_id' => $user1->id,
            'context' => context_user::instance($user1->id),
        ]));
        $goal_generator->create_goal_comment($user1->id, $goal1->id);
        $goal_generator->create_goal_comment($user1->id, $goal1->id);
        $goal_generator->create_goal_comment($user1->id, $goal1->id);

        $goal2 = $goal_generator->create_goal(goal_generator_config::new([
            'user_id' => $user1->id,
            'context' => context_user::instance($user1->id),
        ]));
        $comment = $goal_generator->create_goal_comment($user1->id, $goal2->id);

        $goal3 = $goal_generator->create_goal(goal_generator_config::new([
            'user_id' => $user1->id,
            'context' => context_user::instance($user1->id),
        ]));

        // Create a goal for user 2
        $goal4 = $goal_generator->create_goal(goal_generator_config::new([
            'user_id' => $user2->id,
            'context' => context_user::instance($user2->id),
        ]));
        $goal_generator->create_goal_comment($user2->id, $goal4->id);
        $goal_generator->create_goal_comment($user2->id, $goal4->id);

        // Create a comment for unrelated component. It should not be in the count.
        $goal_generator->create_goal_comment($user2->id, $goal4->id, ['component' => 'unrelated']);

        // Create a comment for unrelated area. It should not be in the count.
        $goal_generator->create_goal_comment($user2->id, $goal4->id, ['area' => 'unrelated']);

        /** @var goal_repository $repository */
        $repository = goal_entity::repository();
        $actual_result = $repository
            ->with('comments')
            ->order_by('id')
            ->get();

        $this->assertEquals(
            [3, 1, 0, 2],
            $actual_result->map(fn(goal_entity $goal) => $goal->comments->count())->all()
        );
    }
}
