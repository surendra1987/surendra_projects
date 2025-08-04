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
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use perform_goal\formatter\goal_raw_data as goal_raw_data_formatter;
use perform_goal\model\goal_raw_data as goal_raw_data_model;

/**
 * Maps the goal_raw_data class into a GraphQL perform_goal_goal_raw_data type.
 */
class goal_raw_data extends type_resolver {
    /**
     * Default formats.
     */
    private const DEF_FORMATS = [
        'description' => format::FORMAT_RAW
    ];

    /**
     * {@inheritdoc}
     */
    public static function resolve(
        string $field,
        $source,
        array $args,
        execution_context $ec
    ) {
        if (!$source instanceof goal_raw_data_model) {
            throw new coding_exception(
                __METHOD__ . ' requires ' . goal_raw_data_model::class
            );
        }

        $format = $args['format'] ?? self::DEF_FORMATS[$field] ?? null;
        $formatter = new goal_raw_data_formatter($source, $source->context);

        return $formatter->format($field, $format);
    }
}
