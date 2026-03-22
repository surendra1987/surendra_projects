<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

namespace perform_goal\event;

use core\entity\user;
use core\event\base;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\perform_status_change as perform_status_change_entity;

/**
 * An event to record info about the status of a performance activity review goal has been changed by a user.
 */
class perform_goal_perform_status_changed extends base {
    /**
     * @inheritDoc
     */
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['objecttable'] = perform_status_change_entity::TABLE;
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Create an event when a personal goal status is updated
     *
     * @param perform_status_change_entity $perform_status_change
     * @return base
     */
    public static function create_from_instance(perform_status_change_entity $perform_status_change): base {
        $goal = goal_entity::repository()->find($perform_status_change->goal_id);
        $user = user::logged_in() ?? get_admin();

        $data = [
            'userid' => $user->id,
            'objectid' => $perform_status_change->id,
            'contextid' => $goal->context_id,
            'other' => [
                'status_changer_user_id' => $perform_status_change->status_changer_user_id,
                'goal_id' => $perform_status_change->goal_id,
                'activity_id' => $perform_status_change->activity_id,
                'status' => $perform_status_change->status,
            ]
        ];

        return static::create($data);
    }

    /**
     * @inheritDoc
     */
    public static function get_name() {
        return get_string('event_perform_goal_perform_status_changed', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public function get_description() {
        $description = "The user with id '{$this->userid}' has changed the status to '{$this->other['status']}' in the performance activity with id '{$this->other['activity_id']}', " .
        "for the goal with id '{$this->other['goal_id']}'.";
        return $description;
    }
}
