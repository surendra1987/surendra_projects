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

namespace perform_goal\event;

class goal_task_progress_changed extends goal_task {

    /** @var string $crud */
    protected string $crud = 'u';

    /** @var string $action */
    protected string $action = '';

    /**
     * @inheritDoc
     */
    public static function get_name() {
        return get_string('event_goal_task_progress_changed', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public function get_description(): string {
        $for = ($this->relateduserid && $this->relateduserid != $this->userid)
            ? " for the user with id '{$this->relateduserid}'"
            : '';
        // Get past an issue where a task completed value of false turns into an empty string.
        $completed_val = ($this->other['completed'] != false) ? $this->other['completed'] : 0;

        return "The user with id '{$this->userid}' has updated the goal task completion to {$completed_val} in the goal task with id '{$this->objectid}'{$for}";
    }
}
