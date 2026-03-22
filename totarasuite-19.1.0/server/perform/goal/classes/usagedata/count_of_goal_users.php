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

use core\entity\user;
use perform_goal\entity\goal;
use tool_usagedata\export;

/**
 * Usage data export class counting users with and without goals. Also, maximum number of goals for a single user.
 */
class count_of_goal_users implements export {

    private const RESULT_KEY_USERS_WITH_GOALS = 'users_with_goals_count';

    private const RESULT_KEY_USERS_WITHOUT_GOALS = 'users_without_goals_count';

    private const RESULT_KEY_MAX_GOALS_FOR_A_USER = 'max_goals_for_a_user';

    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('count_of_goal_users', 'perform_goal');
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
        $query_result = goal::repository()
            ->select(['user_id', 'COUNT(user_id) AS goal_count'])
            ->where_not_null('user_id')
            ->group_by('user_id')
            ->order_by_raw('goal_count DESC')
            ->get();

        $count_users_with_goals = $query_result->count();
        $count_all_users = user::repository()->where('deleted', 0)->count();
        $count_users_without_goals = $count_all_users - $count_users_with_goals;
        $max_goals_for_a_user = $count_users_with_goals ? $query_result->first()->goal_count : 0;

        return [
            self::RESULT_KEY_USERS_WITH_GOALS => $count_users_with_goals,
            self::RESULT_KEY_USERS_WITHOUT_GOALS => $count_users_without_goals,
            self::RESULT_KEY_MAX_GOALS_FOR_A_USER => $max_goals_for_a_user,
        ];
    }
}
