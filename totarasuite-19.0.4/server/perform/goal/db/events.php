<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

use perform_goal\observer\comment_observer;
use totara_comment\event\comment_created;
use totara_comment\event\comment_updated;
use totara_comment\event\reply_created;

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => comment_created::class,
        'callback' => [comment_observer::class, 'comment_created'],
    ],
    [
        'eventname' => comment_updated::class,
        'callback' => [comment_observer::class, 'comment_updated'],
    ],
    [
        'eventname' => reply_created::class,
        'callback' => [comment_observer::class, 'reply_created'],
    ],
];