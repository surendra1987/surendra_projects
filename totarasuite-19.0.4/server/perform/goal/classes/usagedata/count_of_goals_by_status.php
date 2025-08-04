<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal\usagedata;

use perform_goal\entity\goal;
use perform_goal\model\goal as goal_model;
use tool_usagedata\export;

/**
 * Usage data export class counting goals by status and some other general metrics.
 */
class count_of_goals_by_status implements export {

    private const RESULT_KEY_TOTAL_GOALS_COUNT = 'total_goals_count';

    private const RESULT_KEY_GOALS_RECENTLY_UPDATED_COUNT = 'goals_recently_updated_count';

    private const RESULT_KEY_STATUS_RECENTLY_UPDATED_COUNT = 'status_recently_updated_count';

    private const RESULT_KEY_PREFIX_STATUS_COUNT = 'status_count_';

    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('count_of_goals_by_status_summary', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritDoc
     */
    public function export(): array {

        // Time limit for the 'recently updated' counts.
        $thirty_days_ago = time() - 30 * DAYSECS;

        $query_result = goal::repository()
            ->select(['status', 'COUNT(status) AS status_count'])
            ->add_select_raw(
                'COUNT(CASE WHEN status_updated_at < :thirty_days_ago1 THEN NULL ELSE 1 END) AS status_recently_updated_count',
                ['thirty_days_ago1' => $thirty_days_ago]
            )
            ->add_select_raw(
                'COUNT(CASE WHEN updated_at < :thirty_days_ago2 THEN NULL ELSE 1 END) AS recently_updated_count',
                ['thirty_days_ago2' => $thirty_days_ago]
            )
            ->group_by('status')
            ->get()
            ->key_by('status')
            ->all(true);

        $count_of_goals_by_status = [];
        $all_status_codes = array_keys(goal_model::get_status_choices());
        sort($all_status_codes);
        foreach ($all_status_codes as $status_code) {
            $count_of_goals_by_status[self::RESULT_KEY_PREFIX_STATUS_COUNT . $status_code] = isset($query_result[$status_code])
                ? $query_result[$status_code]->status_count
                : 0;
        }

        // Just add up all the status counts to get the total goal count.
        $total_goal_count = array_sum(array_column($query_result, 'status_count'));

        // Add up the other counts.
        $recently_updated_count = array_sum(array_column($query_result, 'recently_updated_count'));
        $status_recently_updated_count = array_sum(array_column($query_result, 'status_recently_updated_count'));

        return array_merge(
            [
                self::RESULT_KEY_TOTAL_GOALS_COUNT => $total_goal_count,
                self::RESULT_KEY_GOALS_RECENTLY_UPDATED_COUNT => $recently_updated_count,
                self::RESULT_KEY_STATUS_RECENTLY_UPDATED_COUNT => $status_recently_updated_count,
            ],
            $count_of_goals_by_status
        );
    }
}
