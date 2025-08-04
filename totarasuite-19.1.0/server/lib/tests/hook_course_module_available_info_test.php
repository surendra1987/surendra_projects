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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

use core\hook\course_module_available_info;
use core_phpunit\testcase;

class core_hook_course_module_available_info_test extends testcase {

    /**
     * Testing the hook when triggered calls the watchers and
     * allows the watchers to update hook
     *
     * @return void
     */
    public function test_course_module_available_info_hook(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/tests/fixtures/course_module_available_info_watcher.php");

        // Set up the watcher.
        $watchers = [
            [
                'hookname' => course_module_available_info::class,
                'callback' => [course_module_available_info_watcher::class, 'watch'],
            ],
        ];

        // replace the watchers with our test watcher
        \totara_core\hook\manager::phpunit_replace_watchers($watchers);

        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);
        // Trigger the hook.
        $hook = new course_module_available_info($course->format, 'data');
        $hook->execute();

        $this->assertEquals('invalid info', $hook->get_available_info());
    }
}