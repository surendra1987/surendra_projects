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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

namespace perform_goal\event;

use core\entity\user;
use core\event\base;
use perform_goal\entity\goal_category as perform_goal_category_entity;

class goal_category_created extends base {

    /**
     * @inheritDoc
     */
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['objecttable'] = perform_goal_category_entity::TABLE;
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Create an event when a goal_category is created
     *
     * @param perform_goal_category_entity $goal_category
     * @return base
     */
    public static function create_from_instance(perform_goal_category_entity $goal_category) {
        $user_id = user::logged_in() ? user::logged_in()->id : get_admin()->id;
        $data = [
            'objectid' => $goal_category->id,
            'userid' => $user_id,
            'contextid' => \context_system::instance()->id,
        ];

        return static::create($data);
    }

    /**
     * @inheritDoc
     */
    public static function get_name() {
        return get_string('event_goal_category_created', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public function get_description() {
        return "The user with id '{$this->userid}' has created a goal_category with id '{$this->objectid}'";
    }
}