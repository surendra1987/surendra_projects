<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\testing\generator as core_generator;
use core_my\models\perform_overview\state;
use core_my\perform_overview_util;
use core_phpunit\testcase;
use perform_goal\model\goal;
use perform_goal\model\overview\item;
use perform_goal\model\overview\overview as overview_model;
use perform_goal\webapi\resolver\query\overview;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_overview_helper.php');

/**
 * @group perform_goal
 */
class perform_goal_webapi_resolver_query_overview_test extends testcase {
    use perform_overview_helper;
    use webapi_phpunit_helper;

    private const QUERY = 'perform_goal_overview';

    protected function setUp(): void {
        parent::setUp();
        perform_overview_util::reset_permission_cache();
    }

    protected function tearDown(): void {
        perform_overview_util::reset_permission_cache();
        parent::tearDown();
    }

    public function test_successful_ajax_call(): void {
        $days_ago = 10;
        [$expected, $subject] = $this->create_test_data($days_ago);

        $args = [
            'input' => [
                'filters' => [
                    'id' => $subject->id,
                    'period' => $days_ago
                ],
                'sort' => [
                    [
                        'column' => 'last_updated',
                        'direction' => 'DESC'
                    ]
                ]
            ]
        ];

        self::setUser($subject);
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_successful($result);

        $this->assert_resolver_result(
            $expected, $this->get_webapi_operation_data($result),
        );
    }

    public function test_failed_ajax_query(): void {
        $days_ago = 10;
        [, $subject] = $this->create_test_data($days_ago);

        $args = [
            'input' => [
                'filters' => ['id' => $subject->id, 'period' => $days_ago]
            ]
        ];

        $try = function(array $input, string $err): void {
            try {
                $this->resolve_graphql_query(self::QUERY, $input);
                self::fail('managed to query when it should have failed');
            } catch (moodle_exception $e) {
                self::assertStringContainsString($err, $e->getMessage());
            }
        };

        self::setUser($subject);
        $feature = 'perform_goals';
        advanced_feature::disable($feature);
        perform_overview_util::reset_permission_cache();
        $try($args, 'Feature perform_goals is not available.');
        advanced_feature::enable($feature);

        perform_overview_util::reset_permission_cache();
        $try([], 'No input parameter for overview');

        self::setUser();
        perform_overview_util::reset_permission_cache();
        $try($args, 'You are not logged in');

        self::setGuestUser();
        perform_overview_util::reset_permission_cache();
        $try($args, 'Must be an authenticated user');

        $core_generator = core_generator::instance();
        $user = $core_generator->create_user();
        self::setUser($user);
        perform_overview_util::reset_permission_cache();
        $try($args, 'No permissions to get overview data for this user');

        self::setUser($subject);
        $args = [
            'input' => [
                'filters' => ['id' => $subject->id, 'period' => 1000]
            ]
        ];
        $try($args, 'Invalid period for overview');
    }

    /**
     * Checks if the results match the expected ones.
     *
     * @param overview_model $expected expected overview.
     * @param array<string,array<string,mixed>> result generated from the query
     *        resolver.
     */
    private function assert_resolver_result(
        overview_model $expected,
        array $results
    ): void {
        [$expected_counts, $expected_items] = state::all()->reduce(
            function (array $tuple, state $state) use ($expected): array {
                [$counts, $items] = $tuple;

                $name = strtolower($state->name);
                $counts[$name] = $expected->get_count_by_state($state);

                // By default the resolver sorts by update_at but this needs a
                // secondary sort by name to ensure predictability.
                $sorted = $expected->get_items_by_state($state)
                    ->sort(
                        fn (item $a, item $b): int =>
                            $b->goal->updated_at === $a->goal->updated_at
                                ? $a->name <=> $b->name
                                : $b->goal->updated_at <=> $a->goal->updated_at
                    )
                    ->all();

                // By default, the resolver only returns a subset of the items
                // as well.
                $items[$name] = array_map(
                    fn (item $item): int => $item->id,
                    array_slice($sorted, 0, overview::ITEM_LIMIT_PER_STATE)
                );

                return [$counts, $items];
            },
            [[], []]
        );

        self::assertEquals(
            $expected->get_total(), $results['total'], 'wrong total'
        );

        self::assertEquals(
            $expected->get_due_soon(), $results['due_soon'], 'wrong due soon'
        );

        self::assertEquals(
            $expected_counts, $results['state_counts'], 'wrong state counts'
        );

        foreach ($results['goals'] as $state => $goals) {
            $ids = array_map(fn (array $item): int => $item['id'], $goals);
            self::assertEquals($expected_items[$state], $ids, 'wrong goals');
        }
    }

    /**
     * Creates test data.
     *
     * @param int $days_ago indicates start of the overview period. The overview
     *        period is from $days ago in the past to today.
     * @param int $from_years_ago indicates the starting period in the far past
     *        from which to search for records. In other words records _before_
     *        this date are ignored for the overview.
     *
     * @return mixed[] an [overview, subject] tuple.
     */
    private function create_test_data(
        int $days_ago = 2,
        int $from_years_ago = 2
    ): array {
        $now = time();
        $start = $now - $days_ago * DAYSECS;
        $cutoff = $now - $from_years_ago * YEARSECS;
        [$goals_by_state, $subject] = self::setup_env($start, $cutoff);

        $overview = new overview_model($subject);
        foreach ($goals_by_state as $name => $entities) {
            $state = state::from_name($name);

            $items = $entities
                ->transform_to([goal::class, 'load_by_entity'])
                ->transform_to(item::class);

            $overview = $overview->set_by_state($state, $items);
        }

        return [$overview, $subject];
    }
}
