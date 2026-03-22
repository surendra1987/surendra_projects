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
use perform_goal\model\target_type\target_type;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\entity\goal as goal_entity;

/**
 * Unit tests for the goal target_type class.
 */
class perform_goal_target_type_test extends testcase {

    public function test_invalid_when_type_does_not_exist(): void {
        $bad_type = "IDon'tExist";
        self::expectExceptionMessage("The '{$bad_type}' target type class must exist");
        $target_type1 = target_type::by_type($bad_type);
    }

    public function test_invalid_when_goal_target_type_does_not_exist(): void {
        self::setAdminUser();
        $bad_target_type = "IDon'tExist";
        $config1 = goal_generator_config::new([
            'name' => 'Test goal custom name1' . time(),
            'target_type' => 'date'
        ]);
        $test_goal1 = goal_generator::instance()->create_goal($config1);
        $test_goal_entity = goal_entity::repository()->find($test_goal1->id); // alter the goal to have a bad value.
        $test_goal_entity->target_type = $bad_target_type;
        $test_goal_entity->save();

        self::expectExceptionMessage("The '{$bad_target_type}' target type class must exist");
        $target_type1 = target_type::for_goal($test_goal1->refresh());
    }

    public function test_label_not_defined(): void {
        // Let's create a concrete class that does not have its own 'get_label' method (i.e. is undefined).
        $dummy_target_type = (new class extends target_type {
            public static function get_type() {
                return 'dummy';
            }
            public function get_progress_percent() {
                return 0.0;
            }
            public function get_progress_raw() {
                return 1;
            }
        });

        $target_type = new $dummy_target_type();
        // Operate.
        $label_result = $target_type->get_label();
        // Assert.
        self::assertEquals('not defined', $label_result);
    }
}
