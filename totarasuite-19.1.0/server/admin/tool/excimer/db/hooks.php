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

defined('MOODLE_INTERNAL') || die();
$watchers = [
    [
        'hookname' => '\core\hook\after_config',
        'callback' => '\tool_excimer\watcher\watcher::after_config',
        'priority' => 200
    ],
    [
        'hookname' => '\core\hook\before_session_start',
        'callback' => '\tool_excimer\watcher\watcher::before_session_start',
        'priority' => 200
    ],
];
