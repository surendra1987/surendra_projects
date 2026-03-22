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

class count_of_rules implements export {

    public function get_summary(): string {
        return get_string('count_of_rules_summary', 'totara_cohort');
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
        global $CFG, $DB;

        require_once $CFG->dirroot . '/cohort/lib.php';


        $sql = 'SELECT cohort.id, COUNT(rules.id) AS rule_count
                FROM {cohort} cohort
                LEFT JOIN {cohort_rule_collections} rule_collections ON rule_collections.id = cohort.activecollectionid
                LEFT JOIN {cohort_rulesets} rule_sets ON rule_sets.rulecollectionid = rule_collections.id
                LEFT JOIN {cohort_rules} rules ON rules.rulesetid = rule_sets.id
                WHERE cohort.cohorttype = ' . cohort::TYPE_DYNAMIC . '
                GROUP BY cohort.id';
        $rules = $DB->get_records_sql($sql);

        $ranges = [
            'nil' => 0,
            '1..2' => 0,
            '3..5' => 0,
            '6..10' => 0,
            '11..100' => 0,
            '101..1000' => 0,
            '1001..10000' => 0,
            '10001..100000' => 0,
            '100001..1000000' => 0,
            '1000001..' => 0,
        ];
        
        foreach ($rules as $rule) {
            $rule->rule_count = (int) $rule->rule_count;

            if ($rule->rule_count === 0) {
                $ranges['nil']++;
                continue;
            }

            if ($rule->rule_count <= 2) {
               $ranges['1..2']++;
               continue;
            }

            if ($rule->rule_count <= 5) {
               $ranges['3..5']++;
               continue;
            }

            if ($rule->rule_count <= 10) {
               $ranges['6..10']++;
               continue;
            }

            if ($rule->rule_count <= 100) {
               $ranges['11..100']++;
               continue;
            }

            if ($rule->rule_count <= 1000) {
               $ranges['101..1000']++;
               continue;
            }

            if ($rule->rule_count <= 10000) {
               $ranges['1001..10000']++;
               continue;
            }

            if ($rule->rule_count <= 100000) {
               $ranges['10001..100000']++;
               continue;
            }

            if ($rule->rule_count <= 1000000) {
               $ranges['100001..1000000']++;
               continue;
            }

            $ranges['1000001..']++;
        }

        return $ranges;
    }

}
