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

use core_phpunit\testcase;
use core_my\models\perform_overview\state;
use perform_goal\formatter\overview\overview as formatter;
use perform_goal\model\goal;
use perform_goal\model\overview\item;
use perform_goal\model\overview\overview;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_overview_helper.php');

/**
 * @group perform_goal
 */
class perform_goal_webapi_type_overview_result_test extends testcase {
    use perform_overview_helper;
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_overview_result';

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(overview::class);

        $this->resolve_graphql_type(self::TYPE, 'name', new \stdClass());
    }

    /**
     * @covers ::resolve
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
        return [
            'total' => [
                'total_formatter',
                formatter::TOTAL
            ],
            'state counts' => [
                'state_counts_formatter',
                formatter::STATE_COUNTS
            ],
            'items' => [
                'items_formatter',
                formatter::ITEMS
            ],
            'due_soon' => [
                'due_soon_formatter',
                formatter::DUE
            ]
        ];
    }

    /**
     * @dataProvider td_valid
     */
    public function test_valid(string $formatterMethod, string $field): void {
        // Setting days_from as 2 allow nearly due goals to be generated as well
        // as overdue goals.
        $overview = $this->create_test_overview(2, 2);

        $this->assertEquals(
            $this->$formatterMethod($overview),
            $this->resolve_graphql_type(self::TYPE, $field, $overview),
            'wrong value'
        );
    }

    // Formatter methods

    /**
     * @param overview $overview
     * @return int
     */
    protected function total_formatter(overview $overview): int {
        return $overview->get_total();
    }

    /**
     * @param overview $overview
     * @return array
     */
    protected function state_counts_formatter(overview $overview): array {
        return state::all()->reduce(
            function (array $acc, state $state) use ($overview): array {
                $key = strtolower($state->name);
                $acc[$key] = $overview->get_count_by_state($state);
                return $acc;
            },
            []
        );
    }

    /**
     * @param overview $overview
     * @return array
     */
    protected function items_formatter(overview $overview): array {
        return state::all()->reduce(
            function (array $acc, state $state) use ($overview): array {
                $key = strtolower($state->name);
                $acc[$key] = $overview->get_items_by_state($state);
                return $acc;
            },
            []
        );
    }

    /**
     * @param overview $overview
     * @return int
     */
    protected function due_soon_formatter(overview $overview): int {
        return $overview->get_due_soon();
    }


    /**
     * Creates a test overview.
     *
     * @param int $days_ago indicates start of the overview period. The overview
     *        period is from $days ago in the past to today.
     * @param int $from_years_ago indicates the starting period in the far past
     *        from which to search for records. In other words records _before_
     *        this date are ignored for the overview.
     *
     * @return overview the test overview.
     */
    private function create_test_overview(
        int $days_ago = 10,
        int $from_years_ago = 2
    ): overview {
        $now = time();
        $start = $now - $days_ago * DAYSECS;
        $cutoff = $now - $from_years_ago * YEARSECS;
        [$goals_by_state, $subject] = self::setup_env($start, $cutoff);

        $overview = new overview($subject);
        foreach ($goals_by_state as $name => $entities) {
            $state = state::from_name($name);

            $items = $entities
                ->transform_to([goal::class, 'load_by_entity'])
                ->transform_to(item::class);

            $overview = $overview->set_by_state($state, $items);
        }

        return $overview;
    }
}
