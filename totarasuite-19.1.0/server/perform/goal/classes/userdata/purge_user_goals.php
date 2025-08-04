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

use context;
use Exception;
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\goal as goal_model;
use totara_userdata\userdata\target_user;

class purge_user_goals extends goal_item {

    /**
     * @inheritDoc
     */
    public static function is_purgeable(int $userstatus) {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function is_exportable(): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected static function purge(target_user $user, context $context): int {
        global $DB;
        // Do not filter by the context in this query, as we are looking for all goal references to this user to purge.
        try {
            $DB->transaction(function () use ($user) {
                goal_entity::repository()
                    ->where('user_id', $user->id)
                    ->get()
                    ->map(function(goal_entity $goal_entity) {
                        // deleting the files on 'like cascade' in the goal model
                        (goal_model::load_by_entity($goal_entity))->delete();
                    });
            });
        } catch (Exception $exception) {
            return self::RESULT_STATUS_ERROR;
        }
        return self::RESULT_STATUS_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public static function is_countable(): bool {
        return false;
    }
}