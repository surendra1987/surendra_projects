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

namespace perform_goal\userdata;

use perform_goal\entity\goal as perform_goal_entity;
use totara_userdata\userdata\item;
use totara_userdata\userdata\target_user;

abstract class goal_item extends item {

    /**
     * @inheritDoc
     */
    public static function get_sortorder() {
        return 300;
    }

    /**
     * @inheritDoc
     */
    public static function is_countable(): bool {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function get_compatible_context_levels(): array {
        return [
            CONTEXT_USER, CONTEXT_SYSTEM, CONTEXT_TENANT
        ];
    }

    /**
     * @inheritDoc
     */
    protected static function count(target_user $user, \context $context) {
        return perform_goal_entity::repository()
            ->where('user_id', $user->id)
            ->count();
    }
}
