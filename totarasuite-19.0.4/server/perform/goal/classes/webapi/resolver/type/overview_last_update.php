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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\resolver\type;

use coding_exception;
use context_system;
use core\date_format;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;

/**
 * Maps an array into a GraphQL perform_goal_overview_last_update type. The array
 * must have these fields:
 * - int 'date': goal last update date
 * - bool 'description': last update description
 */
class overview_last_update extends type_resolver {
    /**
     * Default formats
     */
    private const FORMATS = [
        'date' => date_format::FORMAT_DATELONG,
        'description' => format::FORMAT_HTML
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
        if (!is_array($source)) {
            throw new coding_exception(__METHOD__ . ' requires an input array');
        }

        $format = $args['format'] ?? null;
        if (!$format) {
            $format = self::FORMATS[$field] ?? null;
        }

        $context = $ec->has_relevant_context()
            ? $ec->get_relevant_context()
            : context_system::instance();

        switch ($field) {
            case 'date':
                return (new date_field_formatter($format, $context))
                    ->format($source['date']);

            case 'description':
                return (new string_field_formatter($format, $context))
                    ->format($source['description']);

            default:
                throw new coding_exception(
                    "Unknown field for overview last update type: $field"
                );
        }
    }
}