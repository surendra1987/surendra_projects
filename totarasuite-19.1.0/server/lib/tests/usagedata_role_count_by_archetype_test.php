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
 * @package core
 */

use core\usagedata\role_count_by_archetype;
use core_phpunit\testcase;

class core_usagedata_role_count_by_archetype_test extends testcase {

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_export() {
        global $DB;

        $roles = $DB->get_records('role', null, '', 'archetype, id');

        foreach ($roles as $role) {
            $roles[$role->archetype]->amount_generated = rand(0, 20);

            for ($generated_role_index = 0; $generated_role_index <= $roles[$role->archetype]->amount_generated - 1; $generated_role_index++) {
                $new_role_name = "test_$role->archetype-$generated_role_index";
                create_role($new_role_name, $new_role_name, '', $role->archetype);
            }
        }

        $export_result = (new role_count_by_archetype())->export();

        foreach ($roles as $role) {
            // +1 as each role starts with one (the archetype itself)
            $expected_role_count = $role->amount_generated + 1;
            $this->assertEquals($expected_role_count, $export_result[$role->archetype]);
        }
    }
}