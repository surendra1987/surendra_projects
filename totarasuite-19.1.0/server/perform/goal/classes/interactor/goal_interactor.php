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
 * @author Scott Davies <scott.davies@totara.com>
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\interactor;

use coding_exception;
use context;
use context_user;
use core\entity\user;
use perform_goal\model\goal;
use perform_goal\settings_helper;
use totara_tenant\local\util as tenant_util;

/**
 * Deals with user permissions to access a perform_goal.
 */
class goal_interactor {

    /** @var context The context in which to check capabilities. */
    private $context;

    /** @var goal|null The goal we wish to check, if any */
    private $goal;

    /**
     * @var user|null The user to check capabilities for. By default, the currently logged-in user.
     */
    private ?user $acting_user;

    /**
     * Instantiate the interactor with just a context to check whether the acting user can view
     * or create new goals in that context.
     *
     * Instantiate with a goal to check whether the user can do things with that particular goal.
     *
     * @param context $context
     * @param goal|null $goal $goal
     * @param int|null $actor_id
     */
    public function __construct(context $context, goal $goal = null, ?int $actor_id = null) {
        $this->context = $context;
        if ($goal) {
            $this->goal = $goal;
        }

        // Note, if acting_user is null, it would mean an external participant...
        $this->acting_user = $actor_id ? new user($actor_id) : user::logged_in();
    }

    /**
     * Use a goal instance to instantiate the interactor, to check capabilities on that particular goal.
     *
     * @param goal $goal
     * @param int|null $actor_id
     * @return goal_interactor
     */
    public static function from_goal(goal $goal, ?int $actor_id = null): goal_interactor {
        return new goal_interactor($goal->context, $goal, $actor_id);
    }

    /**
     * Use a user instance to instantiate the interactor, to check whether the acting user can view /
     * create goals for that user.
     * There will be no target goal to check.
     *
     * @param user $user
     * @param int|null $actor_id
     * @return goal_interactor
     */
    public static function for_user(user $user, ?int $actor_id = null): goal_interactor {
        $user_context = context_user::instance($user->id);
        return new goal_interactor($user_context, null, $actor_id);
    }

    /**
     * Can acting user see personal goals in this context?
     *
     * @return bool
     */
    public function can_view_personal_goals(): bool {
        if (settings_helper::is_perform_goals_disabled()) {
            return false;
        }

        // This could mean it's an external participant.
        if (!isset($this->acting_user)) {
            return false;
        }

        try {
            $is_own_context = context_user::instance($this->acting_user->id)->id === $this->context->id;
            return ($is_own_context && has_capability('perform/goal:viewownpersonalgoals', $this->context, $this->acting_user))
                || has_capability('perform/goal:viewpersonalgoals', $this->context, $this->acting_user);
        } catch (coding_exception $exception) {
            debugging(
                'goal_interactor::can_view_personal_goals() capability check is failed for user id: '.$this->acting_user->id.' and context id: ' . $this->context->id .
                'with the error: ' . $exception->getMessage(),
                DEBUG_DEVELOPER
            );
            return false;
        }
    }

    /**
     * Can acting user manage/create personal goals in this context?
     *
     * @return bool
     */
    public function can_create_personal_goal(): bool {
        if (settings_helper::is_perform_goals_disabled()) {
            return false;
        }

        try {
            return ($this->context->contextlevel == CONTEXT_USER
                && $this->context->instanceid == $this->acting_user->id
                && has_capability('perform/goal:manageownpersonalgoals', $this->context, $this->acting_user)
            ) || has_capability('perform/goal:managepersonalgoals', $this->context, $this->acting_user);
        } catch (coding_exception $exception) {
            debugging(
                'goal_interactor::can_create_personal_goal() capability check is failed for user id: '.$this->acting_user->id.' and context id: ' . $this->context->id .
                'with the error: ' . $exception->getMessage(),
                DEBUG_DEVELOPER
            );
            return false;
        }
    }

