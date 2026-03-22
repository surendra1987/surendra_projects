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
use core\entity\user;
use core_my\models\perform_overview\state;
use core_my\perform_overview_util;
use totara_competency\aggregation_users_table;
use totara_competency\formatter\perform_overview\item;
use totara_competency\formatter\perform_overview\item_last_update;
use totara_competency\models\assignment;
use totara_competency\webapi\resolver\query\perform_overview;
use totara_competency\user_groups;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_overview_testcase.php');

/**
 * @group totara_competency
 * @group totara_competency_overview
 */
class totara_competency_webapi_resolver_query_perform_overview_test
extends totara_competency_perform_overview_testcase {
    use webapi_phpunit_helper;

    protected function setUp(): void {
        parent::setUp();
        perform_overview_util::reset_permission_cache();
    }

    protected function tearDown(): void {
        perform_overview_util::reset_permission_cache();
        parent::tearDown();
    }

    private const QUERY = 'totara_competency_perform_overview';

    public function test_successful_ajax_call(): void {
        [$expected, $achievements, $scale, $days_ago] = $this->scenario_set();
        $test_data = $this->create_test_data($achievements, $scale, time());

        $user = $test_data->user;
        $this->queue_for_aggregation($user, $test_data->competencies);

        $args = [
            'input' => [
                'filters' => ['id' => $user->id, 'period' => $days_ago]
            ]
        ];

        self::setUser($user);
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_successful($result);

        $this->assert_resolver_result(
            $expected,
            true,
            $this->get_webapi_operation_data($result),
            $test_data
        );
    }

    /**
     * @covers \totara_competency\webapi\resolver\query\perform_overview::resolve
     */
    public function test_failed_ajax_query(): void {
        [, $achievements, $scale, $days_ago] = $this->scenario_set();
        $test_data = $this->create_test_data($achievements, $scale, time());
        $user = $test_data->user;

        $args = [
            'input' => ['filters' => ['id' => $user->id, 'period' => $days_ago]]
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
            $result, 'Variable "$input" of required type "totara_competency_perform_overview_input!" was not provided.'
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
     * @param array<string,stdClass[]> $raw_expected expected achievements as
     *        generated from self::scenario_set().
     * @param array<string,array<string,mixed>> result generated from the query
     *        resolver.
     * @param bool $exp_pending indicates the expected pending changes result.
     * @param stdClass $test_data data as generated by self::create_test_data();
     *        used to look up generated entity ids given a name, idnumber, etc.
     */
    private function assert_resolver_result(
        array $raw_expected,
        bool $exp_pending,
        array $results,
        stdClass $test_data
    ): void {
        $expected_total = 0;
        $expected_counts = [];
        $expected_items = [];

        foreach ($raw_expected as $state => $history) {
            $count = count($history);
            $expected_total += $count;
            $expected_counts[$state] = $count;

            $expected_items[$state] = $this->format_expected(
                array_slice($history, 0, perform_overview::ITEM_LIMIT_PER_STATE),
                $test_data
            );
        }

        self::assertEquals($expected_total, $results['total'], 'wrong total');
        self::assertEquals($exp_pending, $results['pending_changes'], 'wrong pending');
        self::assertEquals(
            $expected_counts, $results['state_counts'], 'wrong state counts'
        );

        foreach ($results['competencies'] as $state => $achievements) {
            $expected = $expected_items[$state];

            self::assertCount(count($expected), $achievements, 'wrong item counts');
            self::assertEquals($expected, $achievements, 'wrong items');
        }
    }

    /**
     * Takes raw expected achievements and converts them into a format that can
     * be compared with the actual overview achievements.
     *
     * @param stdClass[] $raw expected achievements in the format as returned by
     *        self::scenario_set().
     * @param stdClass $test_data data as generated by self::create_test_data();
     *        used to look up generated entity ids given a name, idnumber, etc.
     *
     * @return array<array<string,mixed>> list of formatted items.
     */
    private function format_expected(
        array $raw, stdClass $test_data
    ): array {
        $sort_key = 'sorting';

        return collection::new($raw)
            ->map(
                fn (stdClass $exp): array => $this->format_expected_single(
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
                    $expected['__typename'] = 'totara_competency_perform_overview_item';
                    return $expected;
                }
            )
            ->all();
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
    private function format_expected_single(
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
     * @return mixed[] [expected achievements, raw achievements, scale, days_ago]
     *         tuple.
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
        $achieved1 = (object) [
            'comp_name' => 'test_competency0',
            'user_group' => [user_groups::COHORT, 'test_cohort'],
            'times' => [
                [$days_ago + 15, $rating_assigned],
                [$days_ago + 10, $rating_progressed],
                [$days_ago - 1, $rating_fully_achieved]
            ]
        ];

        $achieved2 = (object) [
            'comp_name' => 'test_competency1',
            'user_group' => [user_groups::COHORT, 'test_cohort'],
            'times' => [
                [$days_ago + 15, $rating_assigned],
                [$days_ago - 5, $rating_fully_achieved]
            ]
        ];

        $progressed = (object) [
            'comp_name' => 'test_competency0',
            'user_group' => [user_groups::ORGANISATION, 'test_org'],
            'times' => [
                [$days_ago + 15, $rating_assigned],
                [$days_ago - 2, $rating_progressed]
            ]
        ];

        $not_started1 = (object) [
            'comp_name' => 'test_competency0',
            'user_group' => [user_groups::POSITION, 'test_pos'],
            'times' => [
                [$days_ago + 15, $rating_not_started]
            ]
        ];

        $not_started2 = (object) [
            'comp_name' => 'test_competency1',
            'user_group' => [user_groups::POSITION, 'test_pos'],
            'times' => [
                [$days_ago + 1, $rating_not_started]
            ]
        ];

        $not_started3 = (object) [
            'comp_name' => 'test_competency2',
            'user_group' => [user_groups::POSITION, 'test_pos'],
            'times' => [
                [$days_ago + 34, $rating_not_started]
            ]
        ];

        $from_years_ago = perform_overview::DEF_FROM_YEARS_AGO;
        $ignored = (object) [
            'comp_name' => 'test_competency3',
            'user_group' => [user_groups::POSITION, 'test_pos'],
            'times' => [
                [($from_years_ago * 365) + 1, $rating_not_started]
            ]
        ];

        $states = [
            [state::achieved(), [$achieved1, $achieved2]],
            [state::progressed(), [$progressed]],
            [state::not_progressed(), []],
            [state::not_started(), [$not_started1, $not_started2, $not_started3]]
        ];

        $expected = array_reduce(
            $states,
            function (array $acc, array $tuple): array {
                [$state, $achievements] = $tuple;

                $latest = array_map(
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

                $acc[strtolower($state->name)] = $latest;
                return $acc;
            },
            []
        );

        $raw_achievements = [
            $achieved1, $achieved2,
            $progressed,
            $not_started1, $not_started2, $not_started3,
            $ignored
        ];

        return [$expected, $raw_achievements, $raw_scale, $days_ago];
    }

    /**
     * 'Queues' the given competencies for aggregation.
     *
     * @param user $user user whose 'achievements' are to be aggregated.
     * @param collection<int> $competencies ids of competencies to be aggregated.
     */
    private function queue_for_aggregation(user $user, collection $competencies): void {
        $competencies->reduce(
            fn(aggregation_users_table $table, int $competency_id): aggregation_users_table =>
                $table->queue_for_aggregation($user->id, $competency_id),
            new aggregation_users_table()
        );
    }
}
