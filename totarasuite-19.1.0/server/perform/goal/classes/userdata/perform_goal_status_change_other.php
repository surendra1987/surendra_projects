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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal\userdata;

use core\orm\entity\repository;
use perform_goal\entity\perform_status_change;
use totara_userdata\userdata\target_user;

defined('MOODLE_INTERNAL') || die();

/**
 * Does GDPR related processing for goal statuses changed by a participant in
 * a perform activity.
 */
class perform_goal_status_change_other extends perform_goal_status_change {

    /**
     * {@inheritdoc}
     */
    public static function get_sortorder() {
        return 320;
    }

    /**
     * {@inheritdoc}
     */
    protected static function purge(target_user $user, \context $context) {
        static::goal_status_query($user->id)
            ->update(['status_changer_user_id' => null]);

        return static::RESULT_STATUS_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected static function goal_status_query(int $user_id): repository {
        return perform_status_change::repository()
            ->where('subject_user_id', '!=', $user_id)
            ->where('status_changer_user_id', $user_id);
    }
}