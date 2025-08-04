<?php
/**
 * This file is part of Totara Core
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package container_workspace
 */

namespace container_workspace\member;

use container_workspace\interactor\workspace\interactor;
use container_workspace\workspace;
use core\collection;
use core\entity\user;

/**
 * Holds the conditions required for adding an owner to a workspace.
 */
final class owner_addition_conditions {
    // Failed result messages.
    public const ERR_ALREADY_AN_OWNER = 'ERR_ALREADY_AN_OWNER';
    public const ERR_NOT_A_MEMBER = 'ERR_NOT_A_MEMBER';
    public const ERR_IS_SUSPENDED_USER = 'ERR_IS_SUSPENDED_USER';
    public const ERR_NO_ACCESS = 'ERR_NO_ACCESS';

    /**
     * Virtual constructor.
     *
     * @param workspace $workspace
     * @param int $user_id
     * @param callable[] $additional_verifiers more self->evaluation_result
     *        functions to evaluate. These verifiers run after the default ones.
     * @return owner_addition_conditions
     */
    public static function create(
        workspace $workspace,
        int $user_id,
        array $additional_verifiers = []
    ) {
        $builtin_verifiers = [
            self::verify_not_already_an_owner(...),
            self::verify_is_a_member(...),
            self::verify_not_a_suspended_user(...),
            self::verify_access_to_workspace(...),
        ];

        $verifiers = array_merge($builtin_verifiers, $additional_verifiers);
        return new self($workspace, $user_id, collection::new($verifiers));
    }

    /**
     * Checks if the given user is already an owner of the workspace.
     *
     * @param self $conditions details pertaining to the owners to be removed.
     * @return evaluation_result the result of the check.
     */
    private static function verify_not_already_an_owner(self $conditions): evaluation_result {
        if ($conditions->workspace->is_owner(new user($conditions->user_id))) {
            return evaluation_result::failed(
                self::ERR_ALREADY_AN_OWNER,
                get_string(
                    'owner_addition_condition_failed:user_is_already_an_owner',
                    'container_workspace'
                )
            );
        }

        return evaluation_result::passed();
    }

    /**
     * Checks if the given user is a member of the workspace.
     *
     * @param self $conditions details pertaining to the owners to be removed.
     * @return evaluation_result the result of the check.
     */
    private static function verify_is_a_member(self $conditions): evaluation_result {
        if ($conditions->workspace->is_member(new user($conditions->user_id))) {
            return evaluation_result::passed();
        }

        return evaluation_result::failed(
            self::ERR_NOT_A_MEMBER,
            get_string(
                'owner_addition_condition_failed:user_is_not_a_member',
                'container_workspace'
            )
        );
    }

    /**
     * Verifies that the user is not suspended.
     *
     * @param self $conditions details pertaining to the owners to be removed.
     * @return evaluation_result the result of the check.
     */
    private static function verify_not_a_suspended_user(self $conditions): evaluation_result {
        $user = new user($conditions->user_id);
        if ($user->suspended) {
            return evaluation_result::failed(
                self::ERR_IS_SUSPENDED_USER,
                get_string(
                    'owner_addition_condition_failed:user_is_suspended',
                    'container_workspace'
                )
            );
        }

        return evaluation_result::passed();
    }

    private static function verify_access_to_workspace(self $conditions): evaluation_result {
        if (get_config('core', 'tenantsenabled')) {
            // Check if the new owner is able to see this workspace or not.
            $interactor = new interactor($conditions->workspace, $conditions->user_id);
            if (!$interactor->can_view_workspace_with_tenant_check()) {
                return evaluation_result::failed(
                    self::ERR_NO_ACCESS,
                    get_string(
                        'owner_addition_condition_failed:user_cannot_access_workspace',
                        'container_workspace'
                    )
                );
            }
        }

        return evaluation_result::passed();
    }

    /**
     * Default constructor.
     *
     * @param workspace $workspace
     * @param int $user_id
     * @param collection<callable> $verifiers complete set of verifier functions
     *        to evaluate in turn. If any of these verifiers fail, the user
     *        cannot be added as an owner.
     */
    private function __construct(
        public readonly workspace $workspace,
        public readonly int $user_id,
        private readonly collection $verifiers
    ) {
        // EMPTY BLOCK.
    }

    /**
     * Indicates whether the user can be added as an owner of the workspace.
     *
     * @return evaluation_result the result of the check.
     */
    public function evaluate(): evaluation_result {
        return $this->verifiers->reduce(
            fn (evaluation_result $acc, callable $fn): evaluation_result =>
            $acc->is_fulfilled ? $fn($this) : $acc,
            evaluation_result::passed()
        );
    }
}
