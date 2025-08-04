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
 * @package totara_reportbuilder
 */

namespace totara_reportbuilder\usagedata;

use tool_usagedata\export;

class count_columns_used implements export {

    public function get_summary(): string {
        return get_string('count_columns_used_summary', 'totara_reportbuilder');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_ARRAY;
    }

    /**
     * @throws \dml_exception
     */
    public function export(): array {
        global $DB;

        $sql = 'SELECT builder.source, columns.type, columns.value, COUNT(columns.id) as column_count
                  FROM {report_builder_columns} columns
                  JOIN {report_builder} builder ON builder.id = columns.reportid
                 WHERE builder.embedded = 0
              GROUP BY builder.source, columns.type, columns.value';

        $columns = $DB->get_records_sql_unkeyed($sql);

        return array_map(function ($column) {
            return [
                'source' => $column->source,
                'type' => $column->type,
                'value' => $column->value,
                'count' => (int) $column->column_count,
            ];
        }, $columns);
    }
}
