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
use container_workspace\interactor\workspace\interactor;

/**
 * Holds the conditions required for the current user can remove a member from the owners.
 */
final class owner_removal_conditions {
    // Failed result messages.
    public const ERR_OWNERS_LIMITATION = 'ERR_OWNERS_LIMITATION';

    public const ERR_CAN_MANAGE = 'ERR_CAN_MANAGE';
    /**
     * Virtual constructor.
     *
     * @param workspace $workspace
     * @param collection $owners
     * @param user $actor
     * @param callable[] $additional_verifiers more self->evaluation_result
     *        functions to evaluate. These verifiers run after the default ones.
     * @return owner_removal_conditions
     */
    public static function create(
        workspace $workspace,
        collection $owners,
        user $actor,
        array $additional_verifiers = []
    ) {
        $builtin_verifiers = [
            self::verify_can_manage(...),
            self::verify_remaining_owner_count(...),
        ];

        $verifiers = array_merge($builtin_verifiers, $additional_verifiers);
        return new self($workspace, $owners, $actor, collection::new($verifiers));
    }

    /**
     * Check if the logged-in user have capability to remove an owner user/s from workspace
     *
     * @param self $conditions details pertaining to the owners to be removed.
     * @return evaluation_result
     */
    private static function verify_can_manage(self $conditions): evaluation_result {
        $error_message = get_string(
            'owner_removal_condition_failed:can_manage',
            'container_workspace'
        );
        try {
            $interactor = new interactor($conditions->workspace, $conditions->actor->id);
            if ($interactor->can_manage()) {
                return evaluation_result::passed();
            }
        } catch (\Throwable $error) {
            $error_message = $error->getMessage();
        }
        return evaluation_result::failed(
            self::ERR_CAN_MANAGE,
            $error_message
        );
    }

    /**
     * Checks if at least one remaining owner left when a/the owner/s is/are removed
     *
     * @param self $conditions details pertaining to the owners to be removed.
     * @return evaluation_result the result of the check.
     */
    private static function verify_remaining_owner_count(self $conditions): evaluation_result {
        $total_owners = $conditions->workspace->owners();
        if (($total_owners->count() - $conditions->owners->count()) >= 1) {
            return evaluation_result::passed();
        }

        return evaluation_result::failed(
            self::ERR_OWNERS_LIMITATION,
            get_string(
                'owner_removal_condition_failed:remaining_owner_count',
                'container_workspace'
            )
        );
    }

    /**
     * Default constructor.
     *
     * @param workspace $workspace
     * @param collection $owners
     * @param user $actor
     * @param collection<callable> $verifiers complete set of verifier functions
     *        to evaluate in turn. If any of these verifiers fail,
     *        the owner cannot be removed from the workspace
     */
    private function __construct(
        public readonly workspace $workspace,
        public readonly collection $owners,
        public readonly user $actor,
        private readonly collection $verifiers
    ) {
        // EMPTY BLOCK.
    }

    /**
     * Indicates whether the current user can remove an owner from the workspace.
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
