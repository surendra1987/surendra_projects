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

use core\login\complete_login_callback;

/**
 * MFA escalation class for login.
 */
class login extends base {

    /**
     * @param array|null $data Containing the following:
     * array['callback'] complete_login_callback Callback function to call after complete login.
     * @return void
     */
    protected function on_trigger(?array $data = null): void {
        global $USER, $SESSION;

        /** @var complete_login_callback $complete_login_callback*/
        $complete_login_callback = $data['callback'] ?? null;

        if (!is_null($complete_login_callback)) {
            $USER->complete_login_function = $complete_login_callback->get_function();
            $USER->complete_login_args = $complete_login_callback->get_args();
        }

        if (!empty($SESSION->wantsurl)) {
            $USER->mfa_redirect_url = $SESSION->wantsurl;
        }
    }

    /**
     * @inheritDoc
     */
    protected function on_complete(): void {
        global $CFG, $USER;

        $user = get_complete_user_data('id', $this->user_id);
        $redirect_url = $CFG->wwwroot . '/';

        if (!empty($USER->mfa_redirect_url)) {
            $redirect_url = $USER->mfa_redirect_url;
        }

        $complete_login_callback = null;
        if (!empty($USER->complete_login_function)) {
            $complete_login_callback = complete_login_callback::create(
                $USER->complete_login_function,
                $USER->complete_login_args
            );
        }

        complete_user_login($user, $complete_login_callback);
        redirect($redirect_url);
    }
}
