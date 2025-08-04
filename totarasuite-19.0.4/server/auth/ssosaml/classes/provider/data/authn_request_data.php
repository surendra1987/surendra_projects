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

namespace auth_ssosaml\provider\data;

use auth_ssosaml\model\idp\config\bindings;
use coding_exception;

/**
 * This class holds request data generated to make an AuthNRequest.
 *
 * @property-read string $binding SingleSignOnMode used. [HTTP-POST|HTTP-Redirect]
 * @property-read string $url Destination of the IdP to login into
 * @property-read array $data Data sent to the IdP to initiate the login {
 * @type string $SAMLRequest Base64 encoded value of the AuthnRequest XML
 * @type string $Signature Base64 encoded value of the request signature (Used only with HTTP-Redirect)
 * @type string $SigAlg Signature algorithm used (Used only with HTTP-Redirect)
 * @type string $RelayState Optional url for the request (Page to redirect to after login)
 *  }
 */
class authn_request_data extends saml_request {
    /**
     * Create an instance of the class
     *
     * @param array $data
     * @return self
     */
    public static function create(array $data): self {
        self::validate($data);
        return new self(
            $data['binding'],
            $data['url'],
            $data['request_id'],
            $data['data'] ?? []
        );
    }

    /**
     * Validate the data provided
     *
     * @param array $data
     * @return void
     * @throws coding_exception
     */
    private static function validate(array $data): void {
        if (empty($data['binding']) ||
            !in_array($data['binding'], [bindings::SAML2_HTTP_REDIRECT_BINDING, bindings::SAML2_HTTP_POST_BINDING])
        ) {
            throw new coding_exception("Invalid AuthnRequest mode");
        }

        if (empty($data['url'])) {
            throw new coding_exception("Login url required");
        }

        if (empty($data['request_id'])) {
            throw new coding_exception("Request id required");
        }

        if (empty($data['data']['SAMLRequest'])) {
            throw new coding_exception("SAMLRequest required");
        }
    }
}