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
use core\date_format;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use perform_goal\formatter\goal as goal_formatter;
use perform_goal\model\goal as goal_model;

/**
 * Maps the goal class into a GraphQL perform_goal_goal type.
 */
class goal extends type_resolver {
    /**
     * Default formats.
     */
    private const DEF_FORMATS = [
        'name' => format::FORMAT_PLAIN,
        'id_number' => format::FORMAT_PLAIN,
        'description' => format::FORMAT_HTML,
        'start_date' => date_format::FORMAT_DATELONG,
        'target_date' => date_format::FORMAT_DATELONG,
        'current_value_updated_at' => date_format::FORMAT_DATELONG,
        'status_updated_at' => date_format::FORMAT_DATELONG,
        'closed_at' => date_format::FORMAT_DATELONG,
        'created_at' => date_format::FORMAT_DATELONG,
        'updated_at' => date_format::FORMAT_DATELONG
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
        if (!$source instanceof goal_model) {
            throw new coding_exception(__METHOD__ . ' requires ' . goal_model::class);
        }

        $format = $args['format'] ?? self::DEF_FORMATS[$field] ?? null;
        $formatter = new goal_formatter($source, $source->context);

        return $formatter->format($field, $format);
    }
}
