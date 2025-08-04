<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => \tool_usagedata\task\export_task::class,
        'blocking' => 0,
        'minute' => 'R',
        // Any hour between 00:00 - 04:00
        'hour' => mt_rand(0, 4),
        'day' => '*',
        'month' => '*',
        // Saturday (6) or Sunday (7)
        'dayofweek' => mt_rand(6, 7),
    ],
];