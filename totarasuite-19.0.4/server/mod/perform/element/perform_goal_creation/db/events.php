<?php
/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package performelement_perform_goal_creation
 */

use perform_goal\event\personal_goal_details_updated;
use perform_goal\event\personal_goal_status_updated;
use performelement_perform_goal_creation\observer\perform_goal_updated;

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => personal_goal_details_updated::class,
        'callback' => perform_goal_updated::class.'::goal_updated',
    ],
    [
        'eventname' => personal_goal_status_updated::class,
        'callback' => perform_goal_updated::class.'::goal_updated',
    ],
];