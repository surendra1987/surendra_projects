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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\event;

use core\event\base;
use core\entity\user;
use perform_goal\entity\goal as perform_goal_entity;

class personal_goal_details_updated extends base {

    /**
     * @inheritDoc
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['objecttable'] = perform_goal_entity::TABLE;
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Create an event when a personal goal details are updated.
     *
     * @param perform_goal_entity $goal
     * @return base
     */
    public static function create_from_instance(perform_goal_entity $goal) {
        $data = [
            'objectid' => $goal->id,
            'userid' => user::logged_in() ? user::logged_in()->id : get_admin()->id,
            'contextid' => $goal->context_id,
            'relateduserid' => $goal->user_id ?? null
        ];

        return static::create($data);
    }

    /**
     * @inheritDoc
     */
    public static function get_name() {
        return get_string('event_goal_details_updated', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public function get_description() {
        $for = ($this->relateduserid && $this->relateduserid != $this->userid)
            ? " for the user with id '{$this->relateduserid}'"
            : '';
        return "The user with id '{$this->userid}' has updated a personal goal details with id '{$this->objectid}'{$for}";
    }
}