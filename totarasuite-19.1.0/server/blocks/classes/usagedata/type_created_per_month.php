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
 * @package core_block
 */

namespace core_block\usagedata;

use tool_usagedata\export;
use tool_usagedata\helper\time;

class type_created_per_month implements export {

    public function get_summary(): string {
        return get_string('type_created_per_month_summary', 'block');
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

        $sql = 'SELECT bi.blockname, COUNT(bi.id) AS blockcount
                  FROM {block_instances} bi
                  WHERE bi.timecreated > :start AND bi.timecreated <= :end
                  GROUP BY bi.blockname';

        $results = [];

        foreach (time::get_timestamps_for_past_months() as $timestamps) {
            $results[] = [
                'year' => date('Y', $timestamps['start']),
                'month' => date('m', $timestamps['start']),
                'blocks' => $DB->get_records_sql_unkeyed($sql, $timestamps),
            ];
        }

        return $results;
    }
}
