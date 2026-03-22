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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */

defined('MOODLE_INTERNAL') || die;

use bi_intellidata\watcher\after_require_login_watcher;
use bi_intellidata\watcher\external_pluginfile_watcher;
use core\hook\after_require_login;
use totara_webapi\hook\external_pluginfile_allowed_hook;

$watchers = [
    [
        'hookname' => external_pluginfile_allowed_hook::class,
        'callback' => [external_pluginfile_watcher::class, 'allow_external_pluginfile'],
    ],
    [
        'hookname' => after_require_login::class,
        'callback' => [after_require_login_watcher::class, 'allow_tracking'],
    ]
];