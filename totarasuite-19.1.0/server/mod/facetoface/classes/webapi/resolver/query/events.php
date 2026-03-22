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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\webapi\resolver\query;

use coding_exception;
use context_user;
use core\pagination\cursor;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_system_capability;
use core\webapi\query_resolver;
use mod_facetoface\data_providers\events as events_data_provider;
use mod_facetoface\exception\sort as sort_exception;
use mod_facetoface\seminar_event;
use mod_facetoface\webapi\field_mapping\event as event_field_mapping;
use stdClass;
use totara_webapi\client_aware_exception;

/**
 * mod_facetoface_events query resolver.
 */
class events extends query_resolver {

    /**
     * @throws coding_exception
     * @throws client_aware_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $USER;
        $query = $args['query'] ?? [];

        $filters = $query['filters'] ?? [];
        $sort = $query['sort'] ?? null;

        $order_by = null;
        $order_dir = null;
        if (!empty($sort)) {
            if (count($sort) > 1) {
                throw sort_exception::make_client_aware("Sorting by more than one column is not currently supported.");
            }

            $sort = reset($sort);
            $order_dir = $sort['direction'] ?? null;

            $order_by_column = $sort['column'] ?? null;
            $order_by_column = event_field_mapping::get_graphql_to_internal_column_mapping()[$order_by_column] ?? $order_by_column;

            if (!empty($order_by_column)) {
                // Add `id` as a secondary order by column to support cursors with non-unique order by fields
                $order_by = $order_by_column . ",id";
            }
        }

        $pagination = $query['pagination'] ?? null;
        $page_size = $pagination['limit'] ?? null;

        $encoded_cursor = $pagination['cursor'] ?? null;
        $decoded_cursor = !empty($encoded_cursor) ? cursor::decode($encoded_cursor) : null;

        $context = context_user::instance($USER->id);
        $ec->set_relevant_context($context);
        $ec->set_variable('context', $context);

        $events = events_data_provider::create()
            ->set_filters($filters)
            ->set_order($order_by, $order_dir)
            ->set_page_size($page_size)
            ->fetch_paginated($decoded_cursor, function (stdClass $event): seminar_event {
                return (new seminar_event())->from_record_with_dates_and_rooms($event, false);
            });

        // Load event custom fields
        $event_ids = array_map(fn (seminar_event $event): int => $event->get_id(), $events['items']);
        $event_custom_fields = customfield_get_custom_fields_and_data_for_items('facetofacesession', $event_ids);
        $ec->set_variable('custom_fields_facetofacesession', $event_custom_fields);

        // Load room custom fields
        $room_ids = array_merge(...array_map(fn (seminar_event $event): array => array_keys($event->get_rooms()), $events['items']));
        $room_custom_fields = customfield_get_custom_fields_and_data_for_items('facetofaceroom', $room_ids);
        $ec->set_variable('custom_fields_facetofaceroom', $room_custom_fields);

        return $events;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            require_authenticated_user::class,
            new require_system_capability('mod/facetoface:viewallsessions'),
        ];
    }
}
