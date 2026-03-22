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
use totara_webhook\testing\generator as totara_webhook_generator;
use totara_webhook\totara_webhook_payload as totara_webhook_payload;

class totara_webhook_totara_webhook_payload_test extends testcase {

    public function test_create_payload() {
        $generator = totara_webhook_generator::instance();
        $model = $generator->create_totara_webhook();
        $body = [$model->name, $model->endpoint];
        $time = time();

        $payload = new totara_webhook_payload(0, $body, 'test', $model->id, $time);

        $this->assertEquals($model->id, $payload->get_webhook_id());
        $this->assertEquals(0, $payload->get_attempt());
        $this->assertEquals($body, $payload->get_body());
        $this->assertEquals('test', $payload->get_event());
        $this->assertEquals($time, $payload->get_time_created());
    }

    public function test_to_request_payload() {
        $generator = totara_webhook_generator::instance();
        $model = $generator->create_totara_webhook();

        $body = [$model->name, $model->endpoint];
        $time = strtotime("10 September 2000");

        $payload = new totara_webhook_payload(0, $body, 'test', $model->id, $time);

        $result = $payload->to_request_body();

        $this->assertEquals($body, $result['body']);
        $this->assertEquals($model->id, $result['webhook_id']);
        $this->assertEquals('test', $result['event']);
        $this->assertEquals($time, $result['time_created']);
        $this->assertEquals(0, $result['attempt']);
        $this->assertEquals(time(), $result['time_sent']);

    }
}
