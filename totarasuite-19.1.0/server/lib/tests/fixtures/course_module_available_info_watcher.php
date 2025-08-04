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
use format_pathway\blacklist_helper;

class course_module_available_info_watcher {

    public static function watch(course_module_available_info $hook): void {
        if ($hook->get_course_format() === 'pathway' && in_array($hook->get_mod_name(), blacklist_helper::get_blacklist())) {
            $hook->set_available_info('invalid info');
        }
    }
}