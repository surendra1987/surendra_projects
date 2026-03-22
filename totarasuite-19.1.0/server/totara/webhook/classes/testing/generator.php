<?php

/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package totara_webhook
 * @author ben fesili <ben.fesili@totara.com>
 */

namespace totara_webhook\testing;

use core\testing\component_generator;
use totara_webhook\auth\webhook_hmac_auth;
use totara_webhook\content_type\json_adapter;
use totara_webhook\model\totara_webhook;
use totara_webhook\totara_webhook_payload;

require_once __DIR__ . '/../../tests/fixtures/mock_webhook_auth.php';

/**
  * Webhook model generator
  */
final class generator extends component_generator {
    public function create_totara_webhook(array $data = []): totara_webhook {

        $name = $data['name'] ?? 'abc';
        $endpoint = $data['endpoint'] ?? 'abc';
        $events = $data['events'] ?? null;
        $immediate = $data['immediate'] ?? false;
        $content_type_adapter = $data['content_type_adapter'] ?? json_adapter::class;
        $status = $data['status'] ?? 1;
        $auth_class = $data['auth_class'] ?? webhook_hmac_auth::class;

        return totara_webhook::create(
            $name,
            $endpoint,
            $status,
            $immediate,
            $events,
            $content_type_adapter,
            $auth_class
        );
    }

    public function create_totara_webhook_payload(array $data = []): totara_webhook_payload {
        $time_created = $data['time_created'] ?? time();
        $event = $data['event'] ?? 'test';
        $body = $data['body'] ?? ['abc', 'abc'];
        $attempt = $data['attempt'] ?? 0;
        $webhook_id = $data['webhook_id'] ?? 1;
        return new totara_webhook_payload(
            $attempt,
            $body,
            $event,
            $webhook_id,
            $time_created
        );
    }
}
