<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @package hierarchy_position
 */

namespace hierarchy_position\webapi\resolver\query;

defined('MOODLE_INTERNAL') || die();

use core\orm\collection;
use core\orm\query\order;
use core\pagination\cursor;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\query_resolver;
use hierarchy_position\data_providers\positions as positions_provider;
use hierarchy_position\exception\position_exception;

class positions extends query_resolver {

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        global $USER;

        $query = $args['query'] ?? [];
        $sort = $query['sort'] ?? [];

        $order_by = $query['order_by'] ?? 'fullname';
        $order_dir = $query['order_dir'] ?? 'ASC';
        if (!empty($sort)) {
            if (count($sort) > 1) {
                throw new position_exception("Sorting by more than one column is not currently supported.");
            }

            if (empty($sort[0]['column'])) {
                throw new position_exception("Required parameter 'column' not being passed.");
            }

            $sort = reset($sort);
            $order_by = $sort['column'];
            $order_dir = $sort['direction'] ?? order::DIRECTION_ASC;
        }

        $enc_cursor = $query['cursor'] ?? $query['pagination']['cursor'] ?? null;
        $filters = $query['filters'] ?? [];

        $context = \context_user::instance($USER->id);
        $ec->set_relevant_context($context);

        $cursor = $enc_cursor ? cursor::decode($enc_cursor) : null;

        $positions = (new positions_provider())
            ->set_page_size($query['pagination']['limit'] ?? positions_provider::DEFAULT_PAGE_SIZE)
            ->set_filters($filters)
            ->set_order($order_by, $order_dir)
            ->fetch($cursor);

        if (isset($positions['items'])) {
            $positions_collection = collection::new($positions['items']);
            $position_ids = $positions_collection->pluck('id');
            // grab the custom fields related to the positions
            $custom_fields = customfield_get_custom_fields_and_data_for_items('position', $position_ids);
            // add the custom fields to the execution context for the position type resolver
            $ec->set_variable('custom_fields', $custom_fields);
        }

        return $positions;
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('positions'),
            new require_user_capability('totara/hierarchy:viewposition'),
        ];
    }
}
