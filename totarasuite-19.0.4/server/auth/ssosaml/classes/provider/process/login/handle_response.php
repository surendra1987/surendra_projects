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

use auth_ssosaml\exception\response_validation;
use auth_ssosaml\exception\test_bad_login_request;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\user\manager;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\provider\logging\factory as logger_factory;
use auth_ssosaml\provider\process\base;
use auth_ssosaml\provider\process\with_provider;

/**
 * Process class for SAML login requests
 */
class handle_response extends base {
    use with_provider;

    /**
     * @var idp
     */
    protected idp $idp;

    /**
     * @var manager
     */
    protected manager $user_manager;

    /**
     * @param idp $idp
     * @param manager|null $user_manager
     */
    public function __construct(idp $idp, ?manager $user_manager = null) {
        $this->idp = $idp;
        $this->user_manager = $user_manager ?? new manager($idp);
    }

    /**
     * Process the login response that's posted.
     *
     * @return array
     */
    public function execute(): array {
        global $SESSION;

        // If we're logged in & we're in test mode, be a bit defensive with the checks
        if (isloggedin() && !isguestuser()) {
            if (empty($SESSION->ssosaml['test_login'])) {
                // At this point they're already logged in and this isn't a test request, we should not be entertaining this.
                return [
                    'action' => 'login_warning',
                ];
            }

            // We're sure they're part of the test page this time.
            try {
                $response = $this->get_provider($this->idp)->get_login_response();
            } catch (response_validation $ex) {
                // We think we're a test login with a failure, show the test page with the error
                return $this->handle_test_login($response ?? null, $ex->getMessage());
            }

            // Check, is this a test session?
            if ($response->in_response_to && $this->idp->get_session_manager()->is_test_session($response->in_response_to)) {
                return $this->handle_test_login($response);
            }
        } else {
            // Process the response normally then
            $response = $this->get_provider($this->idp)->get_login_response();
        }

        // At this point, if the response is a test session then we've got some reposting of old data, fail it
        if ($response->in_response_to && $this->idp->get_session_manager()->is_test_session($response->in_response_to)) {
            throw new test_bad_login_request();
        }

        // Final check, if it has the test prefix, something dodgy is happening.
        if ($response->in_response_to && str_starts_with($response->in_response_to, 'ttp_')) {
            throw new test_bad_login_request();
        }

        return $this->user_manager->process_login($response);
    }

    /**
     * @param authn_response|null $response
     * @param string|null $error
     * @return never
     */
    protected function handle_test_login(?authn_response $response, ?string $error = null): array {
        global $USER, $SESSION;

        $session_manager = $this->idp->get_session_manager();
        $attributes = [
            'message' => null,
            'attributes' => [],
            'error' => $error,
        ];

        if (empty($attributes['error'])) {
            // Flatten the attributes back down
            $deliminator = $this->idp->field_mapping_config['delimiter'];
            $attributes['attributes'][] = ['name' => 'nameid', 'value' => $response->name_id];

            foreach ($response->attributes as $key => $value) {
                $attributes['attributes'][] = ['name' => $key, 'value' => join($deliminator, $value)];
            }

            if ($response->log_id) {
                $logger = logger_factory::get_logger($this->idp);
                $logger->update_log_entry_user_id($response->log_id, $USER->id);
            }

            // In both cases mark the session as complete
            $session_manager->complete_sp_initiated_session($USER->id, $response);

            $attributes['success'] = true;
        }

        $SESSION->ssosaml['test_login'] = 0;
        $SESSION->ssosaml['test_login_attributes'] = $attributes;

        return [
            'action' => 'test',
        ];
    }
}
