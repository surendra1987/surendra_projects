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

namespace auth_ssosaml\provider\process\test;

use auth_ssosaml\exception\test_no_logout;
use auth_ssosaml\exception\test_no_prior_login;
use auth_ssosaml\model\idp;
use auth_ssosaml\provider\factory;
use auth_ssosaml\provider\process\base;

/**
 * Process class for SAML logout requests
 */
class make_logout_request extends base {
    /**
     * How many test request ids will we keep hold of.
     */
    private const TEST_MEMORY_LIMIT = 10;

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

        // Confirm logout is enabled
        if (!$this->idp->logout_idp) {
            throw new test_no_logout();
        }

        // Lookup the session
        $session = $this->idp->get_session_manager()->get_test_session();

        // There's no known session for us to react to, throw an error
        if (!$session) {
            throw new test_no_prior_login();
        }

        // In our case, we cannot control how the test logout behaves, so instead flag a note to ourselves that
        // this IdP is actually a test case.
        $logout_request = factory::get_provider($this->idp)->make_logout_request($session);

        // Store the request Id
        if (!isset($SESSION->ssosaml['test_session'])) {
            $SESSION->ssosaml['test_session'] = [];
        }

        if (count($SESSION->ssosaml['test_session']) > self::TEST_MEMORY_LIMIT) {
            // Pop the oldest off
            array_shift($SESSION->ssosaml['test_session']);
        }

        $SESSION->ssosaml['test_session'][] = $logout_request->request_id;

        // Handle the request
        $this->submit_request_binding($logout_request);
    }
}
