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

namespace auth_ssosaml\provider\process\login;

use auth_ssosaml\model\idp;
use auth_ssosaml\provider\process\base;
use auth_ssosaml\provider\process\with_provider;
use auth_ssosaml\provider\session_manager;

/**
 * Process class for SAML login requests
 */
class make_request extends base {
    use with_provider;

    /**
     * @var idp
     */
    protected idp $idp;

    /**
     * @var string|null
     */
    protected ?string $wants_url;

    /**
     * @param idp $idp
     * @param string|null $wants_url
     */
    public function __construct(idp $idp, ?string $wants_url) {
        $this->idp = $idp;
        $this->wants_url = $wants_url;
    }

    /**
     * Submit the login request bindings
     *
     * @return void
     */
    public function execute() {
        global $SESSION;
        if ($this->idp->sp_config->test_mode) {
            $SESSION->ssosaml['test_login'] = 1;

            // If we're making a test request, clear any other sitting requests for this Idp (one only)
            session_manager::clear_test_sessions(session_id(), $this->idp->id);
        }

        $authn_request = $this->get_provider($this->idp)->make_login_request($this->wants_url);

        // Handle the request
        $this->submit_request_binding($authn_request);
    }
}
