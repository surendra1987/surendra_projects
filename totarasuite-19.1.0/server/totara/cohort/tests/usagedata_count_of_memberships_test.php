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
use totara_cohort\usagedata\count_of_memberships;
use totara_cohort\testing\generator as cohort_generator;

class totara_cohort_usagedata_count_of_memberships_test extends testcase {

    /**
     * @throws dml_exception
     */
    public function test_export() {
        // This test is limited to three of the ranges and uses real database records
        // We limit this as testing all of the ranges would take incredibly large amounts of records

        $cohort_generator = cohort_generator::instance();

        // Range: nil (1 total)
        $cohort_generator->create_cohort();

        // Range: 1-10 (3 total)
        $audience = $cohort_generator->create_cohort();
        $this->add_cohort_members_records(1, $audience->id);

        $audience = $cohort_generator->create_cohort();
        $this->add_cohort_members_records(10, $audience->id);

        $audience = $cohort_generator->create_cohort();
        $this->add_cohort_members_records(10, $audience->id);

        // Range: 11-100 (1 total)
        $audience = $cohort_generator->create_cohort();
        $this->add_cohort_members_records(15, $audience->id);

        $result = (new count_of_memberships())->export();

        $this->assertEquals(1, $result['nil']);
        $this->assertEquals(3, $result['1..10']);
        $this->assertEquals(1, $result['11..100']);
        $this->assertEquals(0, $result['101..1000']);
        $this->assertEquals(0, $result['1001..10000']);
        $this->assertEquals(0, $result['10001..100000']);
        $this->assertEquals(0, $result['100001..1000000']);
        $this->assertEquals(0, $result['1000001..']);
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function add_cohort_members_records(int $amount_to_add, int $cohort_id): void {
        global $DB;

        $records_to_add = [];
        $user_id = 10;
        while (count($records_to_add) <= $amount_to_add - 1) {
            $records_to_add[] = [
                'userid' => $user_id,
                'cohortid' => $cohort_id,
            ];

            $user_id++;
        }

        $DB->insert_records('cohort_members', $records_to_add);
    }
}

