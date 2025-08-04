<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

namespace core_mfa;

use coding_exception;
use core_component;
use core_mfa\data_provider\factor as factor_provider;
use core_mfa\data_provider\instance as instance_provider;
use core\event\mfa_factors_revoked;

/**
 * Interface to MFA system.
 */
class framework {

    /**
     * User id to work with.
     *
     * @var int
     */
    private $user_id;

    /**
     * Constructor
     *
     * @param int $user_id
     */
    public function __construct(int $user_id) {
        $this->user_id = $user_id;
    }

    /**
     * Is there anything the user is currently able to do related to managing MFA?
     *
     * I.e. registering or removing factors.
     * @return bool
     */
    public function user_can_manage(): bool {
        return $this->has_registered_factors() || $this->user_can_register();
    }

    /**
     * Is MFA required?
     *
     * @return bool
     */
    public function is_mfa_required(): bool {
        global $USER, $DB;

        return !isguestuser($this->user_id) // the user to login is not a guest user
            && empty($USER->mfa_completed) // mfa has not been marked as completed
            && $DB->get_manager()->table_exists('mfa_instance_config') // Check if the MFA tables have been created
            && instance_provider::get_user_instances($this->user_id)->count() > 0; // The user has mfa instances setup
    }

    /**
     * Can the user register factors? limits MFA to site admins only.
     *
     * @return bool
     */
    public function user_can_register(): bool {
        return factor_provider::get_registerable_factors($this->user_id)->count() > 0 && is_siteadmin($this->user_id);
    }

    /**
     * Does the user have factors registered.
     * @return bool
     */
    public function has_registered_factors(): bool {
        return instance_provider::get_user_instances($this->user_id)->count() > 0;
    }

    /**
     * Revoke all factors for user.
     *
     * @return void
     */
    public function revoke_factors(): void {
        instance_provider::delete_user_instances($this->user_id);

        mfa_factors_revoked::create_event($this->user_id)->trigger();
    }
}
