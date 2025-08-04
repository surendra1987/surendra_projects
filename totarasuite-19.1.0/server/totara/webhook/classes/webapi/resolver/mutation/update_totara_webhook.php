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

use core\exception\unresolved_record_reference;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use totara_webapi\client_aware_exception;
use totara_webhook\exception\webhook_creation_exception;
use totara_webhook\model\totara_webhook as totara_webhook_model;
use totara_webhook\middleware\require_totara_webhook;
use totara_webhook\middleware\require_totara_webhook_manage_capability;
use totara_webhook\reference\totara_webhook_record_reference;

class update_totara_webhook extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        $input = $args['input'] ?? [];
        $reference = $input['reference'] ?? null;

        $reference_class = new totara_webhook_record_reference();
        $record = $reference_class->get_record($reference);
        $model = totara_webhook_model::load_by_id($record->id);

        if (count($input) <= 1) {
            return [];
        }

        $name = $input['name'] ?? null;
        $endpoint = $input['endpoint'] ?? null;
        $status = $input['status'] ?? null;

        $event_subscriptions = $input['events'] ?? null;
        $immediate = $input['immediate'] ?? null;

        $endpoint = static::sanitize_endpoint($endpoint);
        $auth_class = $input['auth_class'] ?? null;

        $model->update(
            $name,
            $endpoint,
            $status,
            $immediate,
            $event_subscriptions,
            $auth_class
        );
        return [
            'item' => $model,
        ];
    }

    /**
     * Sanitize the provided endpoint by removing the URL schema.
     * If no endpoint was provided, do nothing. It will not be overwritten.
     *
     * @param string|null $endpoint
     * @returns string - the sanitized endpoint string without the URL schema
     */
    private static function sanitize_endpoint(?string $endpoint): ?string {
        if (!$endpoint) {
            return null;
        }
        $endpoint = preg_replace('(http(s)*://)i', "", $endpoint);
        $endpoint = trim($endpoint);

        if (!$endpoint) {
            return null;
        }

        return $endpoint;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            require_totara_webhook::by_totara_webhook_id('input.reference.id', true),
            require_totara_webhook_manage_capability::class,
        ];
    }
}