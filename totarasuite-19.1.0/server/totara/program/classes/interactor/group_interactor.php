<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author  Murali Nair <murali.nair@totara.com>
 * @package totara_program
 */

namespace totara_program\interactor;

use context;
use context_program;
use context_user;
use core\entity\user;
use stdClass;
use totara_program\assignment\group;
use totara_tenant\util;

/**
 * Deals with user permissions to manipulate a program assignment group.
 */
final class group_interactor {
    /**
     * Virtual constructor.
     *
     * @param group $group the group to be checked.
     * @param ?context $context context to check against. If unspecified, uses
     *        the group's parent program for context.
     * @param ?user $actor user accessing the group. If unspecified, defaults to
     *        the currently logged on user if any.
     *
     * @return self the object.
     */
    public static function from(
        group $group,
        ?context $context = null,
        ?user $actor = null
    ): self {
        $ctx = $context
            ? $context
            : context_program::instance($group->get_programid());

        // Note user::logged_in() returns null if not logged on.
        $user = $actor ?? user::logged_in();

        return new self($group, $ctx, $user);
    }


    /**
     * Default constructor.
     *
     * @param group $group {@see virtual constructor create()}.
     * @param ?context $context {@see virtual constructor create()}.
     * @param ?user $user {@see virtual constructor create()}.
     */
    private function __construct(
        public readonly group $group,
        public readonly context $context,
        public readonly ?user $user
    ) {
        // EMPTY BLOCK.
    }

    /**
     * Indicates if the user can enrol into this group.
     *
     * @return stdClass the result with these fields:
     *         - bool success: true if the user can enrol
     *         - ?string code: error code if success = false
     *         - string message: error message success = falseone
     */
    public function can_self_enrol(): stdClass {
        $group = $this->group;
        $uid = $this->user?->id;

        return match (true) {
            !$uid => $this->error('loggedinnot', 'moodle'),

            // The program::is_viewable() call below does check for a program's
            // expiry. However under certain conditions, the user can still view
            // expired programs eg if he has the viewhiddenprograms capability.
            // Hence this check here to disallow anyone from enrolling into an
            // expired program.
            $this->group->get_program()->has_expired() => $this->error(
                'group:error:enrol:prog_expired'
            ),

            !$this->is_viewable() => $this->error(
                'group:error:enrol:prog_not_viewable'
            ),

            !is_null($uid) && $group->get_users([$uid])->count() != 0 => $this->error(
                'group:error:enrol:already_enrolled'
            ),

            !$group->can_self_enrol() => $this->error(
                'group:error:enrol:group_disallows_enrol'
            ),

            default => (object) [
                'success' => true, 'code' => null, 'message' => null
            ]
        };
    }

    /**
     * Indicates if the user can unenrol out of this group.
     *
     * @return stdClass the result with these fields:
     *         - bool success: true if the user can unenrol
     *         - ?string code: error code if success = false
     *         - ?string message: error message if success = false
     */
    public function can_self_unenrol(): stdClass {
        $group = $this->group;
        $uid = $this->user?->id;

        return match (true) {
            !$uid => $this->error('loggedinnot', 'moodle'),

            // The program::is_viewable() call below does check for a program's
            // expiry. However under certain conditions, the user can still view
            // expired programs eg if he has the viewhiddenprograms capability.
            // Hence this check here to disallow anyone from withdrawing from an
            // expired program.
            $this->group->get_program()->has_expired() => $this->error(
                'group:error:enrol:prog_expired'
            ),

            !$this->is_viewable() => $this->error(
                'group:error:enrol:prog_not_viewable'
            ),

            !is_null($uid) && $group->get_users([$uid])->count() == 0 => $this->error(
                'group:error:enrol:not_enrolled'
            ),

            !$group->can_self_unenrol() => $this->error(
                'group:error:enrol:group_disallows_unenrol'
            ),

            default => (object) [
                'success' => true,
                'code' => null,
                'message' => null
            ]
        };
    }

    /**
     * Formulates an error message.
     *
     * @param string $id localization string identifier.
     * @param string $component localization component.
     *
     * @return stdClass the error object.
     */
    private function error(
        string $id,
        string $component = 'totara_program'
    ): stdClass {
        return (object) [
            'success' => false,
            'code' => $id,
            'message' => get_string($id, $component)
        ];
    }

    /**
     * Checks if program visibility and tenancy rules allow the user to be added
     * to the group.
     *
     * NB: program multitenancy rules can be found here:
     * https://totara.help/docs/multitenancy-in-totara-learn#programs-and-certifications
     *
     * @return bool true if there are no problems.
     */
    private function is_viewable(): bool {
        $program_tenantid = (int)$this->context->tenantid;

        return match (true) {
            !$this->group->get_program()->is_viewable() => false,

            // The program::is_viewable() call earlier is supposed to enforce
            // multitenancy rules. However it does not correctly enforce these
            // rules:
            // - tenanted program/global user/isolation off
            // - tenanted program/global user/isolation on
            //
            // The rules are tenant programs are only available for tenant users
            // regardless of multitenancy isolation level; global users cannot
            // access them. Hence the additional check needed here to overcome
            // the bug in program::is_viewable().
            $program_tenantid > 0 => util::do_contexts_share_same_tenant(
                context_user::instance($this->user->id),
                $this->context
            ),

            default => true
        };
    }
}
