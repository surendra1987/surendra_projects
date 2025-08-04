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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\resolver\type;

use context_system;
use core\webapi\type_resolver;
use core\webapi\execution_context;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;

class subject_instance_overview_item_last_update extends type_resolver {

    /**
     * @inheritdoc
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {

        $format = $args['format'] ?? null;
        $context = $ec->has_relevant_context()
            ? $ec->get_relevant_context()
            : context_system::instance();

        if ($field === 'date') {
            return (new date_field_formatter($format, $context))->format($source['date']);
        }

        if ($field === 'description') {
            return (new string_field_formatter($format, $context))->format($source['description']);
        }

        return null;
    }
}