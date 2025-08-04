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
use perform_goal\entity\goal as goal_entity;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\model\target_type\target_type;
use perform_goal\model\target_type\date as date_target_type_model;

/**
 * Unit tests for the date target_type class for Perform.
 */
class perform_goal_target_type_date_test extends testcase {
    const MONTHSECS = YEARSECS / 12;

    public function test_creation_by_type(): void {
        $target_type1 = target_type::by_type('date');
        self::assertEquals(date_target_type_model::class, $target_type1);
    }

    public function test_static_creation_by_goal(): void {
        self::setAdminUser();
        $config1 = goal_generator_config::new([
            'name' => 'Test goal custom name1' . time(),
            'target_type' => date_target_type_model::get_type() // i.e. 'date'
        ]);
        $test_goal1 = goal_generator::instance()->create_goal($config1);

        $target_type1 = target_type::for_goal($test_goal1); // using the  abstract base class static method for creation.
        self::assertInstanceOf(date_target_type_model::class, $target_type1);
    }

    public function test_constructor_creation_by_goal(): void {
        self::setAdminUser();
        $config2 = goal_generator_config::new([
            'name' => 'Test goal custom name2' . time(),
            'target_type' => date_target_type_model::get_type() // i.e. 'date'
        ]);
        $test_goal2 = goal_generator::instance()->create_goal($config2);

        $target_type2 = new date_target_type_model($test_goal2); // using the concrete class constructor for creation.
        self::assertInstanceOf(date_target_type_model::class, $target_type2);
    }

    public function test_get_label(): void {
        $target_type = new date_target_type_model();
        $label = $target_type::get_label();

        self::assertEquals('Date', $label);
    }

    public function test_get_type(): void {
        $type = date_target_type_model::get_type();

        self::assertEquals('date', $type);
    }

    public function test_get_progress_percent_with_valid_inputs(): void {
        self::setAdminUser();
        // Create a goal with a target_type, start date and target date.
        $config = goal_generator_config::new([
            'name' => 'Test goal custom name' . time(),
            'start_date' => time() - (self::MONTHSECS * 2), // 2 months in the past.
            'target_type' => date_target_type_model::get_type(), // i.e. 'date'
            'target_date' => time() + (self::MONTHSECS * 3) // 3 months in the future.
        ]);
        $test_goal = goal_generator::instance()->create_goal($config);
        $target_type = target_type::for_goal($test_goal);

        // Operate.
        $result = $target_type->get_progress_percent();

        // Assert.
        self::assertEqualsWithDelta(40.0, $result, 0.0001);
    }

    public function test_get_progress_percent_with_invalid_inputs(): void {
        self::setAdminUser();
        $error_msg_prefix = 'Coding error detected, it must be fixed by a programmer: ';

        // 1. Check for a target_type with no goal (i.e. so can't calculate progress_percent)
        $target_type = new date_target_type_model();
        try {
            $result = $target_type->get_progress_percent(); // operate
        } catch (Exception $exc) {
            $msg = $error_msg_prefix . "The goal is missing or not set on the target_type: " . date_target_type_model::get_type() . ".";
            self::assertEquals($msg, $exc->getMessage());
        }

        // 2. Check for a target_type with a goal but invalid start/target_dates (i.e. so can't calculate progress_percent)
        $config = goal_generator_config::new([
            'name' => 'Test goal custom name' . time(),
            'start_date' => time() - (self::MONTHSECS * 2), // 2 months in the past.
            'target_type' => date_target_type_model::get_type(), // i.e. 'date'
            'target_date' => time()
        ]);
        $test_goal = goal_generator::instance()->create_goal($config);
        $test_goal_entity = goal_entity::repository()->find($test_goal->id); // alter the goal to have a bad value.
        $test_goal_entity->target_date = time() - (self::MONTHSECS * 4); // 4 months in the past, before the start date.
        $test_goal_entity->save();

        $target_type = target_type::for_goal($test_goal->refresh());
        try {
            $result = $target_type->get_progress_percent(); // operate
        } catch (Exception $exc) {
            $msg = $error_msg_prefix . 'One of the date inputs is invalid, e.g. the target_date is before the start_date.';
            self::assertEquals($msg, $exc->getMessage());
        }
    }

    public function test_get_progress_raw(): void {
        self::setAdminUser();
        $config = goal_generator_config::new([
            'name' => 'Test goal custom name' . time(),
            'start_date' => time() - (self::MONTHSECS * 2), // 2 months in the past.
            'target_type' => date_target_type_model::get_type(), // i.e. 'date'
            'target_date' => time() + self::MONTHSECS
        ]);
        $test_goal = goal_generator::instance()->create_goal($config);
        $target_type = new date_target_type_model($test_goal);
        $result = $target_type->get_progress_raw(); // operate

        $expected_progress_timestamp = time();
        self::assertGreaterThanOrEqual($expected_progress_timestamp, $result); // time should be close to now.
    }
}
