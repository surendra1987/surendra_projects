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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package core
 */

/**
 * tests for the modify_header hook
 */
class core_hook_modify_header_test extends \core_phpunit\testcase {

    /**
     * Testing the hook when triggered calls the watchers and
     * allows the watchers to update the content of the header
     *
     * @return void
     */
    public function test_modify_header_hook(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/modify_header_watcher.php");

        // setup the watchers
        $watchers = [
            [
                'hookname' => \core\hook\modify_header::class,
                'callback' => [modify_header_watcher::class, 'change_header'],
            ],
        ];

        // replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        // trigger the hook
        $hook = new \core\hook\modify_header("<div>original</div>");
        $hook->execute();

        $this->assertSame(modify_header_watcher::$header_html, $hook->get_header());
    }

    /**
     * Testing the hook is triggered by output renderers header func
     *
     * @return void
     */
    public function test_modify_header_hook_execution(): void {
        global $CFG, $PAGE;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/modify_header_watcher.php");

        $PAGE->set_url('/admin/tool/phpunit/index.php');

        // setup the watchers
        $watchers = [
            [
                'hookname' => \core\hook\modify_header::class,
                'callback' => [modify_header_watcher::class, 'change_header'],
            ],
        ];

        // replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        $page = new moodle_page();
        $page->set_url(new moodle_url('/filter/mediaplugin/dev/perftest.php'));
        $output_renderer = new core_renderer($page, new moodle_url('/filter/mediaplugin/dev/perftest.php'));
        $header = $output_renderer->header();
        $this->assertStringContainsString(modify_header_watcher::$header_html, $header);
    }
}