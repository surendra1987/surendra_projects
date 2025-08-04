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

/**
 * class used for testing modify footer hook
 */
final class modify_footer_watcher {

    public static string $footer_html = "<footer>watcher_html</footer>";

    /**
     * @param \core\hook\modify_footer $hook
     * @return void
     */
    public static function modify_footer(\core\hook\modify_footer $hook): void {
        $hook->set_footer(static::$footer_html);
    }
}