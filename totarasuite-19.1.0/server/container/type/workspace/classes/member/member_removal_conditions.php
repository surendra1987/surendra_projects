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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package container_workspace
 */

namespace container_workspace\member;

use core\entity\user;
use core\collection;
use container_workspace\workspace;

/**
 * Holds the conditions required for removing the owner from a workspace.
 */
final class member_removal_conditions {

    public const ERR_CAN_BE_REMOVED = 'ERR_CAN_BE_REMOVED';

    /**
     * Virtual constructor.
     *
     * @param workspace $workspace
     * @param user $user
     * @param callable[] $additional_verifiers more self->evaluation_result
     *        functions to evaluate. These verifiers run after the default ones.
     * @return owner_removal_conditions
     */
    public static function create(
        workspace $workspace,
        user $user,
        array $additional_verifiers = []
    ) {
        $builtin_verifiers = [
            self::verify_can_be_removed(...),
        ];

        $verifiers = array_merge($builtin_verifiers, $additional_verifiers);
        return new self($workspace, $user, collection::new($verifiers));
    }

    /**
     * Checks if a user can be removed from owner users.
     *
     * @param self $conditions details pertaining to the owners to be removed.
     * @return evaluation_result the result of the check.
     */
    private static function verify_can_be_removed(self $conditions): evaluation_result {
        if ($conditions->workspace->is_owner($conditions->user)) {
            return evaluation_result::passed();
        }

        return evaluation_result::failed(
            self::ERR_CAN_BE_REMOVED,
            get_string(
                'member_removal_condition_failed:can_be_removed',
                'container_workspace',
                $conditions->user->id
            )
        );
    }

    /**
     * Default constructor.
     *
     * @param workspace $workspace
     * @param user $user
     * @param collection<callable> $verifiers complete set of verifier functions
     *        to evaluate in turn. If any of these verifiers fail,
     *        the owner cannot be removed from the workspace
     */
    private function __construct(
        public readonly workspace $workspace,
        public readonly user $user,
        private readonly collection $verifiers
    ) {
        // EMPTY BLOCK.
    }

    /**
     * Indicates whether the owner can be removed from the workspace.
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
