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
use totara_cohort\usagedata\count_of_rules;
use totara_cohort\testing\generator as cohort_generator;

class totara_cohort_usagedata_count_of_rules_test extends testcase {

    public function test_export() {
        // This test is limited to three of the ranges and uses real database records

        $cohort_generator = cohort_generator::instance();

        // Static cohort should not affect result
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_STATIC]);

        // Range nil (total 2)
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);

        // Range 1-2 (total 3)
        // Audience with 1 rule
        $audience = $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $ruleset_id = cohort_rule_create_ruleset($audience->activecollectionid);
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'learning',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        // Audience with 1 rule
        $audience = $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $ruleset_id = cohort_rule_create_ruleset($audience->activecollectionid);
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'learning',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        // Audience with 2 rules
        $audience = $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $ruleset_id = cohort_rule_create_ruleset($audience->activecollectionid);
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

        // Range 3-5 (Total 2)
        // Audience with 3 rules
        $audience = $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $ruleset_id = cohort_rule_create_ruleset($audience->activecollectionid);
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'learning',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'user',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'user',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        // Audience with 4 rules
        $audience = $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $ruleset_id = cohort_rule_create_ruleset($audience->activecollectionid);
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'learning',
            'test_rule',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'user',
            'test_rule_audience_2',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'user',
            'test_rule_audience_2',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );
        $cohort_generator->create_cohort_rule_params($ruleset_id,
            'user',
            'test_rule_audience_2',
            ['operator' => COHORT_RULE_COMPLETION_OP_ALL],
            []
        );

        $result = (new count_of_rules())->export();

        $this->assertEquals(2, $result['nil']);
        $this->assertEquals(3, $result['1..2']);
        $this->assertEquals(2, $result['3..5']);
        $this->assertEquals(0, $result['6..10']);
        $this->assertEquals(0, $result['11..100']);
        $this->assertEquals(0, $result['101..1000']);
        $this->assertEquals(0, $result['1001..10000']);
        $this->assertEquals(0, $result['10001..100000']);
        $this->assertEquals(0, $result['100001..1000000']);
        $this->assertEquals(0, $result['1000001..']);
    }
}

