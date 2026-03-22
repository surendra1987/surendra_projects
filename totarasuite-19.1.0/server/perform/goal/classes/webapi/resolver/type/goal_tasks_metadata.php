<?php
/**
 * This file is part of Totara Perform
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\resolver\type;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use perform_goal\model\goal_tasks_metadata as goal_tasks_metadata_model;

/**
 * Maps the perform_goal\model\goal_task_metadata class into a GraphQL
 * perform_goal_goal_task_metadata type.
 */
class goal_tasks_metadata extends type_resolver {

    /**
     * {@inheritdoc}
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        if (!$source instanceof goal_tasks_metadata_model) {
            throw new coding_exception(
                __METHOD__ . ' requires an input ' . goal_tasks_metadata_model::class
            );
        }

        return match ($field) {
            'total_count' => $source->get_total_count(),
            'completed_count' => $source->get_completed_count(),
            default => throw new coding_exception('Unknown field: ' . $field),
        };
    }
}