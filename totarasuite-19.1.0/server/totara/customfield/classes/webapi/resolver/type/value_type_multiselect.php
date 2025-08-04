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

use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use totara_customfield\webapi\formatter\value_type_multiselect_formatter;

class value_type_multiselect extends type_resolver {

    /**
     * @inheritdoc
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        $options = json_decode($source ?? '', true);
        $options = [
            'options' => $options,
        ];
        $context = $ec->get_variable('context');
        $formatter = new value_type_multiselect_formatter($options, $context);
        return $formatter->format($field);
    }
}
