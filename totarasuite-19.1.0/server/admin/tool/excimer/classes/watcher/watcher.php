<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package tool_excimer
 */

namespace tool_excimer\watcher;

use core\hook\after_config;
use core\hook\before_session_start;
use tool_excimer\manager;

/**
 * Startup hooks for excimer in the Totara lifecycle
 */
final class watcher {
    /**
     * Start the profile after the config is ready.
     */
    public static function after_config(after_config $hook): void {
        global $CFG;

        if (empty($CFG->enable_excimer)) {
            return;
        }

        // Don't boot during install or upgrade
        if (during_initial_install() || isset($CFG->upgraderunning)) {
            return;
        }

        // Start processor.
        $manager = manager::get_instance();
        $manager->start_processor();
    }

    /**
     * Record the initial time right before session starts
     *
     * @param before_session_start $hook
     * @return void
     */
    public static function before_session_start(before_session_start $hook): void {
        global $CFG;
        if (empty($CFG->enable_excimer)) {
            return;
        }
        manager::get_instance();
    }
}
