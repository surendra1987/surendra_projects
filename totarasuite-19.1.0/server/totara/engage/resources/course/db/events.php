<?php
/**
 * This file is part of Totara Learn
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

use core\event\course_deleted;
use core\event\course_updated;
use core\event\user_deleted;
use engage_course\observer\course_observer;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

$observers = [
    [
        'eventname' => course_deleted::class,
        'callback' => [course_observer::class, 'course_deleted_observer'],
    ],
    [
        'eventname' => user_deleted::class,
        'callback' => [course_observer::class, 'user_deleted_observer']
    ],
    [
        'eventname' => course_updated::class,
        'callback' => [course_observer::class, 'course_updated']
    ]
];
