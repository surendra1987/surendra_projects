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

namespace totara_webhook\webapi\resolver\query;

use core\pagination\offset_cursor;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use totara_webhook\data_provider\totara_webhook as totara_webhook_data_provider;
use totara_webhook\model\totara_webhook as totara_webhook_model;
use totara_webhook\model\filter\totara_webhook_filter_factory as totara_webhook_filter_factory;

class totara_webhooks extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $context = \context_course::instance(SITEID);
        $ec->set_relevant_context($context);
        require_capability('totara/webhook:viewtotara_webhooks', $context);

        $input = $args['input'] ?? [];
        $filters = $input['filters'] ?? [];

        // Handle pagination (we support both cursor and offset based) with boilerplate
        if (!empty($input['pagination']['cursor'])) {
            // Cursor-based, use it instead
            $cursor = offset_cursor::decode($input['pagination']['cursor']);
        } else {
            $cursor = offset_cursor::create([
                'page' => $input['pagination']['page'] ?? 1,
                'limit' => $input['pagination']['limit'] ?? totara_webhook_data_provider::DEFAULT_PAGE_SIZE
            ]);
        }

        $sort = $input['sort'] ?? [['column' => 'id', 'direction' => 'asc']];

        $first_order = reset($sort);
        if ($first_order !== false) {
            $order_column = $first_order['column'];
            $order_direction = $first_order['direction'];
        } else {
            $order_column = null;
            $order_direction = null;
        }

        return totara_webhook_data_provider::create(new totara_webhook_filter_factory())
            ->set_filters($filters)
            ->set_order($order_column, $order_direction)
            ->fetch_offset_paginated($cursor, function ($entity): totara_webhook_model {
                return totara_webhook_model::load_by_entity($entity);
            });
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