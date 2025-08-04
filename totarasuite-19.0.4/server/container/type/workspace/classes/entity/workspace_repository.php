<?php
/**
 * This file is part of Totara Engage
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package container_workspace
 */

namespace container_workspace\entity;

use core\entity\context;
use core\entity\role;
use core\entity\user;
use core\orm\entity\repository;

/**
 * Workspace entity repository.
 */
class workspace_repository extends repository {
    /**
     * Indicates if the specified user has the specified role in the workspace.
     *
     * @param int $workspace_course_id workspace _course id_.
     * @param user $user user to check.
     * @param role $role role to look up.
     *
     * @return bool true if the user has the role.
     */
    public static function user_has_role(
        int $workspace_course_id,
        role $role,
        user $user
    ): bool {
        $usr_t = 'u';

        return static::users_by_role(
            workspace_course_id: $workspace_course_id,
            role: $role,
            usr_t: $usr_t
        )->where("$usr_t.id", '=', $user->id)->exists();
    }

    /**
     * Returns the base query to get workspace users by ids and with a given role.
     *
     * @param int $workspace_course_id workspace _course id_.
     * @param role $role role to look up.
     * @param array $user_ids to get
     *
     * @return self
     */
    public static function users_by_ids(
        int $workspace_course_id,
        role $role,
        array $user_ids
    ): repository {
        $usr_t = 'u';

        return static::users_by_role(
            workspace_course_id: $workspace_course_id,
            role: $role,
            usr_t: $usr_t
        )->where_in("$usr_t.id", $user_ids);
    }

    /**
     * Returns the base query to get workspace users by with a given role.
     *
     * @param int $workspace_course_id workspace _course id_.
     * @param role $role role to look up.
     * @param string $ctx_t explicit context table alias to use.
     * @param string $ra_t explicit role assignment table alias to use.
     * @param string $usr_t explicit user table alias to use. NB: do not use
     *        'user'; some databases have problem with this as a alias.
     * @param string $ws_t explicit workspace table alias to use.
     *
     * @return self the primed _user_ repository.
     */
    public static function users_by_role(
        int $workspace_course_id,
        role $role,
        string $ctx_t = 'ctx',
        string $ra_t = 'ra',
        string $usr_t = 'u',
        string $ws_t = 'w'
    ): repository {
        return user::repository()
            ->as($usr_t)
            ->join(['role_assignments', $ra_t], "$ra_t.userid", "$usr_t.id")
            ->join([context::TABLE, $ctx_t], "$ctx_t.id", "$ra_t.contextid")
            ->join([workspace::TABLE, $ws_t], "$ws_t.course_id", "$ctx_t.instanceid")
            ->where("$ra_t.roleid", '=', $role->id)
            ->where("$ws_t.course_id", '=', $workspace_course_id);
    }
}