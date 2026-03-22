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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\webapi\resolver\type;

use context_system;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use totara_catalog\exception\filters_query_exception;
use totara_catalog\formatter\tree_option as tree_option_formatter;
use totara_catalog\webapi\schema_objects\tree_option as tree_option_object;

/**
 * Resolver for type tree_options.
 */
class tree_option extends type_resolver {
    /**
     * @inheritdoc
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        if (!$source instanceof tree_option_object) {
            throw new filters_query_exception('Accepting only the tree_option object.');
        }

        return (new tree_option_formatter($source, context_system::instance()))
            ->format($field, $args['format'] ?? format::FORMAT_PLAIN);
    }
}
