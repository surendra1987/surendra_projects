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
use totara_competency\models\perform_overview\item;
use totara_competency\models\perform_overview\overview;
use totara_competency\formatter\perform_overview\overview as formatter;
use totara_competency\user_groups;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_overview_testcase.php');

/**
 * @group totara_competency
 * @group totara_competency_overview
 */
class totara_competency_webapi_resolver_type_perform_overview_result_test
extends totara_competency_perform_overview_testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'totara_competency_perform_overview_result';

    /**
     * @covers \totara_competency\webapi\resolver\type\perform_overview_result::resolve
     */
    public function test_invalid_input(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(overview::class);

        $this->resolve_graphql_type(self::TYPE, 'id', new \stdClass());
    }

    /**
     * @covers \totara_competency\webapi\resolver\type\perform_overview_result::resolve
     */
    public function test_invalid_field(): void {
        $overview = $this->create_test_overview();
        $field = 'unknown';

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessageMatches("/$field/");
        $this->resolve_graphql_type(self::TYPE, $field, $overview);
    }

    /**
     * Test data for test_valid
     */
    public static function td_valid(): array {
        // Unfortunately, PHPUnit runs _all data providers including others in
        // this and other test harnesses_ before even running a single test. If
        // this method created a 'real' item here, it creates database records
        // that would leak through to other testharnesses; It would likely mess
        // up other tests and it would be exceedingly difficult to trace where
        // those extra records can from. Hence the use of callables below that
        // allow this data provider to return the expected value based on an
        // item created by the targeted test method.
        return [
            '1. total' => [
                fn(overview $overview): int => $overview->get_total(),
                formatter::TOTAL
            ],
            '2. state counts' => [
                fn(overview $overview): array => state::all()->reduce(
                    function (array $acc, state $state) use ($overview): array {
                        $key = strtolower($state->name);
                        $acc[$key] = $overview->get_count_by_state($state);

                        return $acc;
                    },
                    []
                ),
                formatter::STATE_COUNTS
            ],
            '3. items' => [
                fn(overview $overview): array => state::all()->reduce(
                    function (array $acc, state $state) use ($overview): array {
                        $key = strtolower($state->name);
                        $acc[$key] = $overview->get_items_by_state($state);

                        return $acc;
                    },
                    []
                ),
                formatter::ITEMS
            ],
            '4. pending_changes' => [
                fn(overview $overview): bool => $overview->has_pending_changes(),
                formatter::PENDING
            ]
        ];
    }

    /**
     * @dataProvider td_valid
     */
    public function test_valid( callable $expected, string $field): void {
        $overview = $this->create_test_overview();

        $this->assertEquals(
            $expected($overview),
            $this->resolve_graphql_type(self::TYPE, $field, $overview),
            'wrong value'
        );
    }

    /**
     * Creates a test overview.
     *
     * @return overview the test overview.
     */
    private function create_test_overview(): overview {
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
            // ACHIEVED
            (object) [
                'comp_name' => 'test_competency',
                'user_group' => [user_groups::COHORT, 'test_cohort'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago + 10, $rating_progressed],
                    [$days_ago - 1, $rating_fully_achieved]
                ]
            ],
            (object) [
                'comp_name' => 'test_competency1',
                'user_group' => [user_groups::COHORT, 'test_cohort'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago - 5, $rating_fully_achieved]
                ]
            ],
            // PROGRESSED
            (object) [
                'comp_name' => 'test_competency',
                'user_group' => [user_groups::ORGANISATION, 'test_org'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago - 1, $rating_progressed]
                ]
            ],
            // NOT PROGRESSED
            (object) [
                'comp_name' => 'test_competency',
                'user_group' => [user_groups::USER, 'test_assigner'],
                'times' => [
                    [$days_ago + 15, $rating_assigned],
                    [$days_ago + 10, $rating_progressed]
                ]
            ],
            // NOT STARTED
            (object) [
                'comp_name' => 'test_competency',
                'user_group' => [user_groups::POSITION, 'test_pos'],
                'times' => [
                    [$days_ago + 15, $rating_not_started]
                ]
            ],
            (object) [
                'comp_name' => 'test_competency1',
                'user_group' => [user_groups::POSITION, 'test_pos'],
                'times' => [
                    [$days_ago + 1, $rating_not_started]
                ]
            ],
            (object) [
                'comp_name' => 'test_competency2',
                'user_group' => [user_groups::POSITION, 'test_pos'],
                'times' => [
                    [$days_ago + 34, $rating_not_started]
                ]
            ]
        ];

        $test_data = $this->create_test_data($raw_achievements, $raw_scale, time());
        $achievements = $test_data->achievements;
        $overview = new overview($test_data->user);

        $states = [
            user_groups::COHORT => state::achieved(),
            user_groups::ORGANISATION => state::progressed(),
            user_groups::USER => state::not_progressed(),
            user_groups::POSITION => state::not_started()
        ];

        foreach ($achievements as $key => $history) {
            // Only the latest achievement in $history matters in the overview;
            // the create_test_data() call already sorted the achievements in
            // ascending date order.
            $item = $history->transform_to(item::class)->last();

            foreach ($states as $user_group => $state) {
                // PHP 8 has str_ends_with() but this totara version needs to
                // work with 7.4+ :-(
                if (substr_compare($key, $user_group, -strlen($user_group)) === 0) {
                    $overview->add_by_state($state, $item);
                    break;
                }
            }
        }

        return $overview;
    }
}
