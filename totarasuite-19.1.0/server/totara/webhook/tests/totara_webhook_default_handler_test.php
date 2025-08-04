<?php
/**
 * This file is part of Totara Core
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

use core_phpunit\testcase;
use totara_webhook\handler\default_handler\default_handler;
use totara_webhook\handler\default_handler\entity\totara_webhook_payload_queue;
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_default_handler_test extends testcase {

    /**
     * Test the correct payloads are found for processing
     */
    public function test_get_payloads_to_send(): void {
        $default_handler = new default_handler();
        $webhook_generator = totara_webhook_generator::instance();
        $webhook = $webhook_generator->create_totara_webhook([
            'name' => 'test_webhook',
            'endpoint' => 'example.com/webhook'
        ]);

        // setup some payloads to send:
        // webhook - brand new - ready to send
        $webhook_payload = new totara_webhook_payload_queue();
        $webhook_payload->next_send = null;
        $webhook_payload->body = 'to_send';
        $webhook_payload->attempt = 0;
        $webhook_payload->time_sent = null;
        $webhook_payload->webhook_id = $webhook->id;
        $webhook_payload->time_created = time();
        $webhook_payload->event = 'test_event';
        $webhook_payload->save();

        // previously sent but failed - ready to send
        $webhook_payload = new totara_webhook_payload_queue();
        $webhook_payload->next_send = time() - 1;
        $webhook_payload->body = 'to_send';
        $webhook_payload->attempt = 1;
        $webhook_payload->time_sent = null; // time_sent not set until successful save
        $webhook_payload->webhook_id = $webhook->id;
        $webhook_payload->time_created = time();
        $webhook_payload->event = 'test_event';
        $webhook_payload->save();

        // previously sent but failed 9 times - not ready for retry
        $webhook_payload = new totara_webhook_payload_queue();
        $webhook_payload->next_send = time() + (60 * 60 * 24);
        $webhook_payload->body = 'not_ready';
        $webhook_payload->attempt = 1;
        $webhook_payload->time_sent = null; // time_sent not set until successful save
        $webhook_payload->webhook_id = $webhook->id;
        $webhook_payload->time_created = time();
        $webhook_payload->event = 'test_event';
        $webhook_payload->save();

        // previously sent but failed, resend was successful
        $webhook_payload = new totara_webhook_payload_queue();
        $webhook_payload->next_send = time() - 1;
        $webhook_payload->body = 'sent';
        $webhook_payload->attempt = 2;
        $webhook_payload->time_sent = time(); // time_sent not set until successful save
        $webhook_payload->webhook_id = $webhook->id;
        $webhook_payload->time_created = time();
        $webhook_payload->event = 'test_event';
        $webhook_payload->save();

        // successful on first-attempt
        $webhook_payload = new totara_webhook_payload_queue();
        $webhook_payload->next_send = null;
        $webhook_payload->body = 'sent';
        $webhook_payload->attempt = 1;
        $webhook_payload->time_sent = time(); // time_sent not set until successful save
        $webhook_payload->webhook_id = $webhook->id;
        $webhook_payload->time_created = time();
        $webhook_payload->event = 'test_event';
        $webhook_payload->save();

        $payloads_to_send = $default_handler->get_payloads_to_send();
        $this->assertCount(2, $payloads_to_send);
        /** @var totara_webhook_payload_queue $payload */
        foreach ($payloads_to_send as $payload) {
            $this->assertSame('to_send', $payload->body);
        }
    }
}
