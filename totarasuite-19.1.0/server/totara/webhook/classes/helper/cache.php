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

namespace totara_webhook\helper;

use core\orm\collection;
use totara_webhook\entity\totara_webhook_event_subscription;
use totara_webhook\model\totara_webhook;

class cache {
    public const KEY_SUBS_INITIALISED = 'KEY_SUBS_INITIALISED';
    /**
     * Will rebuild the event subscription cache. The cache is used
     * by the event processor to find webhooks which are subscribed
     * to the triggered event.
     *
     * This function is called whenever a webhook is saved.
     *
     * There is also a CLI script that can be called by admins to
     * force a rebuild of the cache should it get out of date.
     *
     * @return void
     */
    public static function rebuild_cache_for_all_webhooks(): void {
        $all_event_subscriptions = totara_webhook_event_subscription::repository()->get();
        $events_with_subscribers = [];
        $event_subscription_cache = \cache::make('totara_webhook', 'totara_webhook_event_subscriptions');

        /** @var totara_webhook_event_subscription $event_subscription */
        foreach ($all_event_subscriptions as $event_subscription) {
            if (!isset($events_with_subscribers[$event_subscription->event])) {
                $events_with_subscribers[$event_subscription->event] = [];
            }
            $events_with_subscribers[$event_subscription->event][] = $event_subscription->webhook_id;
        }

        // At another time we could investigate caching the webhook itself
        // out of scope for at this point, would require some consideration
        // around auth being an encrypted field and how we'd handle that with
        // the cache

        // clear out all existing entries for these events and resave
        $event_subscription_cache->purge();
        $event_subscription_cache->set(static::KEY_SUBS_INITIALISED, true);
        foreach ($events_with_subscribers as $event => $webhook_ids) {
            $event_subscription_cache->set($event, $webhook_ids);
        }
    }

    /**
     * Function can be used by the event processor to find all the webhooks which are
     * associated with a particular event class
     *
     * @param string $event_class
     * @return mixed
     */
    public static function get_webhooks_for_event(string $event_class): collection {
        $event_subscription_cache = \cache::make('totara_webhook', 'totara_webhook_event_subscriptions');

        if (!$event_subscription_cache->has(static::KEY_SUBS_INITIALISED)) {
            static::rebuild_cache_for_all_webhooks();
        }
        // handle cases where the class string includes a preceding slash
        if (str_starts_with($event_class, '\\')) {
            $event_class = substr($event_class, 1);
        }
        $webhook_ids = $event_subscription_cache->get($event_class);

        $subscribed_webhooks = new collection([]);
        if ($webhook_ids) {
            $subscribed_webhooks = \totara_webhook\entity\totara_webhook::repository()
                ->where_in('id', $webhook_ids)
                ->where('status', '=', 1)
                ->get()
                ->map_to(totara_webhook::class);
        }
        return $subscribed_webhooks;
    }
}
