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

/**
 * tests for the before_output_start hook
 */
class core_hook_before_output_start_test extends \core_phpunit\testcase {

    /**
     * Testing the hook when triggered calls the watchers and
     * allows the watchers to update the data
     *
     * @return void
     */
    public function test_before_output_start_hook(): void {
        global $CFG, $PAGE;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/before_output_start_watcher.php");

        // Set up the watchers
        $watchers = [
            [
                'hookname' => \core\hook\before_output_start::class,
                'callback' => [before_output_start_watcher::class, 'before_output_start'],
            ],
        ];

        // Replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        // Trigger the hook
        $hook = new \core\hook\before_output_start($PAGE);
        $hook->execute();

        // Check that the watcher was executed.
        self::assertDebuggingCalled(null, null, 'before_output_start watcher successfully executed');
    }

    /**
     * Testing the hook is triggered by xyz func
     *
     * @return void
     */
    public function test_before_output_start_hook_execution(): void {
        global $CFG, $PAGE;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/before_output_start_watcher.php");

        $PAGE->set_url('/admin/tool/phpunit/index.php');

        // Set up the watchers
        $watchers = [
            [
                'hookname' => \core\hook\before_output_start::class,
                'callback' => [before_output_start_watcher::class, 'before_output_start'],
            ],
        ];

        // Replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        $page = new moodle_page();
        $page->set_url(new moodle_url('/filter/mediaplugin/dev/perftest.php'));
        $output_renderer = new core_renderer($page , new moodle_url('/filter/mediaplugin/dev/perftest.php'));
        $output_renderer->header(); // must be called to get state into the correct position

        // Check that the watcher was executed.
        self::assertDebuggingCalled(null, null, 'before_output_start watcher successfully executed');
    }
}