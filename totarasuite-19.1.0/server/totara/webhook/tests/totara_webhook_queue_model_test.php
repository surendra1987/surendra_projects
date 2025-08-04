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
use totara_webhook\totara_webhook_payload;
use totara_webhook\handler\default_handler\model\totara_webhook_payload_queue as webhook_payload_queue_model;
use totara_webhook\handler\default_handler\entity\totara_webhook_payload_queue as webhook_payload_queue_entity;

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_queue_model_test extends testcase {

    /**
     * @throws coding_exception
     */
    public function test_queue_model_get_totara_webhook(): void {
        $generator = totara_webhook_generator::instance();
        $webhook_model = $generator->create_totara_webhook();
        $webhook_payload = $generator->create_totara_webhook_payload(['webhook_id' => $webhook_model->id]);

        $queue_model = webhook_payload_queue_model::create_from_totara_webhook_payload($webhook_payload);

        $retrieved_webhook_model = $queue_model->totara_webhook();

        $this->assertEquals($retrieved_webhook_model, $webhook_model);

        $entity = new webhook_payload_queue_entity();
        $entity->body = "body";
        $entity->attempt = 0;
        $entity->event = "cool event";
        $id = $webhook_payload->get_webhook_id(); // FK constraint, otherwise I would make it up
        $entity->webhook_id = $id;
        $time = time();
        $entity->time_created = $time;
        $entity->save();

        $queue_model2 = webhook_payload_queue_model::load_by_entity($entity);

        $this->assertInstanceOf(webhook_payload_queue_model::class, $queue_model2);

        $this->assertEquals("body", $queue_model2->body);
        $this->assertEquals("cool event", $queue_model2->event);
        $this->assertEquals($time, $queue_model2->time_created);
        $this->assertEquals($id, $queue_model2->webhook_id);
    }

    /**
     * @throws coding_exception
     */
    public function test_queue_model_convert_to_totara_webhook_payload(): void {
        $generator = totara_webhook_generator::instance();
        $webhook_model = $generator->create_totara_webhook();
        $webhook_payload = $generator->create_totara_webhook_payload(['webhook_id' => $webhook_model->id]);

        $queue_model = webhook_payload_queue_model::create_from_totara_webhook_payload($webhook_payload);

        $converted_payload = $queue_model->convert_to_totara_webhook_payload();

        $this->assertInstanceOf(totara_webhook_payload::class, $converted_payload);
        $this->assertEquals($converted_payload, $webhook_payload);

    }

    /**
     * @throws coding_exception
     */
    public function test_queue_model_create_from_totara_webhook_payload(): void {
        $generator = totara_webhook_generator::instance();
        $webhook_model = $generator->create_totara_webhook();
        $webhook_payload = $generator->create_totara_webhook_payload(['webhook_id' => $webhook_model->id]);

        $queue_model = webhook_payload_queue_model::create_from_totara_webhook_payload($webhook_payload);

        $this->assertInstanceOf(webhook_payload_queue_model::class, $queue_model);
    }

    /**
     * @throws coding_exception
     */
    public function test_queue_model_increment_attempt(): void {
        $generator = totara_webhook_generator::instance();
        $webhook_model = $generator->create_totara_webhook();
        $webhook_payload = $generator->create_totara_webhook_payload(['webhook_id' => $webhook_model->id]);

        $queue_model = webhook_payload_queue_model::create_from_totara_webhook_payload($webhook_payload);

        $this->assertEquals(0, $queue_model->get_attempt());

        $queue_model->increment_attempt();

        $this->assertEquals(1, $queue_model->get_attempt());
    }

    /**
     * @throws coding_exception
     */
    public function test_queue_model_mark_sent(): void {
        $generator = totara_webhook_generator::instance();
        $webhook_model = $generator->create_totara_webhook();
        $webhook_payload = $generator->create_totara_webhook_payload(['webhook_id' => $webhook_model->id]);

        $queue_model = webhook_payload_queue_model::create_from_totara_webhook_payload($webhook_payload);

        $this->assertNull($queue_model->time_sent);

        $queue_model->mark_sent();

        $this->assertEquals(time(), $queue_model->time_sent);
    }

    /**
     * @throws coding_exception
     */
    public function test_queue_model_requeue(): void {
        $generator = totara_webhook_generator::instance();
        $webhook_model = $generator->create_totara_webhook();
        $webhook_payload = $generator->create_totara_webhook_payload(['webhook_id' => $webhook_model->id]);

        $queue_model = webhook_payload_queue_model::create_from_totara_webhook_payload($webhook_payload);

        $this->assertEquals(0, $queue_model->get_attempt());
        $this->assertNull($queue_model->next_send);

        $queue_model->increment_attempt();
        $queue_model->requeue();

        $this->assertEquals(1, $queue_model->get_attempt());
        $this->assertEquals((time() + (60 * 1)), $queue_model->next_send);
    }
}
