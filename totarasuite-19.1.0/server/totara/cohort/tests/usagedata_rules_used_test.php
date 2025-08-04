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

use core_phpunit\testcase;
use totara_cohort\usagedata\rules_used;
use totara_cohort\testing\generator as cohort_generator;

class totara_cohort_usagedata_rules_used_test extends testcase {

    public function test_export() {
        $cohort_generator = cohort_generator::instance();

        $audience = $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $ruleset_id = cohort_rule_create_ruleset($audience->activecollectionid);

        // Type: `learning`, name: `test_rule` (total: 2)
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'learning',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'learning',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        // Type: `learning`, name: `a_different_name` (total: 1)
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'learning',
            'a_different_name',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        // Type: `users`, name: `test_rule` (total: 1)
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'users',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        // Type: `users`, name: `a_different_name` (total: 2)
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'users',
            'a_different_name',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'users',
            'a_different_name',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        // Only dynamic cohorts are recorded, so this should have no impact
        $audience = $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_STATIC]);
        $ruleset_id = cohort_rule_create_ruleset($audience->activecollectionid);
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'learning',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        $results = (new rules_used())->export();

        $this->assertGreaterThanOrEqual(4, count($results));

        $this->assertEquals(2, $results['learning-test_rule']);
        $this->assertEquals(1, $results['learning-a_different_name']);

        $this->assertEquals(1, $results['users-test_rule']);
        $this->assertEquals(2, $results['users-a_different_name']);
    }
}