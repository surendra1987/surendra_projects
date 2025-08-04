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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\model\status\cancelled;
use perform_goal\model\status\completed;
use perform_goal\model\status\in_progress;
use perform_goal\model\status\not_started;

/**
 * Unit tests for the perform_goal core status classes.
 *
 * @group perform_goal
 */
class perform_goal_statuses_test extends testcase {

    public static function core_statuses_data_provider(): array {
        return [
            [not_started::class, 'not_started', 'Not started'],
            [in_progress::class, 'in_progress', 'In progress'],
            [completed::class, 'completed', 'Completed'],
            [cancelled::class, 'cancelled', 'Cancelled'],
        ];
    }

    /**
     * @dataProvider core_statuses_data_provider
     */
    public function test_get_code($class, $code, $label) {
        $instance = new $class();
        $this->assertEquals($code, $instance->get_code());
    }

    /**
     * @dataProvider core_statuses_data_provider
     */
    public function test_get_code_static($class, $code, $label) {
        $this->assertEquals($code, $class::get_code());
    }

    /**
     * @dataProvider core_statuses_data_provider
     */
    public function test_stringify($class, $code, $label) {
        $instance = new $class();
        $this->assertEquals($code, $instance);
    }

    /**
     * @dataProvider core_statuses_data_provider
     */
    public function test_get_label($class, $code, $label) {
        $this->assertEquals($label, $class::get_label());
    }

    /**
     * @dataProvider core_statuses_data_provider
     */
    public function test_instance_properties($class, $code, $label) {
        $instance = new $class();
        $this->assertEquals($code, $instance->id);
        $this->assertEquals($label, $instance->label);
    }

    public static function core_status_types_data_provider(): array {
        return [
            [not_started::class, 'not_started' => true, 'in_progress' => false, 'achieved' => false, 'closed' => false],
            [in_progress::class, false, true, false, false],
            [completed::class, false, false, true, true],
            [cancelled::class, false, false, false, true],
        ];
    }

    /**
     * @dataProvider core_status_types_data_provider
     */
    public function test_status_types($class, $not_started, $in_progress, $achieved, $closed) {
        $this->assertEquals($not_started, $class::is_not_started());
        $this->assertEquals($in_progress, $class::is_in_progress());
        $this->assertEquals($achieved, $class::is_achieved());
        $this->assertEquals($closed, $class::is_closed());
    }
}
