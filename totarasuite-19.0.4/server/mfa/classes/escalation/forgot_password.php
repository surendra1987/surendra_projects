<?php
/*
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

/**
 * MFA escalation class for password reset.
 */
class forgot_password extends base {

    /**
     * @param array|null $data Containing the following:
     * array['token'] Password reset token.
     * @return void
     */
    protected function on_trigger(?array $data = null): void {
        global $SESSION;
        if (empty($data['token'])) {
            throw new coding_exception("Token missing");
        }

        $SESSION->password_reset_token = $data['token'];
    }

    /**
     * @inheritDoc
     */
    protected function on_complete(): void {
        global $CFG;

        redirect("{$CFG->wwwroot}/login/forgot_password.php");
    }
}
