<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core_course
 */

namespace core_course\usagedata;

use tool_usagedata\export;

class categories_per_depth implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('categories_per_depth_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @throws \dml_exception
     */
    public function export(): array {
        global $DB;

        $sql = 'SELECT course_categories.depth, COUNT(course_categories.id) AS categories_count
                  FROM {course_categories} course_categories
              GROUP BY course_categories.depth';

        $depths = $DB->get_records_sql_menu($sql);

        $result = [];
        foreach ($depths as $depth => $count) {
            $result[$depth] = (int) $count;
        }
        return $result;
    }
}