<?php
/**
 * This file is part of Totara Learn
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

namespace engage_course\totara_engage\link;

use totara_engage\link\source_generator;

/**
 * Generator to track whenever courses are the source page.
 *
 * @package engage_course\totara_engage\link
 */
final class course_source extends source_generator {
    public static function get_source_key(): string {
        return 'ec';
    }

    /**
     * @param array $source_params
     * @return array
     */
    public static function convert_source_to_attributes(array $source_params): array {
        return [
            'id' => current($source_params),
        ];
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function convert_attributes_to_source(array $attributes): array {
        return [
            $attributes['id'],
        ];
    }
}