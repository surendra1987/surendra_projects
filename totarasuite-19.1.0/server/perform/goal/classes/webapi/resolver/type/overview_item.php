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
use core\format;
use core\date_format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use perform_goal\model\overview\item;
use perform_goal\formatter\overview\item as formatter;

/**
 * Maps the perform_goal\model\overview\item class into a GraphQL
 * perform_goal_overview_item type.
 */
class overview_item extends type_resolver {
    /**
     * Default formats
     */
    private const FORMATS = [
        formatter::ASSIGNMENT_DATE => date_format::FORMAT_DATELONG,
        formatter::ACHIEVEMENT_DATE => date_format::FORMAT_DATELONG,
        formatter::DESC => format::FORMAT_HTML,
        formatter::NAME => format::FORMAT_PLAIN
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
        if (!$source instanceof item) {
            throw new coding_exception(
                __METHOD__ . ' requires an input ' . item::class
            );
        }

        $format = $args['format'] ?? null;
        if (!$format) {
            $format = self::FORMATS[$field] ?? null;
        }

        $context = $ec->has_relevant_context()
            ? $ec->get_relevant_context()
            : $source->goal->context;

        return (new formatter($source, $context))->format($field, $format);
    }
}