<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package totara_catalog
 */
namespace totara_catalog\webapi\resolver\type;

use context_system;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use totara_catalog\webapi\schema_objects\item_details as item_details_object;
use totara_catalog\formatter\item_details as item_details_formatter;
use TypeError;

/**
 * Resolver for type item.
 */
class item_details extends type_resolver {
    /**
     * @inheritdoc
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        if (!$source instanceof item_details_object) {
            throw new TypeError('source is not of the correct type');
        }

        return (new item_details_formatter($source, context_system::instance()))
            ->format($field, $args['format'] ?? format::FORMAT_PLAIN);
    }
}
