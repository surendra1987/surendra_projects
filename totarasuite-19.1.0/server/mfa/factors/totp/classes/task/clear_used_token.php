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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package mfa_totp
 */

namespace mfa_totp\task;

use core_mfa\data_provider\factor as factor_provider;
use mfa_totp\entity\token as token_entity;

/**
 * Clear used totp token task
 */
class clear_used_token extends \core\task\scheduled_task {

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_name', 'mfa_totp');
    }

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function get_component() {
        return get_string('component', 'mfa_totp');
    }

    /**
     * Do the job.
     */
    public function execute() {
        $factor = factor_provider::get_enabled_factor('totp');
        if (is_null($factor)) {
            return;
        }

        token_entity::repository()
            ->where('created_at', '<', time() - HOURSECS)
            ->delete();
    }
}