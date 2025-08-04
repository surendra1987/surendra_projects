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

defined('MOODLE_INTERNAL') || die();

class core_hook_set_page_layout_test extends \core_phpunit\testcase {

    /**
     * Test that the watcher can change the layout
     * @return void
     * @throws coding_exception
     */
    public function test_set_page_layout(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/set_page_layout_watcher.php");

        // setup the watchers
        $watchers = [
            [
                'hookname' => \core\hook\set_page_layout::class,
                'callback' => [set_page_layout_watcher::class, 'change_layout'],
            ],
        ];

        // replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        // trigger the hook
        $hook = new \core\hook\set_page_layout('original');
        $hook->execute();

        $this->assertSame(set_page_layout_watcher::$watcher_layout, $hook->get_page_layout());
    }

    /**
     * Test that a page calling the set_pagelayout function is setting the layout correctly via hooks
     * @return void
     * @throws coding_exception
     */
    public function test_pagelib_hook_execution(): void {
        global $CFG, $SESSION;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/set_page_layout_watcher.php");

        // setup the watchers
        $watchers = [
            [
                'hookname' => \core\hook\set_page_layout::class,
                'callback' => [set_page_layout_watcher::class, 'change_layout'],
            ],
        ];

        // replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        $page = new moodle_page();
        $page->set_pagelayout('original_test_page_layout');
        $this->assertSame(set_page_layout_watcher::$watcher_layout,$page->pagelayout);

        $original_sess_forcepagelayout = $SESSION->forcepagelayout ?? null;
        $SESSION->forcepagelayout = 'forced_session_layout';
        $page->set_pagelayout('original_test_page_layout');
        $this->assertSame('forced_session_layout', $page->pagelayout);
        $SESSION->forcepagelayout = $original_sess_forcepagelayout;
    }
}