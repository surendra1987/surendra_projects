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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use core\webapi\middleware\require_advanced_feature;
use perform_goal\model\goal_task;
use perform_goal\model\goal_task_resource;
use perform_goal\model\goal_task_type\type as goal_type;
use perform_goal\interactor\goal_interactor;
use perform_goal\webapi\middleware\require_perform_goal;
use perform_goal\trait\webapi_task_operation_error;

class create_goal_task extends mutation_resolver {

    use webapi_task_operation_error;

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        $params = $args['input'] ?? [];

        $interactor = goal_interactor::from_goal($args['goal']);
        if (!$interactor->can_manage()) {
            return static::format_result('goal_task_create_error_can_manage');
        }

        $goal_id = $args['goal']->id;
        $description = $params['description'] ?? null;
        $resource_id = $params['resource_id'] ?? null;
        $resource_type = $params['resource_type'] ?? null;

        if (!$description && !$resource_id) {
            return static::format_result('goal_task_error_not_empty');
        }

        if (
            ($resource_type && !$resource_id)
            || (!$resource_type && $resource_id)
        ) {
            return static::format_result('goal_task_error_no_resource');
        }

        $type = $resource_type
            ? goal_type::by_code((int)$resource_type, (int)$resource_id)
            : null;

        if ($type && !$type->resource_exists()) {
            return static::format_result('goal_task_error_no_resource_exists');
        }

        $goal_task = goal_task::create($goal_id, $description);
        if ($type) {
            goal_task_resource::create($goal_task, $type);
        }

        return [
            'goal_task' => $goal_task,
            'success' => true,
            'errors' => []
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'),
            require_perform_goal::create('input', 'goal_id')
        ];
    }
}
