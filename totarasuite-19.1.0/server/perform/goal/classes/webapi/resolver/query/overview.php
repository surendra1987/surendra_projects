<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\resolver\query;

use core_my\models\perform_overview\state;
use core\webapi\execution_context;
use perform_goal\model\overview\item;
use perform_goal\model\overview\overview as overview_model;

/**
 * Handles the "perform_goal_overview" GraphQL query.
 */
class overview extends overview_base {
    // Indicates how many items per state is returned.
    public const ITEM_LIMIT_PER_STATE = 2;

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $parms = self::parse($args);
        self::authorize($parms, $ec);

        return state::all()->reduce(
            fn(overview_model $acc, state $state): overview_model => $acc->set_by_state(
                $state,
                self::create_data_source($parms, $state)
                    ->get_results()
                    ->transform_to(item::class)
            ),
            new overview_model($parms->user, self::ITEM_LIMIT_PER_STATE)
        );
    }
}
