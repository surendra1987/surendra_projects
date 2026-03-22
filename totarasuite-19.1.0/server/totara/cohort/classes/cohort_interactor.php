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
 * @package totara_cohort
 */

namespace totara_cohort;

use Context;
use context_coursecat;
use context_system;
use context_tenant;
use core\entity\cohort;
use core\entity\tenant;
use core\entity\user;

/**
 * Class cohort_interactor
 */
class cohort_interactor {
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var user
     */
    protected $actor;

    /**
     * Indicates which factory method is called
     *
     * @var bool
     */
    protected $is_for_cohort;

    /**
     * Constructor
     *
     * @param Context $context
     * @param user|null $actor
     */
    private function __construct(context $context, user $actor = null) {
        // When context level is tenant level, we need to get course category context by tenant category id,
        // because audience context id is either system context id or course category context id.
        if ($context->contextlevel === CONTEXT_TENANT) {
            $tenant = new tenant($context->tenantid);

            $this->context = context_coursecat::instance($tenant->categoryid);
        } else {
            $this->context = $context;
        }

        if (is_null($actor)) {
            $actor = user::logged_in();
        }

        $this->actor = $actor;
        $this->is_for_cohort = false;
    }

    /**
     * @param user|null $actor
     * @return self
     */
    public static function for_user(user $actor = null): self {
        if (is_null($actor)) {
            $actor = user::logged_in();
        }

        $context = isset($actor->tenantid) ?
            context_tenant::instance($actor->tenantid) :
            context_system::instance();
        return new static($context, $actor);
    }

    /**
     * @param cohort $cohort
     * @param user|null $actor_id
     * @return self
     */
    public static function for_cohort(cohort $cohort, user $actor = null): self {
        $context = context::instance_by_id($cohort->contextid);
        $cohort_interactor = new static($context, $actor);
        $cohort_interactor->is_for_cohort = true;
        return $cohort_interactor;
    }

    /**
     * @return bool
     */
    public function can_view_cohort(): bool {
        if (!$this->is_for_cohort) {
            return has_capability_in_any_context('moodle/cohort:view', [CONTEXT_SYSTEM, CONTEXT_COURSECAT], $this->actor->id);
        }

        return has_capability('moodle/cohort:view', $this->get_context(), $this->actor->id);
    }

    /**
     * @return bool
     */
    public function can_manage_cohort(): bool {
        if (!$this->is_for_cohort) {
            return has_capability_in_any_context('moodle/cohort:manage', [CONTEXT_SYSTEM, CONTEXT_COURSECAT], $this->actor->id);
        }
        return has_capability('moodle/cohort:manage', $this->get_context(), $this->actor->id);
    }

    /**
     * @return bool
     */
    public function can_assign_cohort(): bool {
        if (!$this->is_for_cohort) {
            return has_capability_in_any_context('moodle/cohort:assign', [CONTEXT_SYSTEM, CONTEXT_COURSECAT], $this->actor->id);
        }
        return has_capability('moodle/cohort:assign', $this->get_context(), $this->actor->id);
    }

    /**
     * @return bool
     */
    public function can_view_or_manage_cohort(): bool {
        return $this->can_view_cohort() || $this->can_manage_cohort();
    }

    /**
     * @return bool
     */
    public function can_assign_or_manage_cohort(): bool {
        return $this->can_assign_cohort() || $this->can_manage_cohort();
    }

    /**
     * @return Context
     */
    public function get_context(): context {
        return $this->context;
    }

    /**
     * @return bool
     */
    public function tenant_enabled(): bool {
        global $CFG;

        return ($CFG->tenantsenabled && !empty($this->actor->tenantid));
    }

    /**
     * @param int $cohort_context_id The context ID from the cohort record
     * @return bool
     */
    public function is_under_same_tenancy_for_target_cohort(int $cohort_context_id): bool {
        foreach ($this->context->get_child_contexts() as $context) {
            if ($context->id === $cohort_context_id) {
                return true;
            }
        }

        return $this->context->id === $cohort_context_id;
    }

    /**
     * Check whether can add a tenant user into a target cohort or not.
     *
     * @param \stdClass $target_cohort
     * @param \stdClass $target_user
     * @return bool
     */
    public function can_add_tenant_user_into_cohort($target_cohort, $target_user): bool {
        if (!empty($target_user->tenantid)) {
            $cohort_context = context::instance_by_id($target_cohort->contextid);

            if ($cohort_context->tenantid !== $target_user->tenantid) {
                return false;
            }
        }

        return true;
    }

}