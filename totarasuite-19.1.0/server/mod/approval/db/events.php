<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package mod_approval
 */

use core\event\cohort_updated;
use hierarchy_organisation\event\organisation_updated;
use hierarchy_position\event\position_updated;
use mod_approval\observer\comment_observer;
use mod_approval\observer\assignment_name_observer;
use totara_comment\event\comment_created;
use totara_comment\event\comment_soft_deleted;
use totara_comment\event\comment_updated;
use totara_comment\event\reply_created;
use totara_comment\event\reply_soft_deleted;

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => cohort_updated::class,
        'callback' => [assignment_name_observer::class, 'update_cohort_names'],
    ],
    [
        'eventname' => organisation_updated::class,
        'callback' => [assignment_name_observer::class, 'update_organisation_names'],
    ],
    [
        'eventname' => position_updated::class,
        'callback' => [assignment_name_observer::class, 'update_position_names'],
    ],
    [
        'eventname' => comment_created::class,
        'callback' => [comment_observer::class, 'comment_created'],
    ],
    [
        'eventname' => comment_updated::class,
        'callback' => [comment_observer::class, 'comment_updated'],
    ],
    [
        'eventname' => comment_soft_deleted::class,
        'callback' => [comment_observer::class, 'comment_soft_deleted'],
    ],
    [
        'eventname' => reply_created::class,
        'callback' => [comment_observer::class, 'reply_created'],
    ],
    [
        'eventname' => reply_soft_deleted::class,
        'callback' => [comment_observer::class, 'reply_soft_deleted'],
    ],
    // TODO: Handle job assignment change, tenant/user/position/organisation deletion etc.
];
