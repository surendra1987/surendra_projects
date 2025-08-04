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
 * @author Mark Metcalfe <mark.metcalfe@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\webapi\resolver\query;

use cm_info;
use core\collection;
use core\webapi\execution_context;
use core\webapi\query_resolver;
use core_course\course_record_reference;
use mod_facetoface\data_providers\seminars as seminars_data_provider;
use mod_facetoface\exception\sort as sort_exception;
use mod_facetoface\seminar;
use mod_facetoface\webapi\field_mapping\seminar as seminar_field_mapping;
use stdClass;

/**
 * mod_facetoface_seminars_in_course query resolver.
 */
class seminars_in_course extends query_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec) {
        global $USER;

        // Always filter to a single course
        $course = (new course_record_reference())->get_record($args['course']);

        require_login($course, false, null, null, true);

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
            $order_by_column = seminar_field_mapping::get_graphql_to_internal_column_mapping()[$order_by_column] ?? $order_by_column;

            if (!empty($order_by_column)) {
                // Add `id` as a secondary order by column to support cursors with non-unique order by fields
                $order_by = $order_by_column . ",id";
            }
        }

        // Get the list of seminar IDs the user actually has permission to view
        $modinfo = get_fast_modinfo($course->id, $USER->id);
        $viewable_instance_ids = collection::new($modinfo->get_instances_of('facetoface'))
            ->filter(fn (cm_info $cm) => $cm->uservisible)
            ->map(fn (cm_info $cm) => $cm->instance)
            ->all();

        // Filter out any seminar IDs that the user does not have permission to view
        if (empty($filters['ids'])) {
            $filters['ids'] = $viewable_instance_ids;
        } else {
            $filters['ids'] = array_intersect($viewable_instance_ids, $filters['ids']);
        }

        $seminars = seminars_data_provider::create()
            ->set_filters($filters)
            ->set_order($order_by, $order_dir)
            ->fetch()
            ->map(fn (stdClass $seminar) => (new seminar())->map_instance($seminar));

        return [
            'items' => $seminars->all(),
            'total' => $seminars->count(),
        ];
    }
}
