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

namespace totara_webhook\handler\default_handler\model;

use core\orm\entity\model;
use totara_webhook\handler\default_handler\entity\totara_webhook_payload_queue as totara_webhook_payload_queue_entity;
use totara_webhook\model\totara_webhook;
use totara_webhook\model\totara_webhook_dlq_item;
use totara_webhook\totara_webhook_payload;

class totara_webhook_payload_queue extends model {

    /**
     * @var totara_webhook_payload_queue_entity
     */
    protected $entity;

    /**
     * @return string
     */
    protected static function get_entity_class(): string {
        return totara_webhook_payload_queue_entity::class;
    }

    /**
     * @return totara_webhook
     * @throws \coding_exception
     */
    public function totara_webhook(): totara_webhook {
        return totara_webhook::load_by_entity($this->entity->webhook);
    }

    /**
     * @return totara_webhook_payload
     */
    public function convert_to_totara_webhook_payload(): totara_webhook_payload {
        $entity = $this->entity;
        $body = json_decode($entity->body, true);

        return new totara_webhook_payload(
            $entity->attempt,
            $body,
            $entity->event,
            $entity->webhook_id,
            $entity->time_created
        );
    }

    /**
     * @param totara_webhook_payload $payload
     * @return self
     * @throws \coding_exception
     */
    public static function create_from_totara_webhook_payload(totara_webhook_payload $payload): self {
        $entity = new totara_webhook_payload_queue_entity();
        $body = $payload->get_body();
        $entity->body = json_encode($body);
        $entity->attempt = 0;
        $entity->event = $payload->get_event();
        $entity->webhook_id = $payload->get_webhook_id();
        $entity->time_created = $payload->get_time_created();
        $entity->save();

        return static::load_by_entity($entity);
    }

    /**
     * Used by the dead letter queue to resurrect a job for resending.
     *
     * @param totara_webhook_dlq_item $dlq_item
     * @return self
     * @throws \coding_exception
     */
    public static function create_from_dlq_item(totara_webhook_dlq_item $dlq_item): self {
        $entity = new totara_webhook_payload_queue_entity();
        $entity->body = $dlq_item->get_body();
        $entity->attempt = $dlq_item->get_attempt();
        $entity->event = $dlq_item->get_event();
        $entity->webhook_id = $dlq_item->get_webhook_id();
        $entity->time_created = $dlq_item->get_time_created();
        $entity->save();

        return static::load_by_entity($entity);
    }

    public function increment_attempt(): void {
        $this->entity->attempt++;
        $this->entity->save();
    }

    public function get_attempt(): int {
        return $this->entity->attempt;
    }

    public function mark_sent(): void {
        $this->entity->time_sent = time();
        $this->entity->save();
    }

    /**
     * @return void
     */
    public function delete(): void {
        $this->entity->delete();
    }

    public function requeue(): void {
        $this->entity->next_send = match($this->get_attempt()) {
            1 => time() + (60 * 1),
            2 => time() + (60 * 2),
            3 => time() + (60 * 5),
            4 => time() + (60 * 15),
            5 => time() + (60 * 30),
            6 => time() + (60 * 60),
            7 => time() + (60 * 60 * 3),
            8 => time() + (60 * 60 * 6),
            9 => time() + (60 * 60 * 12),
            default => time() + (60 * 60 * 24),
        };
        $this->entity->time_sent = null;
        $this->entity->save();
    }
}
