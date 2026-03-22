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

namespace tool_usagedata\api;

use Exception;
use tool_usagedata\exception\data_collector_exception;

class data_collector_api extends api {

    /**
     * Sends data to given url
     * @param string $url The URL to send data to, usually a pre-signed URL
     * @param array $data The exported usagedata payload
     * @return bool
     * @throws data_collector_exception
     */
    public function send_export_data(string $url, array $data): bool {
        try {
            $resp = $this->put($url, $data);

            $resp->throw_if_error();
        } catch (Exception $e) {
            throw new data_collector_exception('Could not export data: ' . $e->getMessage(), 0, $e);
        }

        return true;
    }
}

