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

 use core\collection;
 use core_my\models\perform_overview\state;
 use totara_competency\entity\competency_achievement_repository;
 use totara_competency\user_groups;

 require_once(__DIR__.'/perform_overview_testcase.php');

 /**
  * For the scenarios listed in each scenario_set_X() method, assume:
  * - An achievement with a null scale value means the achievement is 'unrated';
  *   this is always taken as the user ‘has not started’ on the competency.
  * - It could be that an achievement with the lowest scale value also means 'not
  *   started' in the site's domain. This is configured via a 'lowest scale value
  *   is not started' setting in the parent scale.
  *   - This is why there are scenarios which explicitly test achievements with
  *     the lowest scale value.
  * - 'TS' is a timestamp in the past indicating the start of an overview period.
  *   In other words, the overview period is from 'TS' (calculated as from days,
  *   months, etc ago) till today.
  * - T0, T1, etc are times in the past in which the competency achievement scale
  *   value changed and become recorded as the achievement history.
  * - T0...Tn means the state was updated multiple times at times T0, T1 to Tn.
  * - Immediately below the time points in the diagrams are the associated scale
  *   values.
  */
abstract class totara_competency_perform_overview_repository_testcase
extends totara_competency_perform_overview_testcase {
    /**
     * Data provider for test_overview().
     */
    public static function td_overview(): array {
        $state = static::overview_state_under_test();

        return [
            '[lowest != not started]/set 0' => self::scenario_set_0($state, false),
            '[lowest != not started]/set 1' => self::scenario_set_1($state, false),
            '[lowest != not started]/set 2' => self::scenario_set_2($state, false),
            '[lowest != not started]/set 3' => self::scenario_set_3($state, false),
            '[lowest != not started]/set 4' => self::scenario_set_4($state, false),

            '[lowest = not started]/set 0' => self::scenario_set_0($state, true),
            '[lowest = not started]/set 1' => self::scenario_set_1($state, true),
            '[lowest = not started]/set 2' => self::scenario_set_2($state, true),
            '[lowest = not started]/set 3' => self::scenario_set_3($state, true),
            '[lowest = not started]/set 4' => self::scenario_set_4($state, true)
        ];
    }

    /**
     * Test competency overviews.
     *
     * @dataProvider td_overview
     */
    public function test_overview(
        array $raw_expected,
        array $raw_achievements,
        array $raw_scale,
        state $state,
        int $days_ago,
        int $from_years_ago,
        bool $lowest_scale_value_is_not_started
    ): void {
        $test_data = $this->create_test_data(
            $raw_achievements,
            $raw_scale,
            time(),
            $lowest_scale_value_is_not_started
        );

        $repo = competency_achievement_repository::overview_for(
            $days_ago, $from_years_ago, $state, collection::new([$test_data->user])
        );

        $this->assert_overview($raw_expected, $repo->get(), $test_data);
    }

    /**
     * Indicates the overview state being tested.
     *
     * @return state the test state.
     */
    protected static function overview_state_under_test(): state {
        throw new \coding_exception('overview_state_under_test must be overridden to return a valid state');
    }

    /**
     * Scenario set 1: no achievements before or during the overview period
     *
     * Requirements:
     * - state = <ignored> because there are no achievements at all.
     *
     * @param state $expected_state to calculate the expected achievements the
     *        repository should return.
     * @param $lowest_scale_value_is_not_started sets the lowest is not started
     *        value for the created competency scale.
     *
     * @return mixed[] a [expected, scenarios, scale, overview state, days_ago,
     *         from years ago, lowest scale value is not started] tuple to feed
     *         into self::overview_test().
     */
    private static function scenario_set_0(
        state $expected_state, bool $lowest_scale_value_is_not_started
    ): array {
        $scale = [
            ['Not competent', false],
            ['Competent', true]
        ];

        $days_ago = 7;
        $from_years_ago = 2;

        return [
            [],
            [],
            $scale,
            $expected_state,
            $days_ago,
            $from_years_ago,
            $lowest_scale_value_is_not_started
        ];
    }

    /**
     * Scenario set 1: proficient within overview period.
     *
     * Requirements:
     * - Latest achievement must be proficient within overview period.
     *
     * @param state $expected_state state to use to compute expected achievements
     *        the repository should return.
     * @param $lowest_scale_value_is_not_started sets the lowest is not started
     *        value for the created competency scale.
     *
     * @return mixed[] a [expected, scenarios, scale, overview state, days_ago,
     *         from years ago, lowest scale value is not started] tuple to feed
     *         into self::overview_test().
     */
    private static function scenario_set_1(
        state $expected_state, bool $lowest_scale_value_is_not_started
    ): array {
        $comp1 = 'test_sc1_1';
        $comp2 = 'test_sc1_2';

        $user_group1 = [user_groups::COHORT, 'sc1_cohort'];
        $user_group2 = [user_groups::POSITION, 'sc1_pos'];
        $user_group3 = [user_groups::ORGANISATION, 'sc1_org'];

        $rating_not_started = null; // NL
        $rating_assigned = 'Assigned (AS)';
        $rating_progressed = 'Progressing (PR)';
        $rating_partially_achieved = 'Almost (PA)';
        $rating_fully_achieved = 'Complete (FA)';

        $scale = [
            [$rating_assigned, false],
            [$rating_progressed, false],
            [$rating_partially_achieved, true],
            [$rating_fully_achieved, true]
        ];

        // 'TS' in diagrams below. Note $days_ago + X = even further than in the
        // past than $days_ago from today.
        $days_ago = 23;
        $from_years_ago = 2;

        // Scenario 1a
        //    Far past   T0...T2   TS     T3    Today
        //       |       NL...PA   |      PA      |
        //
        // state = 'ACHIEVED'; latest achievement (T3) in overview period is
        // proficient.
        // - This is only possible if an achievement is directly created in the
        //   table. In a normal case, the system creates new achievement records
        //   only if a scale value changed.
        $scn_1a = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_partially_achieved],
                [$days_ago - 2, $rating_partially_achieved]
            ]
        ];

        // Scenario 1b
        //    Far past   T0...T2   TS     T3    Today
        //       |       NL...PA   |      FA      |
        //
        // state = 'ACHIEVED'; latest achievement (T3) proficient in overview
        // period.
        // - It is not 'PROGRESSED' even though the scale value has changed.
        $scn_1b = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group2,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 5, $rating_partially_achieved],
                [$days_ago - 1, $rating_fully_achieved]
            ]
        ];

        // Scenario 1c
        //    Far past    T0    TS     T1       Today
        //       |        NL    |      PA         |
        //
        // state = 'ACHIEVED'; latest achievement (T1) proficient in overview
        // period.
        $scn_1c = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group3,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago - 3, $rating_partially_achieved]
            ]
        ];

        // Scenario 1d
        //    Far past   TS     T0...T2  Today
        //       |       |      NL...PA    |
        //
        // state = 'ACHIEVED'; latest achievement (T2) proficient in overview
        // period.
        $scn_1d = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group1,
            'times' => [
                [$days_ago - 3, $rating_not_started],
                [$days_ago - 4, $rating_progressed],
                [$days_ago - 5, $rating_partially_achieved]
            ]
        ];

        // Scenario 1e
        //    Far past   TS   T0  T1  T2  Today
        //       |       |    NL  PA  PR    |
        //
        // state = ‘PROGRESSED’; latest achievement (T2) scale value != null AND
        // != lowest value if lowest scale value is not started = true
        // - the fact there was a proficient achievement in this period does not
        //   matter because it was not the latest.
        $scn_1e = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group2,
            'times' => [
                [$days_ago - 1, $rating_not_started],
                [$days_ago - 2, $rating_partially_achieved],
                [$days_ago - 4, $rating_progressed]
            ]
        ];

        // Scenario 1f
        //    Far past   T0   T1  TS  T2  Today
        //       |       NL...FA  |        |
        //
        // state = <ignored>; no proficient achievements within overview period
        // - It is not even ‘NOT PROGRESSED’ because the achievement has already
        // reached proficiency.
        $scn_1f = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group3,
            'times' => [
                [$days_ago + 5, $rating_not_started],
                [$days_ago + 1, $rating_fully_achieved]
            ]
        ];

        $states = [
            state::achieved()->value => [$scn_1a, $scn_1b, $scn_1c, $scn_1d],
            state::progressed()->value => [$scn_1e],
            state::not_progressed()->value => [],
            state::not_started()->value => []
        ];

        $expected = array_map(
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
            $states[$expected_state->value]
        );

        return [
            $expected,
            [$scn_1a, $scn_1b, $scn_1c, $scn_1d, $scn_1e, $scn_1f],
            $scale,
            $expected_state,
            $days_ago,
            $from_years_ago,
            $lowest_scale_value_is_not_started
        ];
    }

    /**
     * Scenario set 2: achievements exist before overview period but not within.
     *
     * Requirements:
     * - No achievements during overview period => overview state cannot be
     *  'PROGRESSED' or 'ACHIEVED'
     *
     * @param state $expected_state state to use to compute expected achievements
     *        the repository should return.
     * @param $lowest_scale_value_is_not_started sets the lowest is not started
     *        value for the created competency scale.
     *
     * @return mixed[] a [expected, scenarios, scale, overview state, days_ago,
     *         from years ago, lowest scale value is not started] tuple to feed
     *         into self::overview_test().
     */
    private static function scenario_set_2(
        state $expected_state, bool $lowest_scale_value_is_not_started
    ): array {
        $comp1 = 'test_sc2_1';
        $comp2 = 'test_sc2_2';
        $comp3 = 'test_sc2_3';

        $user_group1 = [user_groups::COHORT, 'sc2_cohort'];
        $user_group2 = [user_groups::POSITION, 'sc2_pos'];

        $rating_not_started = null;
        $rating_assigned = 'Assigned (AS)';
        $rating_stage1 = 'Stage1 (S1)';
        $rating_stage2 = 'Stage2 (S2)';
        $rating_stage3 = 'Stage3 (S3)';
        $rating_stage4 = 'Stage4 (S4)';
        $rating_partially_achieved = 'Almost (PA)';
        $rating_fully_achieved = 'Complete (FA)';

        $scale = [
            [$rating_assigned, false],
            [$rating_stage1, false],
            [$rating_stage2, false],
            [$rating_stage3, false],
            [$rating_stage4, false],
            [$rating_partially_achieved, true],
            [$rating_fully_achieved, true]
        ];

        // 'TS' in diagrams below. Note $days_ago + X = even further than in the
        // past than $days_ago from today.
        $days_ago = 64;
        $from_years_ago = 2;

        // Scenario 2a
        //    Far past   T0...T2   TS       Today
        //       |       NL...S2   |          |
        //
        // state = ‘NOT PROGRESSED’; latest achievement before overview period
        // (T2) scale value != null AND != lowest value if lowest scale value is
        // not started = true
        $scn_2a = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 2, $rating_stage2]
            ]
        ];

        // Scenario 2b
        //    Far past   T0        TS       Today
        //       |       NL        |          |
        //
        // state = ‘NOT STARTED; latest achievement before overview period (T0)
        // scale value = null
        $scn_2b = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group2,
            'times' => [
                [$days_ago + 10, $rating_not_started]
            ]
        ];

        // Scenario 2c
        //    Far past   T0...T2  TS       Today
        //       |       NL...AS  |          |
        //
        // Looking at latest achievement before overview period (T2), state is
        // either:
        // - 'NOT PROGRESSED'; scale value != proficient AND scale value = lowest
        //   value AND lowest scale value is not started = false
        // - 'NOT STARTED'; scale value != proficient AND scale value = lowest
        //   value and AND lowest scale value is not started = true
        $scn_2c = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 2, $rating_assigned]
            ]
        ];

        // Scenario 2d
        //    Far past   T0...T2   TS       Today
        //       |       NL...NL   |          |
        //
        // state = ‘NOT STARTED; latest achievement before overview period (T2)
        // scale value = null
        $scn_2d = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group2,
            'times' => [
                [$days_ago + 15, $rating_not_started],
                [$days_ago + 10, $rating_assigned],
                [$days_ago + 1, $rating_not_started]
            ]
        ];

        // Scenario 2e
        //    Far past  3 yrs ago  TS     Today
        //       |         S3      |        |
        //
        // state = <ignored>; achievement was created before the cut off date in
        // the past.
        // - it is not even 'NOT PROGRESSED'.
        $scn_2e = (object) [
            'comp_name' => $comp3,
            'user_group' => $user_group1,
            'times' => [
                [($from_years_ago * 365) + 1, $rating_stage3]
            ]
        ];

        // Scenario 2f
        //    Far past  3 yrs ago  TS     Today
        //       |         NL      |        |
        //
        // state = <ignored>; achievement was created before the cut off date in
        // the past.
        // - it is not even 'NOT STARTED'.
        $scn_2f = (object) [
            'comp_name' => $comp3,
            'user_group' => $user_group2,
            'times' => [
                [($from_years_ago * 365) + 1, $rating_not_started]
            ]
        ];

        $achieved = state::achieved()->value;
        $progressed = state::progressed()->value;
        $not_progressed = state::not_progressed()->value;
        $not_started = state::not_started()->value;

        $states = [
            $achieved => [],
            $progressed => [],
            $not_progressed => [$scn_2a],
            $not_started => [$scn_2b, $scn_2d]
        ];

        if ($lowest_scale_value_is_not_started) {
            $states[$not_started][] = $scn_2c;
        } else {
            $states[$not_progressed][] = $scn_2c;
        }

        $expected = array_map(
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
            $states[$expected_state->value]
        );

        return [
            $expected,
            [$scn_2a, $scn_2b, $scn_2c, $scn_2d, $scn_2e, $scn_2f],
            $scale,
            $expected_state,
            $days_ago,
            $from_years_ago,
            $lowest_scale_value_is_not_started
        ];
    }

    /**
     * Scenario set 3: achievements exist before and in the overview period.
     *
     * Requirements:
     * - The fact there is an achievement during the overview period => there
     *   has been an update during the overview period => state cannot be 'NOT
     *   PROGRESSED'
     *
     * @param state $expected_state state to use to compute expected achievements
     *        the repository should return.
     * @param $lowest_scale_value_is_not_started sets the lowest is not started
     *        value for the created competency scale.
     *
     * @return mixed[] a [expected, scenarios, scale, overview state, days_ago,
     *         from years ago, lowest scale value is not started] tuple to feed
     *         into self::overview_test().
     */
    private static function scenario_set_3(
        state $expected_state, bool $lowest_scale_value_is_not_started
    ): array {
        $comp1 = 'test_sc3_1';
        $comp2 = 'test_sc3_2';

        $user_group1 = [user_groups::COHORT, 'sc3_cohort'];
        $user_group2 = [user_groups::POSITION, 'sc3_pos'];
        $user_group3 = [user_groups::ORGANISATION, 'sc3_org'];

        $rating_not_started = null;
        $rating_assigned = 'Assigned (AS)';
        $rating_stage1 = 'Stage1 (S1)';
        $rating_stage2 = 'Stage2 (S2)';
        $rating_stage3 = 'Stage3 (S3)';
        $rating_stage4 = 'Stage4 (S4)';
        $rating_partially_achieved = 'Almost (PA)';
        $rating_fully_achieved = 'Complete (FA)';

        $scale = [
            [$rating_assigned, false],
            [$rating_stage1, false],
            [$rating_stage2, false],
            [$rating_stage3, false],
            [$rating_stage4, false],
            [$rating_partially_achieved, true],
            [$rating_fully_achieved, true]
        ];

        // 'TS' in diagrams below. Note $days_ago + X = even further than in the
        // past than $days_ago from today.
        $days_ago = 365;
        $from_years_ago = 2;

        // Scenario 3a
        //    Far past   T0...T2   TS    T3...T5    Today
        //       |       NL...S1   |     S2...S4      |
        //
        // state = ‘PROGRESSED’; latest achievement in overview period (T5) has
        // (scale value != null AND scale value != lowest value if lowest is not
        // started = true) AND the scale value != proficient
        $scn_3a = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 2, $rating_stage1],
                [$days_ago - 20, $rating_stage2],
                [$days_ago - 300, $rating_stage4]
            ]
        ];

        // Scenario 3b
        //    Far past   T0    T1    TS    T2...T5  Today
        //       |       NL    AS    |     S3...AS    |
        //
        // Looking at latest achievement in overview period (T5), state is either:
        // - 'PROGRESSED'; scale value != proficient AND scale value != null
        // - 'NOT STARTED'; scale value != proficient AND scale value = lowest
        //   value AND lowest scale value is not started = true
        // - Regressions count as progressed
        $scn_3b = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group2,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 2, $rating_assigned],
                [$days_ago - 20, $rating_stage3],
                [$days_ago - 300, $rating_assigned]
            ]
        ];

        // Scenario 3c
        //    Far past   T0    T1    TS    T2  T3   Today
        //       |       NL    AS    |     S3  NL     |
        //
        // state = ‘NOT STARTED’; latest achievement in overview period (T3)
        // has a null scale value.
        $scn_3c = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group3,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago + 2, $rating_assigned],
                [$days_ago - 20, $rating_stage3],
                [$days_ago - 300, $rating_not_started]
            ]
        ];

        // Scenario 3d
        //    Far past   T0       TS    T2        Today
        //       |       NL       |     S4          |
        //
        // state = ‘PROGRESSED’; latest achievement in overview period (T2) has
        // (scale value != null OR scale value != lowest value if lowest is not
        // started = true) AND the scale value != proficient.
        $scn_3d = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group1,
            'times' => [
                [$days_ago + 10, $rating_not_started],
                [$days_ago - 300, $rating_stage4]
            ]
        ];

        // Scenario 3e
        //    Far past   T0    T1    T2   TS    T3        Today
        //       |       AS    S3    PA   |     S3          |
        //
        // state = ‘PROGRESSED’; latest achievement in overview period (T3) has
        // (scale value != null OR scale value != lowest value if lowest is not
        // started = true) AND the scale value != proficient
        // - Regressions count as progressed
        $scn_3e = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group2,
            'times' => [
                [$days_ago + 10, $rating_assigned],
                [$days_ago + 5, $rating_stage3],
                [$days_ago + 5, $rating_partially_achieved],
                [$days_ago - 300, $rating_stage3]
            ]
        ];

        // Scenario 3f
        //    Far past   T0    T1   TS    T2    Today
        //       |       AS    S3   |     S3      |
        //
        // state = ‘PROGRESSED’; latest achievement in overview period (T3) has
        // (scale value != null OR scale value != lowest value if lowest is not
        // started = true) AND the scale value != proficient
        // - This is only possible if an achievement is directly created in the
        //   table. In a normal case, the system creates new achievement records
        //   only if a scale value changed.
        $scn_3f = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group3,
            'times' => [
                [$days_ago + 10, $rating_assigned],
                [$days_ago + 5, $rating_stage3],
                [$days_ago - 200, $rating_stage3]
            ]
        ];

        $achieved = state::achieved()->value;
        $progressed = state::progressed()->value;
        $not_progressed = state::not_progressed()->value;
        $not_started = state::not_started()->value;

        $states = [
            $achieved => [],
            $progressed => [$scn_3a, $scn_3d, $scn_3e, $scn_3f],
            $not_progressed => [],
            $not_started => [$scn_3c]
        ];

        if ($lowest_scale_value_is_not_started) {
            $states[$not_started][] = $scn_3b;
        } else {
            $states[$progressed][] = $scn_3b;
        }

        $expected = array_map(
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
            $states[$expected_state->value]
        );

        return [
            $expected,
            [$scn_3a, $scn_3b, $scn_3c, $scn_3d, $scn_3e, $scn_3f],
            $scale,
            $expected_state,
            $days_ago,
            $from_years_ago,
            $lowest_scale_value_is_not_started
        ];
    }

    /**
     * Scenario set 4: achievements exist in overview period but not before.
     *
     * Requirements:
     * - The fact there is an achievement during the overview period => there
     *   has been an update during the overview period => state cannot be 'NOT
     *   PROGRESSED'
     *
     * @param state $expected_state state to use to compute expected achievements
     *        the repository should return.
     * @param $lowest_scale_value_is_not_started sets the lowest is not started
     *        value for the created competency scale.
     *
     * @return mixed[] a [expected, scenarios, scale, overview state, days_ago,
     *         from years ago, lowest scale value is not started] tuple to feed
     *         into self::overview_test().
     */
    private static function scenario_set_4(
        state $expected_state, bool $lowest_scale_value_is_not_started
    ): array {
        $comp1 = 'test_sc4_1';
        $comp2 = 'test_sc4_2';

        $user_group1 = [user_groups::COHORT, 'sc4_cohort'];
        $user_group2 = [user_groups::POSITION, 'sc4_pos'];
        $user_group3 = [user_groups::ORGANISATION, 'sc4_org'];

        $rating_not_started = null;
        $rating_assigned = 'Assigned (AS)';
        $rating_stage1 = 'Stage1 (S1)';
        $rating_stage2 = 'Stage2 (S2)';
        $rating_stage3 = 'Stage3 (S3)';
        $rating_stage4 = 'Stage4 (S4)';
        $rating_partially_achieved = 'Almost (PA)';
        $rating_fully_achieved = 'Complete (FA)';

        $scale = [
            [$rating_assigned, false],
            [$rating_stage1, false],
            [$rating_stage2, false],
            [$rating_stage3, false],
            [$rating_stage4, false],
            [$rating_partially_achieved, true],
            [$rating_fully_achieved, true]
        ];

        // 'TS' in diagrams below. Note $days_ago + X = even further than in the
        // past than $days_ago from today.
        $days_ago = 189;
        $from_years_ago = 2;

        // Scenario 4a
        //    Far past    TS    T0    T1           Today
        //       |        |     NL    AS             |
        //
        // Looking at latest achievement in overview period (T1), state is either:
        // - 'PROGRESSED'; scale value != proficient AND scale value != null
        // - 'NOT STARTED'; scale value != proficient AND scale value = lowest
        //   value and AND lowest scale value is not started = true
        $scn_4a = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group1,
            'times' => [
                [$days_ago - 20, $rating_not_started],
                [$days_ago - 30, $rating_assigned]
            ]
        ];

        // Scenario 4b
        //    Far past    TS    T0        Today
        //       |        |     NL          |
        //
        // state = ‘NOT STARTED’; latest achievement in the overview period (T0)
        // has a scale value = null
        $scn_4b = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group2,
            'times' => [
                [$days_ago - 20, $rating_not_started]
            ]
        ];

        // Scenario 4c
        //    Far past    TS    T0...T3 Today
        //       |        |     NL...S3  |
        //
        // state = 'PROGRESSED'; latest achievement in the overview period (T3)
        // has a scale value != null AND the scale value != proficient
        $scn_4c = (object) [
            'comp_name' => $comp1,
            'user_group' => $user_group3,
            'times' => [
                [$days_ago - 20, $rating_not_started],
                [$days_ago - 30, $rating_assigned],
                [$days_ago - 40, $rating_stage2],
                [$days_ago - 50, $rating_stage3]
            ]
        ];

        // Scenario 4d
        //    Far past    TS    T0...T3 Today
        //       |        |     NL...AS  |
        //
        // Looking at latest achievement in overview period (T3), state is either:
        // - 'PROGRESSED'; scale value != proficient AND scale value != null
        // - 'NOT STARTED'; scale value != proficient AND scale value = lowest
        //   value and AND lowest scale value is not started = true
        $scn_4d = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group1,
            'times' => [
                [$days_ago - 20, $rating_not_started],
                [$days_ago - 30, $rating_assigned]
            ]
        ];

        // Scenario 4e
        //    Far past    TS    T0...T3 Today
        //       |        |     NL...NL  |
        //
        // state = 'NOT STARTED'; latest achievement in the overview period (T3)
        // has a scale value = null
        $scn_4e = (object) [
            'comp_name' => $comp2,
            'user_group' => $user_group2,
            'times' => [
                [$days_ago - 20, $rating_not_started],
                [$days_ago - 30, $rating_assigned],
                [$days_ago - 40, $rating_stage2],
                [$days_ago - 50, $rating_not_started]
            ]
        ];


        $achieved = state::achieved()->value;
        $progressed = state::progressed()->value;
        $not_progressed = state::not_progressed()->value;
        $not_started = state::not_started()->value;

        $states = [
            $achieved => [],
            $progressed => [$scn_4c],
            $not_progressed => [],
            $not_started => [$scn_4b, $scn_4e]
        ];

        if ($lowest_scale_value_is_not_started) {
            $states[$not_started][] = $scn_4a;
            $states[$not_started][] = $scn_4d;
        } else {
            $states[$progressed][] = $scn_4a;
            $states[$progressed][] = $scn_4d;
        }

        $expected = array_map(
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
            $states[$expected_state->value]
        );

        return [
            $expected,
            [$scn_4a, $scn_4b, $scn_4c, $scn_4d, $scn_4e],
            $scale,
            $expected_state,
            $days_ago,
            $from_years_ago,
            $lowest_scale_value_is_not_started
        ];
    }
}