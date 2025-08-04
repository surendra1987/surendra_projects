<?php
/*
 *  This file is part of Totara Core
 *
 *  Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author Aaron Machin <aaron.machin@totara.com>
 *  @package totara_customfield
 */

namespace totara_customfield\webapi\resolver\union;

use core\webapi\union_resolver;
use GraphQL\Type\Definition\ResolveInfo;
use totara_customfield\webapi\resolver\type\value_type_checkbox;
use totara_customfield\webapi\resolver\type\value_type_datetime;
use totara_customfield\webapi\resolver\type\value_type_decimal;
use totara_customfield\webapi\resolver\type\value_type_generic;
use totara_customfield\webapi\resolver\type\value_type_integer;
use totara_customfield\webapi\resolver\type\value_type_location;
use totara_customfield\webapi\resolver\type\value_type_menu;
use totara_customfield\webapi\resolver\type\value_type_multiselect;
use totara_customfield\webapi\resolver\type\value_type_text;
use totara_customfield\webapi\resolver\type\value_type_url;

class value_type implements union_resolver {

    /**
     * @inheritdoc
     */
    public static function resolve_type($objectvalue, $context, ResolveInfo $info): string {
        $type_map = [
            'checkbox' => value_type_checkbox::class,
            'text' => value_type_text::class,
            'multiselect' => value_type_multiselect::class,
            'datetime' => value_type_datetime::class,
            // Textarea is not implemented as a separate type, but rather is purposely implemented using the value type `generic`.
            // There is a lack of support for editors in formatting as plain text.
            // Due to this, it is not possible at this time to provide a robust textarea value type.
            'textarea' => value_type_generic::class,
            'integer' => value_type_integer::class,
            'decimal' => value_type_decimal::class,
            'location' => value_type_location::class,
            'url' => value_type_url::class,
            'menu' => value_type_menu::class,
        ];

        $type = $context->get_variable('custom_field_type');
        if (!isset($type_map[$type])) {
            debugging("Unknown type found, using type_generic for type `$type`");

            return value_type_generic::class;
        }

        return $type_map[$type];
    }
}
