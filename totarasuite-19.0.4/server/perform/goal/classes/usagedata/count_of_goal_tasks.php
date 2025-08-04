<?php
/**
 * This file is part of Totara Perform
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal\usagedata;

use Exception;
use perform_goal\entity\goal_task;
use perform_goal\entity\goal_task_resource;
use perform_goal\model\goal_task_type\type;
use tool_usagedata\export;

/**
 * Usage data export class counting goals by status and some other general metrics.
 */
class count_of_goal_tasks implements export {

    private const RESULT_KEY_TOTAL_GOAL_TASKS_COUNT = 'total_goal_tasks_count';

    private const RESULT_KEY_COMPLETED_GOAL_TASKS_COUNT = 'completed_goal_tasks_count';

    private const RESULT_KEY_GOALS_WITH_AT_LEAST_ONE_TASK_COUNT = 'goals_with_at_least_one_task_count';

    private const RESULT_KEY_GOALS_WITH_AT_LEAST_ONE_TASK_RESOURCE_COUNT = 'goals_with_at_least_one_task_resource_count';

    private const RESULT_KEY_TOTAL_TASK_RESOURCES_COUNT = 'total_task_resources_count';

    private const RESULT_KEY_PREFIX_TASK_RESOURCE_TYPE_COUNT = 'resource_type_count_';


    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('count_of_goal_tasks_summary', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritDoc
     */
    public function export(): array {

        $query_result_task_count = goal_task::repository()
            ->select_raw('COUNT(id) AS total_goal_tasks_count')
            ->add_select_raw('COUNT(CASE WHEN completed_at IS NULL THEN NULL ELSE 1 END) AS completed_goal_tasks_count')
            ->add_select_raw('COUNT(DISTINCT goal_id) AS goals_with_at_least_one_task_count')
            ->get()
            ->first();

        $query_result_task_resource_count = goal_task_resource::repository()
            ->as('gtr')
            ->join([goal_task::TABLE, 'gt'], 'gtr.task_id', 'gt.id')
            ->select_raw('COUNT(gtr.id) AS total_task_resources_count')
            ->add_select_raw('COUNT(DISTINCT gt.goal_id) AS goals_with_at_least_one_task_resource_count')
            ->get()
            ->first();

        $query_result_resource_type_count = goal_task_resource::repository()
            ->select(['resource_type', 'COUNT(resource_type) AS resource_type_count'])
            ->group_by('resource_type')
            ->get()
            ->key_by('resource_type')
            ->all(true);

        $result_resource_type_count = [];
        foreach ($query_result_resource_type_count as $resource_type_code => $row) {
            try {
                $resource_type_name = type::by_code($resource_type_code)->name();
                // name() returns the full class name by default. We just want the string after the last backslash.
                $parts = explode('\\', $resource_type_name);
                $resource_type_name = end($parts);
            } catch (Exception $e) {
                // This could happen when an invalid type code is in the DB. It will lead to an exception in the application,
                // but we don't want any exception here. So we just ignore it.
                continue;
            }
            $result_key = self::RESULT_KEY_PREFIX_TASK_RESOURCE_TYPE_COUNT . $resource_type_name;
            $result_resource_type_count[$result_key] = $row->resource_type_count;
        }

        return array_merge(
            [
                self::RESULT_KEY_TOTAL_GOAL_TASKS_COUNT => $query_result_task_count->total_goal_tasks_count,
                self::RESULT_KEY_COMPLETED_GOAL_TASKS_COUNT => $query_result_task_count->completed_goal_tasks_count,
                self::RESULT_KEY_GOALS_WITH_AT_LEAST_ONE_TASK_COUNT => $query_result_task_count->goals_with_at_least_one_task_count,
                self::RESULT_KEY_GOALS_WITH_AT_LEAST_ONE_TASK_RESOURCE_COUNT => $query_result_task_resource_count->goals_with_at_least_one_task_resource_count,
                self::RESULT_KEY_TOTAL_TASK_RESOURCES_COUNT => $query_result_task_resource_count->total_task_resources_count,
            ],
            $result_resource_type_count
        );
    }
}
