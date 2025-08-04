<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTDvs
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package totara_competency
 * @category test
 */

 use core_my\models\perform_overview\state;
 use core\pagination\cursor;
 use totara_competency\data_providers\perform_overview;
 use totara_competency\user_groups;

 require_once(__DIR__.'/perform_overview_testcase.php');

 /**
  * @group totara_competency
  * @group totara_competency_overview
  */
class totara_competency_perform_overview_data_provider_test
extends totara_competency_perform_overview_testcase {
    public function test_fetch() {
        [$test_data, $days_ago, $from_years_ago, $by_state] = $this->setup_env();

        foreach ($by_state as $state_value => $raw_expected) {
            $state = state::from_value($state_value);
            $results = perform_overview::create_by_state(
                $days_ago, $from_years_ago, $state, $test_data->user
            )->fetch();

            $this->assert_overview($raw_expected, $results, $test_data);
        }
    }

    public function test_fetch_paginated() {
        [$test_data, $days_ago, $from_years_ago, $by_state] = $this->setup_env();

        $state = state::progressed();
        $raw_expected = $by_state[$state->value];

        $page_size = 4;

        $provider = perform_overview::create_by_state(
            $days_ago, $from_years_ago, $state, $test_data->user
        )->set_page_size($page_size);

        // 1st round.
        [
            'items' => $items,
            'total' => $total,
            'next_cursor' => $enc_cursor
        ] = $provider->fetch_paginated();

        $this->assertCount($page_size, $items, 'wrong current page count');
        $this->assertEquals(count($raw_expected), $total, 'wrong total count');
        $this->assertNotEmpty($enc_cursor, 'empty cursor');

        // 2nd round.
        [
            'items' => $items,
            'total' => $total,
            'next_cursor' => $enc_cursor
        ] = $provider->fetch_paginated(cursor::decode($enc_cursor));

        $this->assertCount(1, $items, 'wrong current page count');
        $this->assertEquals(count($raw_expected), $total, 'wrong total count');
        $this->assertEmpty($enc_cursor, 'non empty cursor');
    }

    /**
     * Creates test data for testing this class.
     *
     * @return mixed[] [stdClass testdata, int days_ago, int from_years_ago,
     *         array<int,stdClass[]> mapping of overview state values to
     *         achievements meeting those states].
     */
    private function setup_env(): array {
        $comp1 = 'test_comp_1';
        $comp2 = 'test_comp_2';
        $comp3 = 'test_comp_3';

        $cohort1 = [user_groups::COHORT, 'test_cohort1'];
        $cohort2 = [user_groups::COHORT, 'test_cohort2'];
        $cohort3 = [user_groups::COHORT, 'test_cohort3'];
        $pos1 = [user_groups::POSITION, 'test_pos1'];
        $pos2 = [user_groups::POSITION, 'test_pos2'];
        $org1 = [user_groups::ORGANISATION, 'test_org1'];

        $rating_not_started = null;
        $rating_assigned = 'Assigned (AS)';
        $rating_stage1 = 'Stage1 (S1)';
        $rating_stage2 = 'Stage2 (S2)';
        $rating_stage3 = 'Stage3 (S3)';
        $rating_stage4 = 'Stage4 (S4)';
        $rating_fully_achieved = 'Complete (FA)';

        $scale = [
            [$rating_assigned, false],
            [$rating_stage1, false],
            [$rating_stage2, false],
            [$rating_stage3, false],
            [$rating_stage4, false],
            [$rating_fully_achieved, true]
        ];

        // Note $days_ago + X = even further than in the past than $days_ago
        // from today.
        $days_ago = 23;
        $from_years_ago = 3;

        $ach1 = (object) [
            'comp_name' => $comp1,
            'user_group' => $cohort1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage3],
                [$days_ago - 2, $rating_fully_achieved]
            ]
        ];

        $ach2 = (object) [
            'comp_name' => $comp1,
            'user_group' => $cohort2,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage3],
                [$days_ago + 2, $rating_stage4]
            ]
        ];

        $ach3 = (object) [
            'comp_name' => $comp1,
            'user_group' => $cohort3,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage3],
                [$days_ago - 5, $rating_stage4]
            ]
        ];

        $ach4 = (object) [
            'comp_name' => $comp1,
            'user_group' => $pos1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage3],
                [$days_ago - 5, $rating_stage4],
                [$days_ago - 1, $rating_not_started]
            ]
        ];

        $ach5 = (object) [
            'comp_name' => $comp1,
            'user_group' => $pos2,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage3],
                [$days_ago - 3, $rating_assigned]
            ]
        ];

        $ach6 = (object) [
            'comp_name' => $comp1,
            'user_group' => $org1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage3],
                [$days_ago + 1, $rating_fully_achieved]
            ]
        ];

        $ach7 = (object) [
            'comp_name' => $comp2,
            'user_group' => $cohort1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage3],
                [$days_ago - 10, $rating_fully_achieved]
            ]
        ];

        $ach8 = (object) [
            'comp_name' => $comp2,
            'user_group' => $cohort2,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage1],
                [$days_ago - 10, $rating_stage3]
            ]
        ];

        $ach9 = (object) [
            'comp_name' => $comp2,
            'user_group' => $cohort3,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_stage1]
            ]
        ];

        $ach10 = (object) [
            'comp_name' => $comp2,
            'user_group' => $pos1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago - 15, $rating_stage3]
            ]
        ];

        $ach11 = (object) [
            'comp_name' => $comp2,
            'user_group' => $pos2,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago - 5, $rating_stage3],
                [$days_ago - 15, $rating_assigned]
            ]
        ];

        $ach12 = (object) [
            'comp_name' => $comp2,
            'user_group' => $pos2,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_not_started]
            ]
        ];

        $ignored1 = (object) [
            'comp_name' => $comp3,
            'user_group' => $pos1,
            'times' => [
                [($from_years_ago * 365) + 1, $rating_not_started]
            ]
        ];

        $ignored2 = (object) [
            'comp_name' => $comp3,
            'user_group' => $pos2,
            'times' => [
                [($from_years_ago * 365) + 1, $rating_stage1]
            ]
        ];

        $states = [
            state::achieved()->value => [$ach1, $ach7],
            state::progressed()->value => [$ach3, $ach5, $ach8, $ach10, $ach11],
            state::not_progressed()->value => [$ach2, $ach9],
            state::not_started()->value => [$ach4, $ach12]
        ];

        $raw_achievements = [
            $ach1, $ach2, $ach3, $ach4, $ach5, $ach6, $ach7, $ach8, $ach9,
            $ach10, $ach11, $ach12,
            $ignored1, $ignored2
        ];

        $expected = [];
        foreach ($states as $state => $achievements) {
            $expected[$state] = array_map(
                function (stdClass $achievement): stdClass {
                    $times = $achievement->times;
                    [$latest_time, $latest_rating] = end($times);

                    return (object) [
                        'comp_name' => $achievement->comp_name,
                        'user_group' => $achievement->user_group,
                        'time' => $latest_time,
                        'rating' => $latest_rating
                    ];
                },
                $achievements
            );
        }

        $test_data = $this->create_test_data($raw_achievements, $scale, time());

        return [$test_data, $days_ago, $from_years_ago, $expected];
    }
}