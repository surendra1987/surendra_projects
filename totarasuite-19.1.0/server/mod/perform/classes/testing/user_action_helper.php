<?php
/*
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package mod_perform
 */

namespace mod_perform\testing;

use core\entity\user;

/**
 * Helper class to be used in dataProvider
 */
class user_action_helper {
    /**
     * @param user $user
     * @return void
     */
    public static function delete_user(user $user): void {
        delete_user($user->to_record());
    }

    /**
     * @param user $user
     * @return void
     */
    public static function suspend_user_with_close_instances(user $user): void {
        set_config('perform_hide_suspended_users', 0);
        set_config('perform_close_suspended_user_instances', 1);
        user_suspend_user($user->id);
    }

    /**
     * @param user $user
     * @return void
     */
    public static function suspend_user_with_hide_users(user $user): void {
        set_config('perform_hide_suspended_users', 1);
        set_config('perform_close_suspended_user_instances', 0);
        user_suspend_user($user->id);
    }

    /**
     * @param user $user
     * @return void
     */
    public static function suspend_user_no_settings(user $user): void {
        set_config('perform_hide_suspended_users', 0);
        set_config('perform_close_suspended_user_instances', 0);
        user_suspend_user($user->id);
    }
}
