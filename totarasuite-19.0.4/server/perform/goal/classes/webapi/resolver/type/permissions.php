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
 * This program is distributed in the hope that it will be use`ful,
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

namespace perform_goal\webapi\resolver\type;

use coding_exception;
use context_system;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use perform_goal\formatter\permissions as permissions_formatter;
use perform_goal\interactor\goal_interactor;

/**
 * Maps the interactor class into a GraphQL perform_goal_permissions type.
 */
class permissions extends type_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(
        string $field,
        $source,
        array $args,
        execution_context $ec
    ) {
        if (!$source instanceof goal_interactor) {
            throw new coding_exception(__METHOD__ . ' requires ' . goal_interactor::class);
        }

        $formatter = new permissions_formatter($source, context_system::instance());
        return $formatter->format($field, null);
    }
}
