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

use core\collection;
use core_phpunit\testcase;
use core_my\models\perform_overview\state;
use perform_goal\entity\goal;
use perform_goal\data_provider\perform_overview;

require_once(__DIR__.'/perform_overview_helper.php');

/**
 * @group perform_goal
 */
class perform_goal_perform_overview_data_provider_test extends testcase {
    use perform_overview_helper;

    /**
     * Data provider for test_fetch_sorted().
     */
    public static function td_fetch_sorted(): array {
        return [
            'not started, ov=1yr, cutoff=2yr' => [state::not_started(), 365, 2],
            'not progressed, ov=6mth, cutoff=2yr' => [
                state::not_progressed(), 180, 2
            ],
            'progressed, ov=3mth, cutoff=2yr' => [state::progressed(), 90, 2],
            'achieved, ov=7days, cutoff=2yr' => [state::achieved(), 7, 2]
        ];
    }

    /**
     * Test perform goal overviews.
     *
     * @dataProvider td_fetch_sorted
     */
    public function test_fetch_sorted(
        state $state,
        int $days_ago,
        int $from_years_ago
    ): void {
        $now = time();
        $start = $now - $days_ago * DAYSECS;
        $cutoff = $now - $from_years_ago * YEARSECS;
        [$goals_by_state, $subject] = self::setup_env($start, $cutoff);

        // Sort by latest updated first, then name.
        $sort_by = collection::new([
            ['column' => perform_overview::SORT_UPDATED, 'direction' => 'DESC'],
            ['column' => perform_overview::SORT_NAME, 'direction' => 'ASC']
        ]);

        $exp_goals = $goals_by_state[$state->name]->sort(
            fn (goal $a, goal $b): int => $sort_by->reduce(
                function (int $equals, array $sort) use ($a, $b): int {
                    if ($equals !== 0) {
                        return $equals;
                    }

                    ['column' => $col, 'direction' => $direction] = $sort;
                    [$lhs, $rhs] = $direction === 'ASC'
                        ? [$a->$col, $b->$col]
                        : [$b->$col, $a->$col];

                    return $lhs <=> $rhs;
                },
                0
            )
        );

        $provider = new perform_overview(
            $days_ago, $from_years_ago, $state, $subject
        );
        $act_goals = $provider
            ->add_sort_by($sort_by->all())
            ->get_results();

        $this->assertEquals(
            $exp_goals->count(), $act_goals->count(), 'wrong count'
        );

        $this->assertEquals(
            $exp_goals->pluck('id'),
            $act_goals->pluck('id'),
            "wrong '{$state->name}' goals"
        );
    }

    public function test_fetch_paginated() {
        $now = time();
        $days_ago = 10;
        $from_years_ago = 1;
        [$goals_by_state, $subject] = self::setup_env(
            $now - $days_ago * DAYSECS, $now - $from_years_ago * YEARSECS
        );

        $state = state::progressed();
        $exp_goals = $goals_by_state[$state->name]
            ->sort(fn (goal $a, goal $b): int => $a->id <=> $b->id)
            ->pluck('id');
        $exp_total = count($exp_goals);

        $page_size = 1;
        self::assertGreaterThan(
            $page_size, $exp_total, '< 2 goals in progressed state'
        );

        $provider = new perform_overview(
            $days_ago, $from_years_ago, $state, $subject
        );

        $act_goals = [];
        $next_cursor = null;
        for ($i = 0; $i < $exp_total; $i++) {
            $result = $provider->get_page_results($next_cursor, $page_size);
            $this->assertEquals($exp_total, $result->total, 'wrong total count');

            $next_cursor = $result->next_cursor;
            $i !== $exp_total - 1
                ? $this->assertNotEmpty($next_cursor, 'empty cursor')
                : $this->assertEmpty($next_cursor, 'non empty cursor');

            $goal_ids = $result->items->pluck('id');
            $this->assertCount($page_size, $goal_ids, 'wrong current page count');
            $act_goals = array_merge($act_goals, $goal_ids);
        }

        self::assertEquals($exp_goals, $act_goals, 'wrong goals retrieved');
    }
}
