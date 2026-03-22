<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

namespace core\webapi\resolver\query;

use core\entity\user;
use core\exception\unresolved_record_reference;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use core_user\exception\get_user_exception;

/**
 * Handles the "core_user_user" GraphQL query.
 */
class user_user extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG, $USER;

        $user_reference = $args['reference'];

        if (empty($user_reference)) {
            throw new get_user_exception('Please set one of user reference.');
        }

        try {
            $target_user = user_record_reference::load_for_viewer($user_reference, user::logged_in());
        } catch (unresolved_record_reference $exception) {
            if ($exception->getMessage() === "User reference must resolve one record only") {
                if ($CFG->allowaccountssameemail && isset($user_reference['email'])) {
                    throw new get_user_exception('Multiple users returned with the same email.');
                }
            }
            throw new get_user_exception($exception->getMessage());
        }

        return [
            'user' => $target_user
        ];
    }


    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            require_authenticated_user::class
        ];
    }
}