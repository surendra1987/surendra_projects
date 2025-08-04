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
namespace format_pathway\watcher;

use container_course\hook\remove_module_hook;
use format_pathway\blacklist_helper;

class remove_module_watcher {

    /**
     * remove_module_watcher constructor.
     */
    private function __construct() {
        // Prevent this class from instantiation.
    }

    /**
     * @param remove_module_hook $hook
     * @return void
     */
    public static function watch(remove_module_hook $hook): void {
        $course = $hook->get_course();
        if ($course === null || $course->format !== 'pathway') {
            return;
        }

        $blacklist = blacklist_helper::get_blacklist();

        foreach ($blacklist as $module) {
            $hook->remove_module($module);
        }
    }
}