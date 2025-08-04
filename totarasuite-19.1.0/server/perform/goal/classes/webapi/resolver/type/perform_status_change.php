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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\resolver\type;

use context_system;
use core\date_format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use perform_goal\formatter\perform_status_change as perform_change_status_formatter;

/**
 * Resolver for the 'perform_change_status' response type.
 */
class perform_status_change extends type_resolver {
    /**
     * Default formats.
     */
    private const DEF_FORMATS = [
        'created_at' => date_format::FORMAT_DATELONG
    ];

    /**
     * {@inheritdoc}
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        $format = $args['format'] ?? self::DEF_FORMATS[$field] ?? null;
        $formatter = new perform_change_status_formatter($source, context_system::instance());
        return $formatter->format($field, $format);
    }
}
