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
 * @author  Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package format_pathway
 */

use container_course\hook\remove_module_hook;
use core_phpunit\testcase;
use format_pathway\watcher\remove_module_watcher;


/**
 * @group format_pathway
 */
class format_pathway_remove_module_watcher_test extends testcase {
    /**
     * @return void
     */
    public function test_remove_module_hook_does_nothing_when_course_is_not_a_pathway(): void {
        $hook = new remove_module_hook(['dc' => 'Detective Comics', 'wiki' => 'Wiki']);
        remove_module_watcher::watch($hook);

        self::assertNull($hook->get_course());

        self::assertTrue($hook->has_module('dc'));
        self::assertTrue($hook->has_module('wiki'));

        $modules = $hook->get_modules();
        self::assertNotEmpty($modules);
        self::assertCount(2, $modules);
        self::assertDebuggingNotCalled("The module 'wiki' does not exist in the list");
    }

    /**
     * @return void
     */
    public function test_remove_module_hook_removes_modules_from_blacklist(): void {
        $hook = new remove_module_hook([
            'quiz' => 'Quiz',
            'slam dunk contest' => 'Slam Dunk Contest',
            'wiki' => 'Wiki',
            'book' => 'Nook',
            'folder' => 'folder',
            'data' => 'Database',
            'label' => 'Label',
            'lesson' => 'Lesson',
        ]);
        $course = new \stdClass();
        $course->format = 'pathway';
        $hook->set_course($course);
        remove_module_watcher::watch($hook);

        self::assertNotNull($hook->get_course());
        self::assertTrue($hook->has_module('quiz'));
        self::assertTrue($hook->has_module('slam dunk contest'));

        $modules = $hook->get_modules();
        self::assertNotEmpty($modules);
        self::assertCount(2, $modules);
    }
}