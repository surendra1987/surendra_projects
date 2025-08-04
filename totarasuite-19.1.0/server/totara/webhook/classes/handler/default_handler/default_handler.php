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

namespace totara_webhook\handler\default_handler;

use coding_exception;
use core\orm\collection;
use core\orm\query\builder;
use dml_transaction_exception;
use Throwable;
use totara_core\advanced_feature;
use totara_webhook\config;
use totara_webhook\entity\totara_webhook_dlq_item as dlq_entity;
use totara_webhook\handler\totara_webhook_handler;
use totara_webhook\model\totara_webhook;
use totara_webhook\model\totara_webhook_dlq_item;
use totara_webhook\totara_webhook_payload;
use totara_webhook\totara_webhook_publisher;
use totara_webhook\handler\default_handler\entity\totara_webhook_payload_queue as webhook_payload_queue_entity;
use totara_webhook\handler\default_handler\model\totara_webhook_payload_queue as webhook_payload_queue_model;

class default_handler implements totara_webhook_handler {
    /**
     * This function handles the processing of a webhook
     * and whether to queue or send directly
     *
     * @param totara_webhook $totara_webhook
     * @param totara_webhook_payload $webhook_payload
     * @return void
     * @throws coding_exception
     */
    public function dispatch(totara_webhook $totara_webhook, totara_webhook_payload $webhook_payload): void {
        // webhooks should not proceed if the feature is disabled
        if (advanced_feature::is_disabled('totara_webhook')) {
            return;
        }
        // Create a queue payload from the webhook payload
        $queue_payload = webhook_payload_queue_model::create_from_totara_webhook_payload($webhook_payload);

        // If the webhook is to be sent immediately, send it g
        if ($totara_webhook->get_immediate()) {
            $this->send_payload($queue_payload);
        }
    }

    /**
     * This function processes each item in the payload queue
     *
     * @return void
     * @throws coding_exception
     */
    public function process_queue(): void {
        // webhooks should not proceed if the feature is disabled
        if (advanced_feature::is_disabled('totara_webhook')) {
            return;
        }
        // Find all the payloads queued to be sent
        $queue_payloads = $this->get_payloads_to_send();

        /** @var webhook_payload_queue_entity $queue_payload */
        foreach ($queue_payloads as $queue_payload) {
            $this->send_payload($queue_payload);
        }
    }

    /**
     * @return collection
     * @throws coding_exception
     */
    public function get_payloads_to_send(): collection {
        return webhook_payload_queue_entity::repository()
            ->join('totara_webhook_webhook', 'webhook_id', '=', 'id')
            ->where('time_sent', '=', null)
            ->where('totara_webhook_webhook.status', '=', 1)
            ->where(function (builder $builder) {
                $builder->where('next_send', '=', null)
                    ->or_where('next_send', '<=', time());
            })
            ->get()
            ->map_to(webhook_payload_queue_model::class);
    }

    /**
     * @return void
     */
    public function purge_dead_letters(): void {
        // webhooks should not proceed if the feature is disabled
        if (advanced_feature::is_disabled('totara_webhook')) {
            return;
        }
        $offset = config::get_dead_letter_purge_threshold();
        $threshold = time() - $offset;

        dlq_entity::repository()
            ->where('created_at', '<', $threshold)
            ->delete();
    }

    /**
     * This function purges the queue of all items over a certain threshold
     *
     * @return void
     * @throws coding_exception
     */
    public function purge_queue(): void {
        // webhooks should not proceed if the feature is disabled
        if (advanced_feature::is_disabled('totara_webhook')) {
            return;
        }
        $offset = config::get_queue_purge_threshold();
        $threshold = time() - $offset;

        webhook_payload_queue_entity::repository()
            ->where('time_sent', '<', $threshold)
            ->delete();
    }

    /**
     * This function handles the sending of the actual webhook
     *
     * @param webhook_payload_queue_model $queue_payload
     * @return void
     * @throws coding_exception
     * @throws dml_transaction_exception|Throwable
     */
    public function send_payload(webhook_payload_queue_model $queue_payload): void {
        global $DB;
        // webhooks should not proceed if the feature is disabled
        if (advanced_feature::is_disabled('totara_webhook')) {
            return;
        }
        $queue_payload->increment_attempt();
        $webhook_payload = $queue_payload->convert_to_totara_webhook_payload();

        $response = totara_webhook_publisher::publish($queue_payload->totara_webhook(), $webhook_payload);
        $response_code = $response->get_http_code();

        // Check the response - mark as sent or remove to DLQ if needed
        if ($response_code >= 200 && $response_code < 300) {
            $queue_payload->mark_sent();
            return;
        } elseif ($response_code >= 400 && $response_code < 500) {
            $transaction = $DB->start_delegated_transaction();
            try {
                totara_webhook_dlq_item::create_from_totara_webhook_payload($webhook_payload);
                $queue_payload->delete();
                $transaction->allow_commit();
            } catch (\Exception $e) {
                $queue_payload->requeue();
                $transaction->rollback();
            }
            return;
        }

        // catch any tasks that were resurrected from the DLQ, and if they failed again,
        // we dead letter them again.
        if ($queue_payload->get_attempt() > 10) {
            $transaction = $DB->start_delegated_transaction();
            try {
                totara_webhook_dlq_item::create_from_totara_webhook_payload($webhook_payload);
                $queue_payload->delete();
                $transaction->allow_commit();
            } catch (\Exception $e) {
                $queue_payload->requeue();
                $transaction->rollback();
            }
            return;
        }

        $queue_payload->requeue();
    }
}
