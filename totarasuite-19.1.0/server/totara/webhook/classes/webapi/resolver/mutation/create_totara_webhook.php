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

 * @author ben fesili <ben.fesili@totara.com>
 */

namespace totara_webhook\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\mutation_resolver;
use totara_webapi\client_aware_exception;
use totara_webhook\auth\webhook_hmac_auth;
use totara_webhook\content_type\json_adapter;
use totara_webhook\exception\webhook_creation_exception;
use totara_webhook\model\totara_webhook as totara_webhook_model;

class create_totara_webhook extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        $context = \context_course::instance(SITEID);
        $ec->set_relevant_context($context);
        require_capability('totara/webhook:managetotara_webhooks', $context);

        $input = $args['input'];
        $name = $input['name'] ?? null;
        $endpoint = $input['endpoint'] ?? null;
        $status = $input['status'] ?? true;

        $event_subscriptions = $input['events'] ?? null;

        $endpoint = static::sanitize_endpoint($endpoint);

        $immediate  = $input['immediate'] ?? false;

        $auth_class = $input['auth_class'] ?? webhook_hmac_auth::class;

        $model = totara_webhook_model::create(
            $name,
            $endpoint,
            $status,
            $immediate,
            $event_subscriptions,
            json_adapter::class,
            $auth_class
        );

        return [
            'item' => $model,
        ];
    }

    /**
     * Sanitize the provided endpoint by removing the URL schema.
     * If no URL endpoint was provided, throw an exception.
     *
     * @param string|null $endpoint
     * @returns string - the sanitized endpoint string without the URL schema
     * @throws client_aware_exception
     */
    private static function sanitize_endpoint(?string $endpoint): string {
        if (!$endpoint) {
            throw webhook_creation_exception::make('You must provide a webhook endpoint URL');
        }
        $endpoint = preg_replace('(http(s)*://)i', "", $endpoint);
        $endpoint = trim($endpoint);

        if (!$endpoint) {
            throw webhook_creation_exception::make('You must provide a webhook endpoint URL');
        }

        return $endpoint;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user()
        ];
    }
}