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

namespace totara_catalog\webapi\resolver\union;

use coding_exception;
use core\webapi\union_resolver;
use GraphQL\Type\Definition\ResolveInfo;
use totara_catalog\webapi\resolver\type\option;
use totara_catalog\webapi\resolver\type\tree_option;
use totara_catalog\webapi\schema_objects\option as option_schema_object;
use totara_catalog\webapi\schema_objects\tree_option as tree_option_schema_object;

/**
 * filter_option_union class
 */
class filter_option_union implements union_resolver {
    /**
     * @inheritDoc
     */
    public static function resolve_type($object_value, $context, ResolveInfo $info): string {
        if ($object_value instanceof option_schema_object) {
            return option::class;
        }
        if ($object_value instanceof tree_option_schema_object) {
            return tree_option::class;
        }
        throw new coding_exception('Unknown union type: ' . get_class($object_value));
    }
}
