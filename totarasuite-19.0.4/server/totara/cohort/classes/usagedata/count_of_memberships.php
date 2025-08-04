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

use tool_usagedata\export;

class count_of_memberships implements export {

    public function get_summary(): string {
        return get_string('count_of_memberships_summary', 'totara_cohort');
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

        $sql = 'SELECT cohort.id, COUNT(cohort_members.id) AS members_count
                FROM {cohort} cohort
                LEFT JOIN {cohort_members} cohort_members ON cohort_members.cohortid = cohort.id
                GROUP BY cohort.id';

        $cohorts = $DB->get_records_sql($sql);

        $ranges = [
            'nil' => 0,
            '1..10' => 0,
            '11..100' => 0,
            '101..1000' => 0,
            '1001..10000' => 0,
            '10001..100000' => 0,
            '100001..1000000' => 0,
            '1000001..' => 0,
        ];
        foreach ($cohorts as $cohort) {
            $members_count = (int) $cohort->members_count;

            if ($members_count === 0) {
                $ranges['nil']++;
                continue;
            }

            if ($members_count <= 10) {
               $ranges['1..10']++;
               continue;
            }

            if ($members_count <= 100) {
               $ranges['11..100']++;
               continue;
            }

            if ($members_count <= 1000) {
               $ranges['101..1000']++;
               continue;
            }

            if ($members_count <= 10000) {
               $ranges['1001..10000']++;
               continue;
            }

            if ($members_count <= 100000) {
               $ranges['10001..100000']++;
               continue;
            }

            if ($members_count <= 1000000) {
               $ranges['100001..1000000']++;
               continue;
            }

            $ranges['1000001..']++;
        }

        return $ranges;
    }

}
