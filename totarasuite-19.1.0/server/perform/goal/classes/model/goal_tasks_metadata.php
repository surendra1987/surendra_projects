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

namespace perform_goal\model;

use perform_goal\model\goal as goal_model;

/**
 * Class for holding metadata about a goal's tasks.
 */
class goal_tasks_metadata {
    /** @var int  */
    private int $goal_id;

    /** @var int Total number of tasks */
    private int $total_count;

    /** @var int Number of completed tasks */
    private int $completed_count;

    public function __construct(goal_model $goal) {
        $tasks = $goal->get_tasks();

        $this->goal_id = $goal->id;

        $this->total_count = $tasks->count();

        $this->completed_count = $tasks
            ->filter(fn (goal_task $task) => $task->completed === true)
            ->count();
    }

    /**
     * Get the total number of tasks.
     *
     * @return int
     */
    public function get_total_count(): int {
        return $this->total_count;
    }

    /**
     * Get the number of completed tasks.
     *
     * @return int
     */
    public function get_completed_count(): int {
        return $this->completed_count;
    }

    /**
     * @return int
     */
    public function get_goal_id(): int {
        return $this->goal_id;
    }
}