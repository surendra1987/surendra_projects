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

namespace totara_webhook;

use core\event\base;
use totara_core\advanced_feature;
use totara_webhook\data_processor\data_sanitiser;
use totara_webhook\exception\insufficient_webhook_handler_exception;
use totara_webhook\handler\totara_webhook_handler;
use totara_webhook\handler\totara_webhook_handler_factory;
use totara_webhook\helper\cache;
use totara_webhook\hook\data_processor;
use totara_webhook\hook\data_sanitiser_hook;

class event_processor {
    protected static ?totara_webhook_handler $totara_webhook_handler = null;

    /**
     * @param base $event
     * @return void
     * @throws \coding_exception
     * @throws insufficient_webhook_handler_exception
     */
    public static function process_event(base $event): void {
        // webhooks should not proceed if the feature is disabled
        if (advanced_feature::is_disabled('totara_webhook')) {
            return;
        }
        // call the cache
        $webhooks = cache::get_webhooks_for_event($event::class);
        $handler = static::get_webhook_handler();
        // handle events which an object id as it's optional - e.g. badge viewed
        $data = $event->get_data();
        if ($event->objectid !== null && $event->objecttable !== null) {
            $snapshot = null;
            try {
                $snapshot = $event->get_record_snapshot($event->objecttable, $event->objectid);
            } catch (\dml_exception $e) {
                // catch issues with unknown tables or missing records
            }
            // handle cases where the event record is not found in the database
            if ($snapshot) {
               $data = $snapshot;
            }
        }
        $data = json_decode(json_encode($data), true);
        $data_sanitiser = new data_sanitiser($event::class, $data);
        foreach ($webhooks as $webhook) {
            // call hook to allow plugins to further redact / expose data
            $hook = new data_sanitiser_hook($webhook, $data_sanitiser);
            $hook->execute();
            $webhook_payload = new totara_webhook_payload(
                0,
                $hook->sanitiser->get_sanitised_data(),
                $event::class,
                $webhook->get_id(),
                time()
            );
            $handler->dispatch($webhook, $webhook_payload);
        }
    }

    /**
     * Sets the webhook handler by creating an instance of the specified class.
     * Useful for intercepting the webhook handler for testing purposes.
     *
     * This function should only be called once during the lifetime of the application.
     * If called multiple times, the last call will be used.
     *
     * If the class name is null, the default handler will be used.
     * It can be really useful for testing purposes.
     *
     * @param string|null $class_name The name of the class to be used as the webhook handler.
     * @return void
     * @throws insufficient_webhook_handler_exception
     */
    public static function set_webhook_handler(?string $class_name = null): void {
        static::$totara_webhook_handler = totara_webhook_handler_factory::create_instance($class_name);
    }

    /**
     * @param string|null $class_name
     * @return totara_webhook_handler
     * @throws insufficient_webhook_handler_exception
     */
    public static function get_webhook_handler(?string $class_name = null): totara_webhook_handler {
        if (static::$totara_webhook_handler === null) {
            static::$totara_webhook_handler = totara_webhook_handler_factory::create_instance($class_name);
        }
        return static::$totara_webhook_handler;
    }
}
