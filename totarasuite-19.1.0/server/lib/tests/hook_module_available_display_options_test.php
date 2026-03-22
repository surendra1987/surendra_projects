<?php
/**
 * This file is part of Totara LMS
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

use core\hook\module_available_display_options;
use core_phpunit\testcase;
use totara_core\hook\manager as hook_manager;

class core_hook_module_available_display_options_test extends testcase {

    /**
     * Testing the hook when triggered calls the watchers and
     * allows the watchers to update hook
     *
     * @return void
     */
    public function test_module_available_display_options_hook(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/module_available_display_options_watcher.php");

        // Set up the watcher.
        $watchers = [
            [
                'hookname' => module_available_display_options::class,
                'callback' => [module_available_display_options_watcher::class, 'watch'],
            ],
        ];

        // Replace the watchers with our test watcher.
        hook_manager::phpunit_replace_watchers($watchers);

        // Trigger the hook.
        $hook = new module_available_display_options([1, 2, 3], 'some_name', 123);
        $hook->execute();

        $this->assertEquals([4, 5, 6], $hook->available_options);
    }
}