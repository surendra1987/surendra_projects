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
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\goal;
use perform_goal\model\overview\item;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_overview_helper.php');

/**
 * @group perform_goal
 */
class perform_goal_webapi_resolver_query_overview_by_state_test extends testcase {
    use perform_overview_helper;
    use webapi_phpunit_helper;

    private const QUERY = 'perform_goal_overview_by_state';

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
        [$expected, $subject, $state] = $this->create_test_data($days_ago);

        $page_size = 1;
        $exp_total = count($expected);
        self::assertGreaterThan(
            $page_size, $exp_total, '< 2 goals in ' . $state->name . ' state'
        );

        $args = [
            'input' => [
                'filters' => [
                    'id' => $subject->id,
                    'period' => $days_ago,
                    'status' => strtolower($state->name)
                ],
                'pagination' => ['limit' => $page_size],
                'sort' => [
                    [
                        'column' => 'last_updated',
                        'direction' => 'DESC'
                    ]
                ]
            ]
        ];

        self::setUser($subject);

        $act_ids = [];
        for ($i = 0; $i < $exp_total; $i++) {
            $args['input']['pagination']['page'] = $i + 1;

            ['goals' => $items, 'total' => $total] = $this->get_webapi_operation_data(
                $this->parsed_graphql_operation(self::QUERY, $args)
            );

            $this->assertEquals($exp_total, $total, 'wrong total count');
            $this->assertCount($page_size, $items, 'wrong item count');

            foreach ($items as $item) {
                $act_ids[] = $item['id'];
            }
        }

        $exp_ids = $expected->map(fn (item $item): int => $item->id)->all();
        self::assertEquals($exp_ids, $act_ids, 'wrong items retrieved');
    }

    public function test_failed_ajax_query(): void {
        $days_ago = 10;
        [, $subject, $state] = $this->create_test_data($days_ago);

        $args = [
            'input' => [
                'filters' => [
                    'id' => $subject->id,
                    'period' => $days_ago,
                    'status' => strtolower($state->name)
                ],
                'pagination' => ['limit' => 10]
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
     * @return mixed[] [item for the state, subject, state] tuple.
     */
    private function create_test_data(
        int $days_ago = 2,
        int $from_years_ago = 2
    ): array {
        $now = time();
        $start = $now - $days_ago * DAYSECS;
        $cutoff = $now - $from_years_ago * YEARSECS;
        [$goals_by_state, $subject] = self::setup_env($start, $cutoff);

        $state = state::progressed();
        $items = $goals_by_state[$state->name]
            ->sort(
                // By default the resolver sorts by the update_at but this needs
                // a secondary sort by name to ensure predictability.
                fn (goal_entity $a, goal_entity $b): int =>
                    $b->updated_at === $a->updated_at
                        ? $a->name <=> $b->name
                        : $b->updated_at <=> $a->updated_at
            )
            ->transform_to([goal::class, 'load_by_entity'])
            ->transform_to(item::class);

        return [$items, $subject, $state];
    }
}
