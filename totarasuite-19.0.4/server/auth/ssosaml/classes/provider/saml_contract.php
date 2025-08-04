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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider;

use auth_ssosaml\entity\session;
use auth_ssosaml\exception\response_validation;
use auth_ssosaml\model\idp\config;
use auth_ssosaml\model\idp\metadata;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\provider\data\logout_message;
use auth_ssosaml\provider\data\saml_request;
use auth_ssosaml\provider\logging\contract as logger_contract;

/**
 * SAML contract
 */
interface saml_contract {

    /**
     * Get an instance of the SAML provider.
     *
     * @param config $config
     * @param metadata $idp_metadata
     * @param session_manager $session_manager
     * @param assertion_manager $assertion_manager
     * @param logger_contract $logger
     * @return saml_contract
     */
    public static function get_instance(
        config $config,
        metadata $idp_metadata,
        session_manager $session_manager,
        assertion_manager $assertion_manager,
        logger_contract $logger
    ): self;

    /**
     * Get the service provider metadata.
     *
     * @return string An XML representation of the Metadata
     */
    public function get_sp_metadata(): string;

    /**
     * Get Issuer from the SAML message
     *
     * @return string
     */
    public static function get_issuer(): string;

    /**
     * Make an SP-Initiated login request(AuthnRequest).
     *
     * @param string|null $wants_url
     * @return saml_request Data required to redirect to IdP's login
     */
    public function make_login_request(?string $wants_url = null): saml_request;

    /**
     * Get the response of a single sign on request (AuthnRequest)
     * This must also validate the response according to the SAML Specs
     * Response is gotten from the HTTP request data ($_POST|$_GET)
     *
     * @return authn_response
     * @throws response_validation When the response is invalid.
     */
    public function get_login_response(): authn_response;

    /**
     * Make a single logout request.
     *
     * @param session $session
     * @return saml_request
     */
    public function make_logout_request(session $session): saml_request;

    /**
     * Get the logout message(response/request) of a single logout.
     *
     * Can be a logout response or a logout request
     * @return logout_message
     */
    public function get_logout_message(): logout_message;

    /**
     * Make IdP initiated single logout response.
     *
     * @param logout_message $message
     * @param int|null $user_id
     * @return saml_request
     */
    public function make_logout_response(logout_message $message, ?int $user_id): saml_request;
}
