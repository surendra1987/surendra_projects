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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider\process\logout;

use auth_ssosaml\model\idp;
use auth_ssosaml\provider\data\logout_message;
use auth_ssosaml\provider\factory;
use auth_ssosaml\provider\process\base;
use core\session\manager;
use moodle_url;
use auth_ssosaml\exception\response_validation;

/**
 * Process class for SAML logout message
 */
class handle_message extends base {
    /**
     * @var idp
     */
    protected idp $idp;

    /**
     * @param idp $idp
     */
    public function __construct(idp $idp) {
        $this->idp = $idp;
    }

    /**
     * Process the actual logout
     *
     * @return void
     */
    public function execute() {
        global $SESSION;

        $provider = factory::get_provider($this->idp);
        $message = $provider->get_logout_message();

        if ($message->type == logout_message::LOGOUT_RESPONSE) {
            // Check if this was a real logout call or not
            $test_sessions = !empty($SESSION->ssosaml['test_session']) ? $SESSION->ssosaml['test_session'] : [];
            if ($message->in_response_to && is_array($test_sessions) && in_array($message->in_response_to, $test_sessions)) {
                $SESSION->ssosaml['test_logout'] = [
                    'status' => $message->status,
                    'status_message' => $message->status_message,
                ];

                redirect(new moodle_url('/auth/ssosaml/idp_test.php', [
                    'id' => $this->idp->id,
                    'test_action' => 'logout_callback',
                ]));
                return;
            }

            if (!$message->status) {
                throw response_validation\logout_failed::make($message->status_message);
            }

            if ($this->idp->logout_url) {
                redirect(new moodle_url($this->idp->logout_url));
            }

            // We bounce to the landing page
            redirect('/');
            exit;
        }

        $session_manager = $this->idp->get_session_manager();

        // Pull out the sessions we need to invalidate
        $idp_sessions = $session_manager->get_sessions($message->name_id, $message->session_index ?? null);

        $user_id = null;
        foreach ($idp_sessions as $idp_session) {
            $user_id = $idp_session->user_id;

            // Remove the sessions
            manager::kill_session($idp_session->session_id);
            $idp_session->delete();
        }

        // Make logout response for IdP
        $logout_response = $provider->make_logout_response($message, $user_id);

        // Send message back to Idp with logout response
        $this->submit_request_binding($logout_response);
    }
}
