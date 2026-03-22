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

use core_phpunit\testcase;
use fixtures\totara_webhook_mock_curl_client;
use totara_webhook\testing\generator as totara_webhook_generator;
use totara_webhook\totara_webhook_publisher;

require __DIR__ ."/fixtures/totara_webhook_mock_curl_client.php";

class totara_webhook_totara_webhook_publisher_test extends testcase {
    /**
     * @throws coding_exception
     */
    public function test_publish() : void {
        $generator = totara_webhook_generator::instance();
        $model = $generator->create_totara_webhook();
        $payload = $generator->create_totara_webhook_payload(['webhook_id' => $model->id]);

        $client = new totara_webhook_mock_curl_client();
        totara_webhook_publisher::set_client($client);
        $result = totara_webhook_publisher::publish($model, $payload);
        /** @var totara_webhook_mock_curl_client $client_called */
        $client_called = totara_webhook_publisher::get_client();

        $this->assertTrue($client_called->executed);
        $headers = $client_called->request->get_headers();
        $headers = join(',', $headers);
        $this->assertTrue(str_contains($headers, 'X-Totara-Signature'));
    }
}
