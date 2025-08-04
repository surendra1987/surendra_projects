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

use coding_exception;
use core\webapi\execution_context;
use perform_goal\model\overview\item;

/**
 * Handles the "perform_goal_overview_by_state" GraphQL query.
 */
class overview_by_state extends overview_base {
    /**
     * Number of items returned per page.
     *
     * @var int
     */
    public const DEFAULT_PAGE_SIZE = 20;

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $parms = self::parse($args);
        self::authorize($parms, $ec);

        $pagination = $args['input']['pagination'] ?? null;

        $state = $parms->state;
        if (!$state) {
            throw new coding_exception('No state for overview by state');
        }

        $results = self::create_data_source($parms, $state)->get_offset_page(
            $pagination['limit'] ?? self::DEFAULT_PAGE_SIZE,
            $pagination['page'] ?? 1
        );

        return [
            'total' => $results->total,
            'goals' => $results->items->transform_to(item::class)
        ];
    }
}