    /**
     * Can acting user see the target goal?
     *
     * @return bool
     */
    public function can_view(): bool {
        if (settings_helper::is_perform_goals_disabled()) {
            return false;
        }

        // Goal interactor must have a goal to check.
        if (!$this->goal) {
            return false;
        }

        try {
            // Is the target goal a personal goal?
            if (!empty($this->goal->user_id)) {
                return (
                    (
                        $this->goal->user_id == $this->acting_user->id
                        && has_capability('perform/goal:viewownpersonalgoals', $this->context, $this->acting_user)
                    )
                    || has_capability('perform/goal:viewpersonalgoals', $this->context, $this->acting_user)
                );
            }
            return false;
        } catch (coding_exception $exception) {
            debugging(
                'goal_interactor::can_view() capability check is failed for user id: '.$this->acting_user->id.' and context id: ' . $this->context->id .
                'with the error: ' . $exception->getMessage(),
                DEBUG_DEVELOPER
            );
            return false;
        }
    }

    /**
     * Can acting user manage the target goal?
     *
     * @return bool
     */
    public function can_manage(): bool {
        if (settings_helper::is_perform_goals_disabled()) {
            return false;
        }

        // Goal interactor must have a goal to check.
        if (!$this->goal) {
            return false;
        }

        try {
            // Is the target goal a personal goal?
            if (!empty($this->goal->user_id)) {
                return (
                    (
                        $this->goal->user_id == $this->acting_user->id
                        && has_capability('perform/goal:manageownpersonalgoals', $this->context, $this->acting_user)
                    )
                    || has_capability('perform/goal:managepersonalgoals', $this->context, $this->acting_user)
                );
            }
            return false;
        } catch (coding_exception $exception) {
            debugging(
                'goal_interactor::can_manage() capability check is failed for user id: '.$this->acting_user->id.' and context id: ' . $this->context->id .
                'with the error: ' . $exception->getMessage(),
                DEBUG_DEVELOPER
            );
            return false;
        }
    }

    /**
     * Can acting user set progress for the target goal?
     *
     * @return bool
     */
    public function can_set_progress(): bool {
        if (settings_helper::is_perform_goals_disabled()) {
            return false;
        }

        // Goal interactor must have a goal to check.
        if (!$this->goal) {
            return false;
        }

        try {
            // Is the target goal a personal goal?
            if (!empty($this->goal->user_id)) {
                return (
                    (
                        $this->goal->user_id == $this->acting_user->id
                        && has_capability('perform/goal:setownpersonalgoalprogress', $this->context, $this->acting_user)
                    )
                    || has_capability('perform/goal:setpersonalgoalprogress', $this->context, $this->acting_user)
                );
            }
            return false;
        } catch (coding_exception $exception) {
            debugging(
                'goal_interactor::can_set_progress() capability check is failed for user id: '.$this->acting_user->id.' and context id: ' . $this->context->id .
                'with the error: ' . $exception->getMessage(),
                DEBUG_DEVELOPER
            );
            return false;
        }
    }

    /**
     * Check if the acting user is allowed to set a particular owner_id.
     * This is allowed when multitenancy is off.
     * When multitenancy is on then usual restrictions apply.
     *
     * @param int $owner_id
     * @return bool
     */
    public function can_set_owner_id(int $owner_id): bool {
        global $CFG;

        if (!empty($CFG->tenantsenabled)) {
            $owner = new user($owner_id);

            // If owner and the goal's context belong to different tenants, prevent setting this owner.
            if ($owner->tenantid && $this->context->tenantid && $owner->tenantid !== $this->context->tenantid) {
                return false;
            }

            if (!$this->acting_user->tenantid) {
                // System user can set any owner id
                return true;
            }

            // Acting user is a tenant member.

            if (!empty($CFG->tenantsisolated)) {
                // Tenant isolation: can only set owner from the same tenant.

                // The owner user could be only a tenant participant (not a member).
                // Check if they're only a participant in the same tenant. If so, they're allowed to be set as the goal owner.
                if (empty($owner->tenantid)) {
                    $tenant_ids_check = tenant_util::get_user_participation($owner_id);
                    return ($tenant_ids_check && $this->acting_user->tenantid === current($tenant_ids_check));
                }

                // The owner user is a tenant member. Check it's the same tenant though.
                return $this->acting_user->tenantid === $owner->tenantid;
            }

            // No tenant isolation: Can set system user or user from same tenant as owner.
            return ($this->acting_user->tenantid === $owner->tenantid) || !$owner->tenantid;
        }

        // Multitenancy not enabled: No restrictions.
        return true;
    }

    /**
     * Getter for acting_user. Mainly for testability.
     *
     * @return user
     */
    public function get_acting_user(): user {
        return $this->acting_user;
    }
}
