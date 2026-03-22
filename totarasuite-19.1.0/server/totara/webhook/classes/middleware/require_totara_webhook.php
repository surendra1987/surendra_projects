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

namespace totara_webhook\middleware;

use Closure;
use invalid_parameter_exception;
use moodle_exception;
use core\orm\query\exceptions\record_not_found_exception;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use totara_webhook\model\totara_webhook as totara_webhook_model;

/**
 * Interceptor that uses Webhook related data in the incoming graphql payload
 * for authentication.
 */
class require_totara_webhook implements middleware {
    /**
     * @var ?Closure $retriever payload method that retrieves a Webhook model instance
     *      using data in the specified payload.
     */
    private ?Closure $retriever = null;

    /**
     * @var bool $set_relevant_context whether to populate the incoming graphql execution context's
            relevant context field with the totara_webhook context.
     */
    private $set_relevant_context = false;

    /**
     * Creates an object instance that validates based on the totara_webhook id in the
     * incoming payload.
     *
     * @param string $payload_keys the keys in the payload to use to extract the
     *        id from the payload. For example if the keys are "a.b.c", then the
     *        id is retrieved as $payload['a']['b']['c'].
     * @param bool $set_relevant_context if true, sets the graphql execution
     *        context's relevant context field with the Webhook context.
     *
     * @return require_totara_webhook the object instance.
     */
    public static function by_totara_webhook_id(
        string $payload_keys,
        bool $set_relevant_context = false
    ): require_totara_webhook {
        $retriever = function (payload $payload) use ($payload_keys): totara_webhook_model {
            $id = self::get_id($payload_keys, $payload);
            if (!$id) {
                throw new invalid_parameter_exception('invalid Webhook id');
            }

            return totara_webhook_model::load_by_id($id);
        };

        return new require_totara_webhook($retriever, $set_relevant_context);
    }

    private static function get_payload_value(string $payload_keys, payload $payload) {
        $keys = explode('.', $payload_keys);

        $initial = array_shift($keys);
        $result = $payload->get_variable($initial);

        if ($result) {
            foreach ($keys as $key) {
                $result = $result[$key] ?? null;
            }
        }

        return $result;
    }

    /**
     * Returns an id extracted from the incoming payload.
     * Wraps get_payload_value() and casts result to int.
     *
     * @param string $payload_keys
     * @param payload $payload
     *
     * @return int the extracted ID.
     */
    private static function get_id(string $payload_keys, payload $payload): int {
        return (int)self::get_payload_value($payload_keys, $payload);
    }

    /**
     * Default constructor.
     *
     * @param Closure $retriever payload->activity method that retrieves an activity
     *        using data in the specified payload.
     * @param bool $set_relevant_context if true, sets the graphql execution
     *        context's relevant context field with the activity context.
     */
    private function __construct(Closure $retriever, bool $set_relevant_context) {
        $this->retriever = $retriever;
        $this->set_relevant_context = $set_relevant_context;
    }

    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        global $PAGE;

        $retrieve = $this->retriever;
        try {
            $totara_webhook_model = $retrieve($payload);
        } catch (record_not_found_exception $exception) {
            throw new moodle_exception('invalid_totara_webhook', 'totara_webhook');
        }

        \require_login(null, false, null, false, true);

        if ($this->set_relevant_context) {
            $context = $totara_webhook_model->get_context();
            $payload->get_execution_context()->set_relevant_context($context);
        }

        // Store the loaded Webhook in the payload for later use
        $payload->set_variable('totara_webhook', $totara_webhook_model);

        return $next($payload);
    }
}

