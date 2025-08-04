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
use core\orm\collection;
use core\orm\entity\encrypted_model;
use totara_core\http\request;
use totara_webhook\auth\webhook_auth;
use totara_webhook\auth\webhook_hmac_auth;
use totara_webhook\content_type\json_adapter;
use totara_webhook\entity\totara_webhook as totara_webhook_entity;
use totara_webhook\event\totara_webhook_created;
use totara_webhook\event\totara_webhook_updated;
use totara_webhook\event\totara_webhook_deleted;
use totara_webhook\totara_webhook_content_type_adapter;
use totara_webhook\helper\cache;

/**
  * Webhook model class
  *
  * Model for Webhook resource
  *
  * Properties:
  * @property-read int $id Internal database identifier
  * @property-read string $name Name of the webhook
  * @property-read string $endpoint URL the webhook data will be sent to
  * @property-read int $created_at When the model was created as a timestamp
  * @property-read int $updated_at When the model was last updated as a timestamp
  * @property-read bool $status Determines if the webhook is enabled or disabled
  *
  */
class totara_webhook extends encrypted_model {

    protected $encrypted_attribute_list = [
        'auth_config',
    ];

    protected $model_accessor_whitelist = [
        'name',
        'endpoint',
        'status',
        'immediate',
    ];

    /**
     * @var totara_webhook_entity
     */
    protected $entity;

    protected static function get_entity_class(): string {
        return totara_webhook_entity::class;
    }

    public static function create(
        string $name,
        string $endpoint,
        bool   $status = true,
        bool $immediate = false,
        ?array $events = null,
        string $content_type_adapter = json_adapter::class,
        string $auth_class = webhook_hmac_auth::class,
    ): self {

        $entity = new totara_webhook_entity();
        if (empty(trim($name))) {
            throw new \coding_exception('name cannot be empty');
        }
        $entity->name = $name;

        $entity->status = $status;
        $entity->endpoint = $endpoint;
        $entity->content_type_adapter = $content_type_adapter;
        $entity->immediate = $immediate;

        // check auth class before trying to save - we should not save the webhook if the auth class isn't valid
        $interfaces = class_implements($auth_class);
        if (!$interfaces || !in_array(webhook_auth::class, $interfaces)) {
            throw new \coding_exception('auth_class must be an instance of webhook_auth');
        }
        $entity->auth_class = $auth_class;

        $entity->save();

        if ($events) {
            $event_subscriptions = [];
            foreach ($events as $event) {
                $event_subscription = new \totara_webhook\entity\totara_webhook_event_subscription();
                $event_subscription->webhook_id = $entity->id;
                $event_subscription->event = $event;
                $event_subscriptions[] = $event_subscription;
            }
            $entity->event_subscriptions()->save($event_subscriptions);
            cache::rebuild_cache_for_all_webhooks();
        }

        $model = static::load_by_entity($entity);
        $model->set_encrypted_attribute('auth_config', $auth_class::generate_config());

        $created_event = totara_webhook_created::create_from_totara_webhook($model);
        $created_event->trigger();

        return $model;
    }

    /**
     * @param string|null $name

     * @param string|null $endpoint

     * @return $this
     */
    public function update(
        ?string $name,
        ?string $endpoint,
        ?bool   $status,
        ?bool $immediate,
        ?array $events = null,
        ?string $auth_class = null,
    ): self {

        if (!is_null($name)) {
            if (empty(trim($name))) {
                throw new \coding_exception('name cannot be empty');
            }
            $this->entity->name = $name;
        }

        if (!is_null($endpoint)) {
            $this->entity->endpoint = $endpoint;
        }

        if (!is_null($status)) {
            $this->entity->status = $status;
        }

        if (!is_null($immediate)) {
            $this->entity->immediate = $immediate;
        }

        if (!is_null($auth_class) && $this->entity->auth_class !== $auth_class) {
            $interfaces = class_implements($auth_class);
            if (!$interfaces || !in_array(webhook_auth::class, $interfaces)) {
                throw new \coding_exception('auth_class must be an instance of webhook_auth');
            }

            $this->entity->auth_class = $auth_class;
            // also would need to regenerate the auth config
            $this->entity->auth_config = $auth_class::generate_config();
        }

        $this->entity->save();

        if (!is_null($events)) {
            // Delete any previously created event subscriptions - otherwise this would append
            $this->entity->event_subscriptions()->delete();

            $event_subscriptions = [];
            foreach ($events as $event) {
                $event_subscription = new \totara_webhook\entity\totara_webhook_event_subscription();
                $event_subscription->webhook_id = $this->entity->id;
                $event_subscription->event = $event;
                $event_subscriptions[] = $event_subscription;
            }

            $this->entity->event_subscriptions()->save($event_subscriptions);
            cache::rebuild_cache_for_all_webhooks();
        }

        $this->entity->refresh();

        $model = static::load_by_entity($this->entity);
        $updated_event = totara_webhook_updated::create_from_totara_webhook($model);
        $updated_event->trigger();

        return $this;
    }

    /**
     * Apply any auth to the request prior to sending
     *
     * @param request $request
     * @return request
     */
    public function authorise_request(request $request): request {
        $request = $this->auth_class::authorise_request($request, $this->auth_config);
        return $request;
    }

    public function delete(): void {
        $model = static::load_by_entity($this->entity);
        $deleted_event = totara_webhook_deleted::create_from_totara_webhook($model);
        $deleted_event->trigger();

        $this->entity->delete();

        cache::rebuild_cache_for_all_webhooks();
    }

    /**
     * Get 'name' field.
     *
     * @return string
     */
    public function get_name(): string {
        return $this->entity->name;
    }
    /**
     * Get 'endpoint' field.
     *
     * @return string
     */
    public function get_endpoint(): string {
        return $this->entity->endpoint;
    }
    /**
     * Get 'status' field.
     *
     * @return bool
     */
    public function get_status(): bool {
        return $this->entity->status;
    }

    /**
     * Return all the events this webhook is subscribed to
     *
     * @return collection
     */
    public function get_event_subscriptions(): collection {
        return $this
            ->entity
            ->event_subscriptions()
            ->get()
            ->map_to(totara_webhook_event_subscription::class);
    }

    /**
     * Returns the context associated with this Webhook
     *
     * @return context
     */
    public function get_context(): context {
        return context_course::instance(SITEID);
    }

    /**
     * @return totara_webhook_content_type_adapter
     */
    public function get_content_type_adapter(): totara_webhook_content_type_adapter {
        return new $this->entity->content_type_adapter();
    }

    /**
     * Whether or not the webhook should send immediately when triggered
     * @return bool
     */
    public function get_immediate(): bool {
        return $this->entity->immediate;
    }
}
