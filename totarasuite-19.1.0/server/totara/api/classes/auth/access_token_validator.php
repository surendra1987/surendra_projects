<?php
/**
 * This file is part of Totara TXP
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
 * @author  Michael Ivanov <michael.ivanov@totara.com>
 * @package totara_api
 */

namespace totara_api\auth;

use core\session\manager;
use Psr\Http\Message\ServerRequestInterface;
use totara_api\exception\api_access_exception;
use totara_api\model\client;
use totara_api\pdo\client_service_account;
use totara_core\http\util;
use totara_oauth2\entity\client_provider;
use totara_oauth2\io\request;
use totara_oauth2\server;

/**
 * Class access_token_validator
 *
 * This class provides methods to validate an OAuth2 access token, get the API client associated with the request,
 * and log in the API user.
 */
class access_token_validator {
    /**
     * Validates the access token provided in the HTTP request headers.
     *
     * @return ServerRequestInterface Returns the verified request object.
     *
     * @throws api_access_exception Throws an exception if the HTTP request headers are missing or invalid, if the
     * required Authorization header is not present, or if the access token is missing, expired, or invalid.
     */
    public static function validate_access_token(): ServerRequestInterface {
        $headers = util::get_request_headers();
        if (empty($headers)) {
            throw new api_access_exception('Missing or invalid HTTP request headers');
        }

        // Check for missing Authorization header containing the bearer token. (Apache security default setting can strip this in requests!)
        $header_keys = array_keys($headers);
        $auth_header_found = false;
        $auth_header_wanted = "authorization";
        foreach ($header_keys as $header_key) {
            if (trim(strtolower($header_key)) === $auth_header_wanted) {
                $auth_header_found = true;
                break;
            }
        }

        if (!$auth_header_found) {
            throw new api_access_exception('The request did not contain the required Authorization header. Ensure you set the header in your request and that it is not being stripped by your server or proxy configuration.');
        }

        $oauth2_request = request::create_from_global(
            $_GET,
            $_POST,
            $headers,
            $_SERVER
        );

        $server = server::create();
        $request = $server->verify_request($oauth2_request);

        if (!$request) {
            throw new api_access_exception('Missing, expired or invalid access token. Ensure the Authorization header is set to a valid Bearer token.');
        }

        return $request;
    }

    /**
     * Retrieves the API client associated with the OAuth2 request.
     *
     * @param ServerRequestInterface $request The request object.
     *
     * @return client Returns the client object.
     *
     * @throws api_access_exception Throws an exception if the OAuth2 client provider or API client cannot be identified.
     */
    public static function get_client_by_request(ServerRequestInterface $request): client {
        $client_provider = client_provider::repository()->find_by_client_id($request->getAttribute('oauth_client_id'));

        if (is_null($client_provider)) {
            throw new api_access_exception('Couldn\'t identify OAuth2 client provider for this request.');
        }

        $client = $client_provider->clients()->one();
        if (is_null($client)) {
            throw new api_access_exception('Couldn\'t identify API client for this request.');
        }

        return client::load_by_entity($client);
    }

    /**
     * Logs in the API user associated with the client object.
     *
     * @param client $client The client object.
     *
     * @return void
     *
     * @throws api_access_exception Throws an exception if the API user cannot be identified or is invalid.
     */
    public static function login_api_user(client $client): void {
        $user = $client->user;
        if (is_null($user)) {
            throw new api_access_exception('Couldn\'t identify API user for this request.');
        }

        if (client_service_account::VALID != client::validate_api_user($user, $client->tenant_id)) {
            throw new api_access_exception('API user is invalid.');
        }

        manager::set_user($user->to_record());
    }

}