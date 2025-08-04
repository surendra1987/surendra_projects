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

namespace auth_ssosaml\controllers;

use auth_ssosaml\exception\test_action;
use auth_ssosaml\exception\test_no_matching_logout_key;
use auth_ssosaml\exception\test_no_prior_login;
use auth_ssosaml\exception\test_unknown_login;
use auth_ssosaml\model\idp;
use auth_ssosaml\provider\process\login\make_request;
use auth_ssosaml\provider\process\test\make_logout_request;
use moodle_url;
use totara_mvc\tui_view;

/**
 * Controller for testing the IdP
 */
class idp_test extends base_admin {
    /**
     * @return tui_view
     */
    public function action(): tui_view {
        $id = $this->get_required_param('id', PARAM_INT);
        $this->set_url(new moodle_url('/auth/ssosaml/idp_test.php', ['id' => $id]));
        $idp = idp::load_by_id($id);
        $idp->sp_config->set_test_mode(true);

        // Figure out what action we're calling
        $test_action = $this->get_optional_param('test_action', null, PARAM_ALPHAEXT);
        $valid_actions = ['login', 'logout', 'login_callback', 'logout_callback'];
        $test_response = [];

        try {
            if (!empty($test_action) && in_array($test_action, $valid_actions, true)) {
                $initiate_action = 'initiate_' . $test_action;
                $process_action = 'process_' . $test_action;
                if (method_exists($this, $initiate_action)) {
                    $this->$initiate_action($idp);
                    exit;
                } else if (method_exists($this, $process_action)) {
                    $test_response = $this->$process_action($idp);
                } else {
                    throw new \coding_exception('Test action "' . $test_action . '" is not defined');
                }
            }
        } catch (test_no_prior_login $ex) {
            $test_response['warning'] = $ex->getMessage();
        } catch (test_action $ex) {
            $test_response['error'] = $ex->getMessage();
        }

        $log_entries = [];
        if ($idp->debug) {
            $log_entries = $this->execute_graphql_operation('auth_ssosaml_saml_log', [
                'input' => [
                    'idp_id' => $idp->id,
                    'active_session' => true,
                    'test' => true,
                ]
            ])['data']['entries']['items'];
        }

        $message = null;
        if (!empty($test_response['error'])) {
            $message = ['type' => 'error', 'message' => $test_response['error']];
        } else if (!empty($test_response['warning'])) {
            $message = ['type' => 'warning', 'message' => $test_response['warning']];
        } else if (!empty($test_response['success'])) {
            $message = ['type' => 'success', 'message' => $test_response['success']];
        }

        return tui_view::create('auth_ssosaml/pages/IdPTest', [
            'idp-label' => $idp->label ?: get_string('default_label', 'auth_ssosaml'),
            'test-url' => $this->url->out(),
            'attributes' => $test_response['attributes'] ?? [],
            'idp-id' => (string)$idp->id,
            'debug-log-rows' => $log_entries,
            'message' => $message,
        ]);
    }

    /**
     * @param idp $idp
     * @return never
     */
    private function initiate_login(idp $idp): void {
        (new make_request($idp, null))->execute();
        exit;
    }

    /**
     * @param idp $idp
     * @return never
     */
    private function initiate_logout(idp $idp): void {
        (new make_logout_request($idp))->execute();
        exit;
    }

    /**
     * @param idp $idp
     * @return array
     */
    private function process_login_callback(idp $idp): array {
        global $SESSION;

        if (isset($SESSION->ssosaml['test_login_attributes'])) {
            $response = $SESSION->ssosaml['test_login_attributes'];
            unset($SESSION->ssosaml['test_login_attributes']);
            return [
                'error' => $response['error'] ?? null,
                'success' => !empty($response['success']) ? get_string('test_login_success', 'auth_ssosaml') : null,
                'attributes' => $response['attributes'] ?? [],
            ];
        }

        throw new test_unknown_login();
    }

    /**
     * Process the actual logout request.
     *
     * @param idp $idp
     * @return array|string[]
     */
    private function process_logout_callback(idp $idp): array {
        global $SESSION;

        if (empty($SESSION->ssosaml['test_session'])) {
            throw new test_no_matching_logout_key();
        }

        unset($SESSION->ssosaml['test_session']);

        $results = $SESSION->ssosaml['test_logout'] ?? [];
        unset($SESSION->ssosaml['test_logout']);

        if (isset($results['status']) && $results['status']) {
            return [
                'success' => get_string('test_logout_success', 'auth_ssosaml'),
            ];
        }

        return [
            'error' => get_string('error:test_logout_failed', 'auth_ssosaml', $results['status_message'] ?? 'Unknown'),
        ];
    }
}
