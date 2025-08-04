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

use context;
use context_course;
use core\orm\entity\model;
use totara_webhook\entity\totara_webhook_event_subscription as totara_webhook_event_subscription_entity;
use totara_webhook\event\totara_webhook_event_subscription_created;
use totara_webhook\event\totara_webhook_event_subscription_updated;
use totara_webhook\event\totara_webhook_event_subscription_deleted;

/**
  * Webhook Event Subscription model class
  *
  * Mapping of webhooks subscribed to events
  *
  * Properties:
  * @property-read int $id Internal database identifier
  * @property-read ?string $event event class name the webhook is subscribed to
  * @property-read int $webhook_id ID of the webhook subscribed to the event
  * @property-read int $created_at When the model was created as a timestamp
  * @property-read int $updated_at When the model was last updated as a timestamp
  *
  */
class totara_webhook_event_subscription extends model {

    protected $model_accessor_whitelist = [
        'event',
        'webhook_id'
    ];

    /**
     * @var totara_webhook_event_subscription_entity
     */
    protected $entity;

    protected static function get_entity_class(): string {
        return totara_webhook_event_subscription_entity::class;
    }

    public static function create(
        ?string $event,
        int $webhook_id
    ) {

        $entity = new totara_webhook_event_subscription_entity();
        $entity->event = $event;

        $entity->webhook_id = $webhook_id;


        $entity->save();

        $model = static::load_by_entity($entity);

        $created_event = totara_webhook_event_subscription_created::create_from_totara_webhook_event_subscription($model);
        $created_event->trigger();

        return $model;
    }

    /**
     * @param string|null $event

     * @param int|null $webhook_id

     * @return $this
     */
    public function update(
        ?string $event,
        ?int $webhook_id
    ): self {

        if (!is_null($event)) {
            $this->entity->event = $event;
        }

        if (!is_null($webhook_id)) {
            $this->entity->webhook_id = $webhook_id;
        }

        $this->entity->save();
        $this->entity->refresh();

        $model = static::load_by_entity($this->entity);
        $updated_event = totara_webhook_event_subscription_updated::create_from_totara_webhook_event_subscription($model);
        $updated_event->trigger();

        return $this;
    }

    public function delete(): void {
        $model = static::load_by_entity($this->entity);
        $deleted_event = totara_webhook_event_subscription_deleted::create_from_totara_webhook_event_subscription($model);
        $deleted_event->trigger();

        $this->entity->delete();
    }

    /**
     * Get 'event' field.
     *
     * @return ?string
     */
    public function get_event(): ?string {
        return $this->entity->event;
    }
    /**
     * Get 'webhook_id' field.
     *
     * @return string
     */
    public function get_webhook_id(): int {
        return $this->entity->webhook_id;
    }

    /**
     * Get the associated totara_webhook model with this subscription
     *
     * @return totara_webhook
     */
    public function get_webhook(): totara_webhook {
        $webhook_id = $this->entity->webhook_id;
        return totara_webhook::load_by_id($webhook_id);
    }

    /**
     * Returns the context associated with this Webhook Event Subscription
     *
     * @return context
     */
    public function get_context(): context {
        return context_course::instance(SITEID);
    }
}