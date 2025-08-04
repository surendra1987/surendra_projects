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

use coding_exception;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use core\webapi\middleware\require_advanced_feature;
use perform_goal\interactor\goal_interactor;
use perform_goal\webapi\middleware\require_perform_goal_task;

/**
 * This resolver is for deleting a totara perform_goal_task
 */
class delete_goal_task extends mutation_resolver {

    /**
     * {@inheritDoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        // The require_perform_goal_task middleware retrieves the goal task and
        // passes it via $args.
        $goal_task = $args[require_perform_goal_task::TASK_KEY];
        $goal = $goal_task->get_goal();

        $interactor = goal_interactor::from_goal($goal);
        if (!$interactor->can_manage()) {
            throw new coding_exception(
                'Sorry, but you do not currently have permissions to do that (delete a goal task in this context).'
            );
        }

        return ['success' => $goal_task->delete()];
    }

    /**
     * {@inheritDoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'),
            require_perform_goal_task::create()
        ];
    }
}
