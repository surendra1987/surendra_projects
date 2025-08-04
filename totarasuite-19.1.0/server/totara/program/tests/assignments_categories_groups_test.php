<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_program
 */

use core_phpunit\testcase;
use totara_program\assignment\base as assignment_base;
use totara_program\assignments\assignments;
use totara_program\assignments\categories\groups;
use totara_program\entity\prog_group;
use totara_program\entity\prog_group_user;
use totara_program\testing\generator as prog_generator;

class totara_program_assignments_categories_groups_test extends testcase {
    public function test_delete_by_program() {
        $this->setAdminUser();
        $prog_generator = prog_generator::instance();

        // Set up target and control programs with group assignments.
        $program_target = $prog_generator->create_program();
        $program_control = $prog_generator->create_program();

        $group_target = $prog_generator->create_group();
        $group_control = $prog_generator->create_group();

        assignment_base::create_from_instance_id(
            $program_target->id,
            assignments::ASSIGNTYPE_GROUP,
            $group_target->id
        )->save();
        assignment_base::create_from_instance_id(
            $program_control->id,
            assignments::ASSIGNTYPE_GROUP,
            $group_control->id
        )->save();

        // Check initial conditions.
        self::assertEquals(2, prog_group::repository()->count());

        // Run the function.
        groups::delete_by_program($program_target->id);

        // Test that target data was removed and control data was not.
        self::assertEquals(1, prog_group::repository()->count());
        self::assertEquals(1, prog_group::repository()
            ->where('id', '=', $group_control->id)
            ->count()
        );
    }

    public function test_delete_by_user() {
        $this->setAdminUser();
        $prog_generator = prog_generator::instance();

        // Set up a program with a target and control users assigned in a group.
        $program = $prog_generator->create_program();
        $group = $prog_generator->create_group();

        $user_target = self::getDataGenerator()->create_user();
        $user_control = self::getDataGenerator()->create_user();

        $prog_generator->create_group_user($user_target->id, $group->id);
        $prog_generator->create_group_user($user_control->id, $group->id);

        assignment_base::create_from_instance_id(
            $program->id,
            assignments::ASSIGNTYPE_GROUP,
            $group->id
        )->save();

        // Check initial conditions.
        self::assertEquals(2, prog_group_user::repository()->count());

        // Run the function.
        groups::delete_by_user($user_target->id);

        // Check that the target prog_group_user was deleted and the control was not.
        self::assertEquals(1, prog_group_user::repository()->count());
        self::assertEquals(1, prog_group_user::repository()
            ->where('user_id', '=', $user_control->id)
            ->count()
        );
    }
}