<?php

/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rami Habib <rami.habib@totaralearning.com>
 * @package block_totara_trending
 */

/* List of handlers */

$tasks = [
    [
        'classname' => 'block_totara_trending\task\refresh_totara_trending_data_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*/4',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ]
];
