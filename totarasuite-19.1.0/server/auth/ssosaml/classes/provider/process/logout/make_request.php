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

use auth_ssosaml\entity\session;
use auth_ssosaml\model\idp;
use auth_ssosaml\provider\factory;
use auth_ssosaml\provider\logging\db_logger;
use auth_ssosaml\provider\process\base;
use auth_ssosaml\provider\session_manager;

/**
 * Process class for SAML logout requests
 */
class make_request extends base {
    /**
     * Process the actual logout
     *
     * @return void
     */
    public function execute() {
        // User is logging out, clean up/remove any test sessions they had under this session_id
        $session_id = session_id();

        if (!empty($session_id)) {
            db_logger::clear_test_entries($session_id);
            session_manager::clear_test_sessions($session_id);
        }

        // Lookup the session
        /** @var session $session */
        $session = session_manager::get_active_session();

        // There's no known session for us to react to, let the regular logout flow
        if (!$session) {
            return;
        }

        $idp = idp::load_by_id($session->idp_id);
        if (!$idp->logout_idp || !$idp->status) {
            // Delete the session
            $session->delete();
            return;
        }

        $logout_request = factory::get_provider($idp)->make_logout_request($session);

        // Normal logout has to happen (as we interrupt it)
        require_logout();

        // Delete the session object
        $session->delete();

        // Handle the request
        $this->submit_request_binding($logout_request);
    }
}
