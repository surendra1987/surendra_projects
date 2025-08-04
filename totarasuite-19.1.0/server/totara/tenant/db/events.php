<?php
/**
 * This file is part of Totara Learn
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package totara_tenant
 */

use totara_core\event\user_undeleted;
use totara_core\event\user_unsuspended;
use totara_tenant\totara_tenant_observer;

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => user_unsuspended::class,
        'callback' => [totara_tenant_observer::class, 'user_unsuspended'],
    ],
    [
        'eventname' => user_undeleted::class,
        'callback' => [totara_tenant_observer::class, 'user_undeleted'],
    ],
];
