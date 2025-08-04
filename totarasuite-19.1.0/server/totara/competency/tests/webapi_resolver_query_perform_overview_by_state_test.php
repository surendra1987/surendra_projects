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

use core\date_format;
use core\collection;
use core_my\models\perform_overview\state;
use core_my\perform_overview_util;
use totara_competency\formatter\perform_overview\item;
use totara_competency\formatter\perform_overview\item_last_update;
use totara_competency\models\assignment;
use totara_competency\webapi\resolver\query\perform_overview_by_state;
use totara_competency\user_groups;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_overview_testcase.php');

/**
 * @group totara_competency
 * @group totara_competency_overview
 */
class totara_competency_webapi_resolver_query_perform_overview_by_state_test
extends totara_competency_perform_overview_testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'totara_competency_perform_overview_by_state';

    protected function setUp(): void {
        parent::setUp();
        perform_overview_util::reset_permission_cache();
    }

    protected function tearDown(): void {
        perform_overview_util::reset_permission_cache();
        parent::tearDown();
    }

    public function test_successful_ajax_call(): void {
        [$expected, $achievements, $scale, $days_ago, $state] = $this->scenario_set();
        $test_data = $this->create_test_data($achievements, $scale, time());

        $user = $test_data->user;
        self::setUser($user);

        $achievement_count = count($expected);
        $page_size = (int)($achievement_count / 2) + 1;

        $args = [
            'input' => [
                'filters' => [
                    'id' => $user->id,
                    'period' => $days_ago,
                    'status' => strtolower($state->name)
                ],
                'pagination' => ['limit' => $page_size]
            ]
        ];

        // 1st round.
        ['items' => $items, 'total' => $total] = $this->get_webapi_operation_data(
            $this->parsed_graphql_operation(self::QUERY, $args)
        );

        $this->assertEquals($achievement_count, $total, 'wrong total count');
        $this->assertCount($page_size, $items, 'wrong current page count');

        $retrieved = array_merge([], $items);

        // 2nd round.
        $args['input']['pagination']['page'] = 2;

        ['items' => $items, 'total' => $total] = $this->get_webapi_operation_data(
            $this->parsed_graphql_operation(self::QUERY, $args)
        );

        $this->assertEquals($achievement_count, $total, 'wrong total count');
        $this->assertCount(
            $achievement_count - $page_size, $items, 'wrong current page count'
        );

        $retrieved = array_merge($retrieved, $items);
        $this->assert_resolver_result($expected, $retrieved, $test_data);
    }

    /**
     * @covers \totara_competency\webapi\resolver\query\perform_overview_by_state::resolve
     */
    public function test_failed_ajax_query(): void {
        [, $achievements, $scale, $days_ago, $state] = $this->scenario_set();
        $test_data = $this->create_test_data($achievements, $scale, time());
        $user = $test_data->user;

        $args = [
            'input' => [
                'filters' => [
                    'id' => $user->id,
                    'period' => $days_ago,
                    'status' => strtolower($state->name)
                ],
                'pagination' => ['cursor' => null, 'limit' => 100]
            ]
        ];

        self::setUser($user);
        $feature = 'competencies';
        advanced_feature::disable($feature);
        perform_overview_util::reset_permission_cache();
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_failed(
            $result, 'Feature competencies is not available.'
        );
        advanced_feature::enable($feature);

        $feature = 'competency_assignment';
        advanced_feature::disable($feature);
        perform_overview_util::reset_permission_cache();
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_failed(
            $result, 'Feature competency_assignment is not available.'
        );
        advanced_feature::enable($feature);

        perform_overview_util::reset_permission_cache();
        $result = $this->parsed_graphql_operation(self::QUERY, []);
        $this->assert_webapi_operation_failed(
            $result, 'Variable "$input" of required type "totara_competency_perform_overview_by_state_input!" was not provided.'
        );

        self::setUser();
        perform_overview_util::reset_permission_cache();
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_failed($result, 'You are not logged in');

        self::setGuestUser();
        perform_overview_util::reset_permission_cache();
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_failed($result, 'Must be an authenticated user');

        self::setUser($this->create_user());
        perform_overview_util::reset_permission_cache();
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_failed(
            $result,
            'No permissions to get overview data for this user'
        );
    }

    /**
     * Checks if the results match the expected ones.
     *
     * @param stdClass[] $raw_expected expected achievements as generated from
     *        self::scenario_set().
     * @param array<array<string,mixed>> result generated from the query resolver.
     * @param stdClass $test_data data as generated by self::create_test_data();
     *        used to look up generated entity ids given a name, idnumber, etc.
     */
    private function assert_resolver_result(
        array $raw_expected,
        array $results,
        stdClass $test_data
    ): void {
        $sort_key = 'sorting';

        $expected = collection::new($raw_expected)
            ->map(
                fn(stdClass $exp): array =>  $this->format_expected(
                    $exp, $test_data, $sort_key
                )
            )
            ->sort(
                fn(array $a, array $b): int => $b[$sort_key] <=> $a[$sort_key]
            )
            ->map(
                function (array $expected) use ($sort_key): array {
                    // This was just added for sorting; it is not in the actual
                    // results.
                    unset($expected[$sort_key]);
                    return $expected;
                }
            )
            ->all();

        self::assertCount(count($expected), $results, 'wrong item counts');
        self::assertEquals($expected, $results, 'wrong items');
    }

    /**
     * Takes raw expected achievements and converts them into a format that can
     * be compared with the actual overview achievements.
     *
     * @param stdClass $raw expected achievements in the format as returned by
     *        self::scenario_set().
     * @param stdClass $test_data data as generated by self::create_test_data();
     *        used to look up generated entity ids given a name, idnumber, etc.
     * @param string $sort_key the additional field field in the returned array
     *        that helps in sorting the expected results.
     *
     * @return array<string,mixed> list of formatted items.
     */
    private function format_expected(
        stdClass $raw_expected, stdClass $test_data, string $sort_key
    ): array {
        $comp_name = $raw_expected->comp_name;
        [$user_group_type, $user_group_idn] = $raw_expected->user_group;
        $comp_id = $test_data->competencies[$comp_name];
        $uid = $test_data->user->id;

        $key = $this->achievement_key(
            $uid,
            $comp_id,
            $test_data->user_groups[$user_group_idn],
            $user_group_type
        );

        $rating = $raw_expected->rating;
        [$last_update_desc, $achievement_level] = is_null($rating)
            ? [
                get_string(
                    'perform_overview_last_update_description_not_started',
                    'totara_competency'
                ),
                get_string(
                    'perform_overview_not_started', 'totara_competency'
                )
            ]
            : [
                get_string(
                    'perform_overview_last_update_description',
                    'totara_competency',
                    (object) ['scale_value_name' => $rating]
                ),
                $rating
            ];

        $latest_achievement_entity = $test_data->achievements[$key]->last();
        $last_update = $latest_achievement_entity->last_aggregated;

        $assignment_entity = $latest_achievement_entity->assignment;
        $assignment_date = $assignment_entity
            ->assignment_user()
            ->where('user_id', $uid)
            ->one()
            ->created_at;

        $assignment_type = assignment::load_by_entity($assignment_entity)
            ->progress_name;

        $fmt_txt = fn(string $text): string => format_string($text);
        $fmt_html = fn(string $text): string => format_text($text, FORMAT_HTML);
        $fmt_date = fn (int $date): string => userdate(
            $date,
            get_string(
                date_format::get_lang_string(date_format::FORMAT_DATELONG),
                'langconfig'
            )
        );

        $url = new moodle_url(
            '/totara/competency/profile/details/index.php',
            ['competency_id' => $comp_id, 'user_id' => $uid]
        );

        return [
            item::COMPETENCY_ID => "$comp_id",
            item::COMPETENCY_NAME => $fmt_txt($comp_name),
            item::COMPETENCY_DESC => $fmt_html(
                $latest_achievement_entity->competency->description
            ),
            item::ASSIGNMENT_URL => $url->out(false),
            item::ASSIGNMENT_DATE => $fmt_date($assignment_date),
            item::ASSIGNMENT_TYPE => $fmt_txt($assignment_type),
            item::UNIQUE_ID => "$latest_achievement_entity->id",
            item::ACHIEVEMENT_LEVEL => $fmt_txt($achievement_level),
            item::ACHIEVEMENT_DATE => $fmt_date(
                $latest_achievement_entity->time_scale_value
            ),
            item::LAST_UPDATE => [
                item_last_update::DATE => $fmt_date($last_update),
                item_last_update::DESC => $last_update_desc
            ],

            // Temporarily added so that format_expected() can do the right
            // sorting on the expected results.
            $sort_key => sprintf(
                "%d/%d/%d/%d", $last_update, $uid, $comp_id, $assignment_entity->id
            )
        ];
    }

    /**
     * Creates test data.
     *
     * @return mixed[] [expected achievements, raw achievements, scale, days_ago,
     *         expected state] tuple.
     */
    private function scenario_set(): array {
        $rating_not_started = null;
        $rating_assigned = 'Assigned (AS)';
        $rating_progressed = 'Progressing (PR)';
        $rating_fully_achieved = 'Complete (FA)';

        $raw_scale = [
            [$rating_assigned, false],
            [$rating_progressed, false],
            [$rating_fully_achieved, true]
        ];

        $days_ago = 23;

        $raw_achievements = [
            (object) [
                'comp_name' => 'test_competency0',
                'user_group' => [user_groups::COHORT, 'test_cohort0'],
                'times' => [
                    [$days_ago + 15, $rating_not_started],
                    [$days_ago + 10, $rating_assigned],
                    [$days_ago - 1, $rating_fully_achieved]
                ]
            ],

            (object) [
                'comp_name' => 'test_competency0',
                'user_group' => [user_groups::ORGANISATION, 'test_org0'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago + 10, $rating_progressed],
                    [$days_ago - 4, $rating_fully_achieved]
                ]
            ],

            (object) [
                'comp_name' => 'test_competency0',
                'user_group' => [user_groups::POSITION, 'test_pos0'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago - 4, $rating_fully_achieved]
                ]
            ],

            (object) [
                'comp_name' => 'test_competency1',
                'user_group' => [user_groups::COHORT, 'test_cohort1'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago - 5, $rating_fully_achieved]
                ]
            ],

            (object) [
                'comp_name' => 'test_competency1',
                'user_group' => [user_groups::ORGANISATION, 'test_org1'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago - 6, $rating_fully_achieved]
                ]
            ],

            (object) [
                'comp_name' => 'test_competency1',
                'user_group' => [user_groups::POSITION, 'test_pos1'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago - 10, $rating_fully_achieved]
                ]
            ]
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
            $raw_achievements
        );

        // These achievements are ignored.
        $from_years_ago = perform_overview_by_state::DEF_FROM_YEARS_AGO;
        $raw_achievements[] = (object) [
            'comp_name' => 'test_competency3',
            'user_group' => [user_groups::POSITION, 'test_pos'],
            'times' => [
                [($from_years_ago * 365) + 1, $rating_not_started]
            ]
        ];

        return [
            $expected,
            $raw_achievements,
            $raw_scale,
            $days_ago,
            state::achieved()
        ];
    }
}
