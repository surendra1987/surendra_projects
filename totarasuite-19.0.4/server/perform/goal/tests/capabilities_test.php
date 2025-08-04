<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;

global $CFG;

/**
 * Unit tests for capabilities available for interacting with Perform Goals (introduced 2023).
 */
class perform_goal_capabilities_test extends testcase {

    /**
     * Check that capabilities for perform_goal for the 'authenticated user' and 'staff manager' archetypes / roles were
     * set up in the database via an upgrade step.
     * @return void
     */
    public function test_capabilities_created_from_archetypes(): void {
        global $DB;
        $capabilities_staff_manager = ['perform/goal:viewpersonalgoals',
            'perform/goal:managepersonalgoals',
            'perform/goal:setpersonalgoalprogress'
        ];
        $capabilities_auth_user = ['perform/goal:viewownpersonalgoals',
            'perform/goal:manageownpersonalgoals',
            'perform/goal:setownpersonalgoalprogress',
        ];

        foreach ($capabilities_staff_manager as $capability) {
            $capability_found = $DB->record_exists('capabilities', [
                'name' => $capability, 'contextlevel' => CONTEXT_USER
            ]);
            $this->assertTrue($capability_found);
        }

        foreach ($capabilities_auth_user as $capability) {
            $capability_found = $DB->record_exists('capabilities', [
                'name' => $capability, 'contextlevel' => CONTEXT_USER
            ]);
            $this->assertTrue($capability_found);
        }
    }

    /**
     * @return void
     */
    public function test_role_capabilities_assigned(): void {
        global $DB;
        $params_staff_manager = [
            "role_shortname" => "staffmanager",
            "cap1" => "perform/goal:viewpersonalgoals",
            "cap2" => "perform/goal:managepersonalgoals",
            "cap3" => "perform/goal:setpersonalgoalprogress"
        ];
        $params_auth_user = [
            "role_shortname" => "user",
            "cap1" => "perform/goal:viewownpersonalgoals",
            "cap2" => "perform/goal:manageownpersonalgoals",
            "cap3" => "perform/goal:setownpersonalgoalprogress",
        ];

        $query = "select
            count(rc.capability)
        from
            {role} as r
        inner join {role_capabilities} as rc on
            rc.roleid = r.id
        where
            r.shortname = :role_shortname
            and rc.capability in (:cap1, :cap2, :cap3)";

        // Operate.
        $records_count_staff_manager = $DB->count_records_sql($query, $params_staff_manager);
        $records_count_auth_user = $DB->count_records_sql($query, $params_auth_user);

        // Assert. We expect the above capabilities to set up in the role & role_capabilities tables, linking them to the
        // correct roles.
        $this->assertGreaterThanOrEqual(3, $records_count_staff_manager);
        $this->assertGreaterThanOrEqual(3, $records_count_auth_user);
    }
}
