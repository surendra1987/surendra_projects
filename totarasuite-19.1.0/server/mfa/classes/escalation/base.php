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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_mfa
 */

namespace core_mfa\escalation;

use coding_exception;
use core_mfa\data_provider\factor as factor_provider;
use core_mfa\data_provider\instance as instance_provider;

/**
 * Base class for MFA escalation.
 */
abstract class base {

    /**
     * User Id
     * @var int
     */
    protected $user_id;

    /**
     * @param int $user_id
     */
    public function __construct(int $user_id) {
        $this->user_id = $user_id;
    }

    /**
     * Requires MFA by redirecting the user to MFA page.
     *
     * @param array|null $data
     * @return void
     */
    public function trigger(?array $data = null): void {
        global $USER;
        $USER->mfa_completed = false;
        $USER->pseudo_id = $this->user_id;
        $USER->mfa_action = get_class($this);
        $this->on_trigger($data);
    }

    /**
     * Set extra requirements on trigger
     *
     * @return void
     */
    abstract protected function on_trigger(?array $data = null): void;


    /**
     * Verify user input for a particular factor.
     *
     * @param string $factor
     * @param array $data
     * @return bool
     */
    public function verify(string $factor, array $data): bool {
        global $USER, $CFG;

        if ($USER->pseudo_id !== $this->user_id) {
            throw new coding_exception('Wrong user match');
        }

        require_once("$CFG->libdir/authlib.php");

        $user = get_complete_user_data('id', $this->user_id);

        if (login_is_lockedout($user)) {
            return false;
        }

        $factor = factor_provider::get_enabled_factor($factor);

        if (is_null($factor)) {
            return false;
        }

        $instances = instance_provider::get_user_instances_for_factor($this->user_id, $factor->get_name());
        $verified = $factor->verify($this->user_id, $data, $instances->all());

        if (!$verified) {
            if ($factor->should_increment_lockout()) {
                login_attempt_failed($user);
            }
            return false;
        }

        $USER->mfa_completed = true;

        return true;
    }

    /**
     * Complete called after the MFA verification process. Redirects the user.
     *
     * @return void
     */
    public function complete(): void {
        global $USER;
        if (empty($USER->mfa_completed) || empty($USER->pseudo_id)) {
            throw new coding_exception("MFA verification not completed");
        }
        $this->on_complete();
    }

    /**
     * Function implemented by child class to be executed on complete.
     *
     * @return void
     */
    abstract protected function on_complete() :void;
}
