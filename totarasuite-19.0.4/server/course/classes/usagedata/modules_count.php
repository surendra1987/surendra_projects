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

class modules_count implements export {

    public const COMPONENT = 'core_mod';

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('modules_count_summary');
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
        $sql = 'SELECT modules.name, COUNT(course_modules.id) AS instancecount
                  FROM {modules} modules
                  JOIN {course_modules} course_modules ON course_modules.module = modules.id
              GROUP BY modules.name';

        $modules = $DB->get_records_sql_menu($sql);

        $return = [];
        foreach ($modules as $module_name => $count) {
            $return[$module_name] = (int) $count;
        }

        return $return;
    }
}