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

class rules_used implements export {

    public function get_summary(): string {
        return get_string('rules_used_summary', 'totara_cohort');
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

        $sql = 'SELECT CONCAT(rules.ruletype, \'-\', rules.name) type_and_name, COUNT(rules.id) as rule_count
                FROM {cohort_rules} rules
                JOIN {cohort_rulesets} rule_sets ON rule_sets.id = rules.rulesetid
                JOIN {cohort_rule_collections} rule_collections ON rule_collections.id = rule_sets.rulecollectionid
                JOIN {cohort} cohort ON cohort.activecollectionid = rule_collections.id
                WHERE cohort.cohorttype = ' . cohort::TYPE_DYNAMIC . '
                GROUP BY rules.ruletype, rules.name';
        $rules = $DB->get_records_sql_unkeyed($sql);

        $result = [];
        foreach ($rules as $rule) {
            $result[$rule->type_and_name] = (int) $rule->rule_count;
        }

        return $result;
    }
}
