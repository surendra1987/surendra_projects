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

namespace totara_customfield\webapi\resolver\type;

use core\webapi\execution_context;
use core\webapi\type_resolver;
use totara_customfield\webapi\formatter\field_formatter;

class field extends type_resolver {

    /**
     * @inheritdoc
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        // File is currently an unsupported type for the resolver
        if ($source['definition']['type'] === 'file') {
            $source['value'] = null;
            $source['raw_value'] = null;
            $source['definition']['default_value'] = null;
            $source['definition']['raw_default_value'] = null;
        }

        // Setting the custom field type for the value_type union resolver
        $ec->set_variable('custom_field_type', $source['definition']['type']);

        $context = $ec->get_variable('context');
        $formatter = new field_formatter(self::apply_default_values($source), $context);

        return $formatter->format($field);
    }

    private static function apply_default_values($source) {
        if (isset($source['value'])) {
            return $source;
        }

        if (!isset($source['definition']['default_value'])) {
            return $source;
        }

        $source['value'] = $source['definition']['default_value'];
        $source['raw_value'] = $source['definition']['raw_default_value'];

        return $source;
    }
}
