<?php
/**
 * This file is part of Totara Learn
 *
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as activateed by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @package qtype_calculated
 * @category test
 */

use core_phpunit\testcase;

require_once(__DIR__ . '/../db/upgradelib.php');

class qtype_calculated_upgrade_manage_calculated_capability_test extends testcase {
    public function test_upgrade(): void {
        global $DB;

        $capability = 'moodle/question:managecalculated';
        self::assertEmpty(get_roles_with_capability($capability));

        $candidate_roles = array_map(
            function (stdClass $role): int {
                return $role->id;
            },
            get_roles_with_capability('moodle/question:add')
        );

        qtype_calculated_assign_manage_calculated_capability();

        $updated_roles = array_map(
            function (stdClass $role): int {
                return $role->id;
            },
            get_roles_with_capability($capability)
        );

        self::assertEqualsCanonicalizing($candidate_roles, $updated_roles);
    }
}
