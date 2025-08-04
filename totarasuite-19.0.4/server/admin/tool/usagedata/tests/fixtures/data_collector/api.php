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

if (!defined('PHPUNIT_TEST') || !PHPUNIT_TEST) {
    die();
}

/**
 * This is a test api to mock the data_collector_api
 *
 * This mock returns a 'debug' property which is simply the PHP $_POST variables.
 * This is purely developer-orientated and not part of the production-api
 */

$data = json_decode(file_get_contents("php://input"), true);

echo json_encode([
    'status' => 'OK',
    'debug' => [
        'received' => $data,
    ],
]);