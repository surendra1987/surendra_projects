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

defined('MOODLE_INTERNAL') || die();

/**
 * class used for testing before_output_start hook
 */
final class before_output_start_watcher {

    public static string $footer_html = "<footer>watcher_html</footer>";

    /**
     * @param \core\hook\before_output_start $hook
     * @return void
     */
    public static function before_output_start(\core\hook\before_output_start $hook): void {
        // We need a way to signal to the tests that this function was run. The test can expect this exception.
        throw new coding_exception('before_output_start watcher successfully executed');
    }
}