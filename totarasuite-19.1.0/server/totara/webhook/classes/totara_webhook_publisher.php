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

namespace totara_webhook;

use totara_core\http\clients\curl_client;
use totara_core\http\method;
use totara_core\http\request;
use totara_core\http\response;
use totara_webhook\model\totara_webhook;

class totara_webhook_publisher {
    protected static ?curl_client $client = null;

    /**
     * @param curl_client $client
     */
    public static function set_client(curl_client $client): void {
        static::$client = $client;
    }

    /**
     * @return curl_client
     */
    public static function get_client(): curl_client {
        if (static::$client === null) {
            static::set_client(new curl_client());
        }
        return static::$client;
    }

    /**
     * @throws \coding_exception
     */
    public static function publish(totara_webhook $webhook, totara_webhook_payload $payload) : response {
        $headers = [];

        $content_type = $webhook->get_content_type_adapter();
        $headers['content-type'] = $content_type->get_content_type();

        $data = $content_type->format($payload);

        $client = static::get_client();
        $client->set_timeout(10);
        $request = new request("https://{$webhook->endpoint}", method::POST, $data, $headers);

        // intercept the request to apply any auth
        $request = $webhook->authorise_request($request);

        return $client->execute($request);
    }
}
