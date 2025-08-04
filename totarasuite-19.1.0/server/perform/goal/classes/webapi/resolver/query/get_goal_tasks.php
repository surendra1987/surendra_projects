<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\resolver\query;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal;
use perform_goal\model\goal_task;
use perform_goal\webapi\middleware\require_perform_goal;

/**
 * Handles the "perform_goal_get_goal_tasks" GraphQL query.
 */
class get_goal_tasks extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        // The require_perform_goal middleware retrieves the goal and passes it
        // via $args.
        /** @var goal $goal */
        $goal = $args[require_perform_goal::GOAL_KEY];

        $can_view = goal_interactor::from_goal($goal)->can_view();
        if (!$can_view) {
            throw new coding_exception(
                'you do not currently have permissions to do that (view goal tasks in this context)'
            );
        }

        $tasks = $goal->tasks;
        $goal_tasks_metadata = $goal->goal_tasks_metadata;

        return [
            'goal_id' => $goal->id,
            'tasks' => $tasks,
            'total' => $goal_tasks_metadata->get_total_count(),
            'completed' => $goal_tasks_metadata->get_completed_count(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'),
            new require_login(),
            new require_authenticated_user(),
            require_perform_goal::create()
        ];
    }
}
