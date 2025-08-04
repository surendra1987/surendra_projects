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

namespace perform_goal\webapi\resolver\type;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\type_resolver;

/**
 * Maps an array into a GraphQL perform_goal_overview_by_state_result type. The
 * array must have these fields:
 * - int 'total': total number of goals in that state
 * - overview collection<item>|item[] 'goals': the goals in that state.
 */
class overview_by_state_result extends type_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(
        string $field,
        $source,
        array $args,
        execution_context $ec
    ) {
        if (!is_array($source)) {
            throw new coding_exception(__METHOD__ . ' requires an input array');
        }

        switch ($field) {
            case 'goals':
                return $source['goals'];

            case 'total':
                return (int)$source['total'];

            default:
                throw new coding_exception(
                    "Unknown field for overview by state result: $field"
                );
        }
    }
}
