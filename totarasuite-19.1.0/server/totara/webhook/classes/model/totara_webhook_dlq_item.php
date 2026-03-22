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

namespace totara_webhook\model;

use coding_exception;
use context;
use context_course;
use core\orm\entity\model;
use totara_webhook\entity\totara_webhook_dlq_item as totara_webhook_dlq_item_entity;
use totara_webhook\event\totara_webhook_dlq_item_created;
use totara_webhook\event\totara_webhook_dlq_item_updated;
use totara_webhook\event\totara_webhook_dlq_item_deleted;
use totara_webhook\handler\default_handler\model\totara_webhook_payload_queue;
use totara_webhook\totara_webhook_payload;

/**
  * Totara webhook dead letter queue item model class
  *
  * Represents and item on the webhook dead letter queue which failed to send
  *
  * Properties:
  * @property-read int $id Internal database identifier
  * @property-read int $webhook_id The ID of the webhook the payload failed to send to
  * @property-read int $created_at When the model was created as a timestamp
  * @property-read int $updated_at When the model was last updated as a timestamp
  *
  */
class totara_webhook_dlq_item extends model {

    /**
     * @var string[]
     */
    protected $model_accessor_whitelist = [
        'webhook_id'
    ];

    /**
     * @var totara_webhook_dlq_item_entity
     */
    protected $entity;

    /**
     * @return string
     */
    protected static function get_entity_class(): string {
        return totara_webhook_dlq_item_entity::class;
    }

    /**
     * @param totara_webhook_payload $payload
     * @return totara_webhook_dlq_item
     * @throws coding_exception
     */
    public static function create_from_totara_webhook_payload(
        totara_webhook_payload $payload
    ): totara_webhook_dlq_item {
        $entity = new totara_webhook_dlq_item_entity();
        $entity->body = json_encode($payload->get_body());
        $entity->webhook_id = $payload->get_webhook_id();
        $entity->attempt = $payload->get_attempt();
        $entity->event = $payload->get_event();
        $entity->time_created = $payload->get_time_created();

        $entity->save();

        $model = static::load_by_entity($entity);

        $created_event = totara_webhook_dlq_item_created::create_from_totara_webhook_dlq_item($model);
        $created_event->trigger();

        return $model;
    }

    /**
     * @param int|null $webhook_id
     * @param int|null $attempt
     * @param string|null $event
     * @param string|null $body
     * @param int|null $time_created
     * @return $this
     * @throws coding_exception
     */
    public function update(
        ?int $webhook_id = null,
        ?int $attempt = null,
        ?string $event = null,
        ?string $body = null,
        ?int $time_created = null
    ): self {

        if (!is_null($webhook_id)) {
            $this->entity->webhook_id = $webhook_id;
        }
        if (!is_null($attempt)) {
            $this->entity->attempt = $attempt;
        }
        if (!is_null($event)) {
            $this->entity->event = $event;
        }
        if (!is_null($body)) {
            $this->entity->body = $body;
        }
        if (!is_null($time_created)) {
            $this->entity->time_created = $time_created;
        }

        $this->entity->save();
        $this->entity->refresh();

        $model = static::load_by_entity($this->entity);
        $updated_event = totara_webhook_dlq_item_updated::create_from_totara_webhook_dlq_item($model);
        $updated_event->trigger();

        return $this;
    }

    /**
     * Will trigger a totara_webhook_dlq_item_deleted event and delete the item from the DB
     *
     * @return void
     * @throws coding_exception
     */
    public function delete(): void {
        $model = static::load_by_entity($this->entity);
        // we don't trigger further events on dlq items being deleted - could end in a recursive loop.
        if ($model->get_event() === totara_webhook_dlq_item_deleted::class) {
            $this->internal_delete();
            return;
        }
        $deleted_event = totara_webhook_dlq_item_deleted::create_from_totara_webhook_dlq_item($model);
        $deleted_event->trigger();

        $this->internal_delete();
    }

    /**
     * This will delete the dlq item from the DB - this is only to be called directly by
     * the public delete method or the requeue function. This is to ensure that the event
     * deleted is not triggered - this is handled by the public delete method.
     *
     * @return void
     */
    protected function internal_delete(): void {
        $this->entity->delete();
    }

    /**
     * Get the 'webhook_id' field.
     *
     * @return int
     */
    public function get_webhook_id(): int {
        return $this->entity->webhook_id;
    }

    /**
     * Get the attempts to send the payload
     *
     * @return int
     */
    public function get_attempt(): int {
        return $this->entity->attempt;
    }

    /**
     * Get the event class name
     *
     * @return string
     */
    public function get_event(): string {
        return $this->entity->event;
    }

    /**
     * Get the payload body
     *
     * @return string
     */
    public function get_body(): string {
        return $this->entity->body;
    }

    /**
     * Returns the time the payload was created
     *
     * @return int
     */
    public function get_time_created(): int {
        return $this->entity->time_created;
    }

    /**
     * Returns the context associated with this Totara webhook dead letter queue item
     *
     * @return context
     */
    public function get_context(): context {
        return context_course::instance(SITEID);
    }

    /**
     * Requeue the payload
     *
     * This will create a new payload queue item with the same payload data
     * and the same webhook ID as this dead lettered item. The attempt will remain the same
     * as it was when the item was dead lettered. The attempt will be increased by the
     * event processor when it does the next send. If the item fails again - it'll follow the
     * same process as any standard payload queue item.
     *
     * @return totara_webhook_payload_queue
     * @throws coding_exception
     */
    public function requeue(): totara_webhook_payload_queue {
        $payload_queue = totara_webhook_payload_queue::create_from_dlq_item($this);
        $this->delete();

        return $payload_queue;
    }
}