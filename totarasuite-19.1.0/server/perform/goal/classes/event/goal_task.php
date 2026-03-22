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

use core\entity\user;
use core\event\base;
use perform_goal\entity\goal_task as goal_task_entity;

abstract class goal_task extends base {

    /**
     * @inheritDoc
     */
    protected function init(): void {
        $this->data['crud'] = $this->crud;
        $this->data['objecttable'] = goal_task_entity::TABLE;
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Create an event when a goal task is created/updated/deleted
     *
     * @param goal_task_entity $goal_task
     * @return base
     */
    public static function create_from_instance(goal_task_entity $goal_task): goal_task {
        $user = user::logged_in() ?? get_admin();

        $data = [
            'objectid' => $goal_task->id,
            'userid' => $user->id, // currently logged in user
            'contextid' => $goal_task->goal->context->id,
            'relateduserid' => $goal_task->goal->user->id ?? null, // goal subject user
            'other' => [
                'goal_id' => $goal_task->goal_id,
                'completed' => !is_null($goal_task->completed_at)
            ]
        ];

        return static::create($data);
    }

    /**
     * @inheritDoc
     */
    public function get_description(): string {
        $for = ($this->relateduserid && $this->relateduserid != $this->userid)
            ? " for the user with id '{$this->relateduserid}'"
            : '';
        return "The user with id '{$this->userid}' has {$this->action} a goal task with id '{$this->objectid}'{$for}";
    }
}
