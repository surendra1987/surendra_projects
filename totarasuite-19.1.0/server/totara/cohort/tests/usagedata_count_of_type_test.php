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
use totara_cohort\usagedata\count_of_type;
use totara_cohort\testing\generator as cohort_generator;

class totara_cohort_usagedata_count_of_type_test extends testcase {

    public function test_export() {
        $cohort_generator = cohort_generator::instance();

        // Static (Total: 5)
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_STATIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_STATIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_STATIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_STATIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_STATIC]);

        // Dynamic (Total: 6)
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);
        $cohort_generator->create_cohort(['cohorttype' => cohort::TYPE_DYNAMIC]);


        $results = (new count_of_type())->export();

        $this->assertEquals(5, $results['static']);
        $this->assertEquals(6, $results['dynamic']);
    }
}