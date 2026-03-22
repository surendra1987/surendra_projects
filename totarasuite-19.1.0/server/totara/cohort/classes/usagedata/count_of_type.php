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
 * @package totara_cohort
 */

namespace totara_cohort\usagedata;

use cohort;
use tool_usagedata\export;

class count_of_type implements export {

    public function get_summary(): string {
        return get_string('count_of_type_summary', 'totara_cohort');
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
        global $DB, $CFG;

        require_once $CFG->dirroot . '/cohort/lib.php';

        $sql = 'SELECT cohorttype, COUNT(id) AS type_count
                FROM {cohort}
                GROUP BY cohorttype';
        
        $types = $DB->get_records_sql_menu($sql);

        $result = [];
        foreach ($types as $type => $count) {
            $type_key = ($type == cohort::TYPE_STATIC) ? 'static' : 'dynamic';
            $result[$type_key] = (int) $count;
        }

        return $result;
    }

}
