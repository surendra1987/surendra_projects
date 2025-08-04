<?php
/**
 * This file is part of Totara Core
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package core
 */

use core\hook\reset_theme_and_output;
use core_phpunit\testcase;
use totara_core\hook\manager;

class core_hook_reset_theme_and_output_test extends testcase {

    public function test_execute() {
        $self = $this;
        $hook = function ($hook) use ($self, &$i) {
            $self->assertInstanceOf(reset_theme_and_output::class, $hook);
        };

        $watchers = [
            [
                'hookname' => 'core\hook\reset_theme_and_output',
                'callback' => $hook,
            ],
        ];

        manager::phpunit_replace_watchers($watchers);

        $instance = new reset_theme_and_output();
        $instance->execute();

        manager::phpunit_reset();
    }
}