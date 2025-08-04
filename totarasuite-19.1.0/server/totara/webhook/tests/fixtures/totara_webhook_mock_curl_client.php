<?php
/**
 * This file is part of Totara TXP
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Kelsey Scheurich <kelsey.scheurich@totara.com>
 * @package totara_webhook
 */

namespace fixtures;

use totara_core\http\clients\curl_client;
use totara_core\http\request;
use totara_core\http\response;

defined('MOODLE_INTERNAL')||die();

class totara_webhook_mock_curl_client extends curl_client {

    public bool $executed = false;
    public request $request;

    public function execute(request $request): response {
        $this->executed = true;
        $this->request = $request;
        return new response("body", 200, []);
    }
}