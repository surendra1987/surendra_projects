<?php
/*
 * This file is part of Totara TXP
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
 * @author  Michael Ivanov <michael.ivanov@totara.com>
 * @package totara_api
 */

use totara_webapi\controllers\external_pluginfile;
use totara_webapi\helper;

ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (defined('NO_MOODLE_COOKIES')) {
    // This should not happen, dev must be trying to include this page from elsewhere.
    die;
}

define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
define('EXTERNAL_API', true);

require(__DIR__ . '/../totara/webapi/classes/helper.php');
helper::validate_environment();

require_once(__DIR__ . '/../config.php');

(new external_pluginfile())->process('file_request');
