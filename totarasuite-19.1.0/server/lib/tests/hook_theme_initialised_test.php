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

class core_hook_theme_initialised_test extends \core_phpunit\testcase {

    /**
     * Ensure the hook is triggered and the page layout is updated
     *
     * @return void
     * @throws coding_exception
     */
    public function test_hook_updates_page_layout_correctly(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/theme_initialised_watcher.php");
        // setup the watcher
        $watchers = [
            [
                'hookname' => \core\hook\theme_initialised_for_page_layout::class,
                'callback' => [theme_initialised_watcher::class, 'update_layout'],
            ],
        ];

        // replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        // setup the page and trigger the hook
        $page = new moodle_page();
        $page->set_pagelayout('popup');
        $page->initialise_theme_and_output();
        $this->assertSame(theme_initialised_watcher::$watcher_layout, $page->pagelayout);
    }

    /**
     * ensure that if initialised was called again the hook would not be triggered to overwrite
     * the page layout again
     *
     * @return void
     * @throws coding_exception
     */
    public function test_hook_will_only_trigger_once(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/theme_initialised_watcher.php");
        // setup the watcher
        $watchers = [
            [
                'hookname' => \core\hook\theme_initialised_for_page_layout::class,
                'callback' => [theme_initialised_watcher::class, 'update_layout'],
            ],
        ];

        // replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        // setup the page and trigger the hook
        $page = new moodle_page();
        $page->set_pagelayout('popup');
        $page->initialise_theme_and_output();
        $this->assertSame(theme_initialised_watcher::$watcher_layout, $page->pagelayout);

        $page->set_pagelayout('popup');
        $page->initialise_theme_and_output();
        $this->assertSame('popup', $page->pagelayout);
    }

}