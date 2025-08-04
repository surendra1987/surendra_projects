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
use totara_customfield\webapi\formatter\value_type_url_formatter;

class value_type_url extends type_resolver {

    /**
     * @inheritdoc
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        $context = $ec->get_variable('context');

        $url_data = json_decode($source ?? '', true);

        // URL value will be a JSON object with keys `url` and `text` if it's been set on the item (i.e. on a course)
        // However, default values for URL custom fields are set as the plain text URL, meaning we need separate handling for
        // when the URL is not a JSON object
        if ($url_data === null) {
            // Text cannot be handled if we've not been given a JSON object.
            // As we only have the plain text URL value, we do not have access to the `text` value.
            if ($field === 'text') {
                return null;
            }

            $formatter = new value_type_url_formatter(
                ['url' => $source],
                $context
            );
            return $formatter->format('url', format::FORMAT_PLAIN);
        }

        $formatter = new value_type_url_formatter($url_data, $context);
        return $formatter->format($field, format::FORMAT_PLAIN);
    }
}
