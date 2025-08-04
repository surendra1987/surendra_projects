<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @author Murali Nair <murali.nair@totara.com>
 *
 * @package perform_goal
 */

use core\testing\generator as core_generator;
use GraphQL\Error\DebugFlag;
use totara_oauth2\testing\generator as oauth2_generator;
use totara_webapi\request;
use totara_webapi\server;
use totara_webapi\controllers\api_controller;
use totara_webapi\controllers\external;

/**
 * Holds convenient methods and structures for testing external APIs.
 */
trait external_api_phpunit_helper {
    /**
     * Confirms the external API operation failed.
     *
     * @param array<string,mixed> $result result from external API operation.
     * @param string $exp expected error message.
     */
    final protected static function assert_external_operation_failed(
        array $result,
        string $exp
    ): void {
        $errors = $result['errors'] ?? [];
        self::assertNotEmpty(
            $errors, 'no errors in response' . print_r($result, true)
        );

        $msg = $errors[0]['message'] ?? '';
        if (str_contains($msg, $exp)) {
            return;
        }

        $debug_msg = $errors[0]['extensions']['debugMessage'] ?? '';
        self::assertStringContainsString(
            $exp, $debug_msg, "'$exp' not found in error/debugging traces"
        );
    }

    /**
     * Confirms the external API operation passed.
     *
     * @param array<string,mixed> $result result from external API operation.
     */
    final protected static function assert_external_operation_successful(
        array $result
    ): void {
        $errors = $result['errors'] ?? [];
        self::assertEmpty($errors, 'got errors: ' . print_r($errors, true));
    }

    /**
     * Returns the 'real' response data from an external API operation.
     *
     * @param array<string,mixed> $result result from external API operation.
     */
    final protected static function get_operation_data(array $result) {
        return reset($result['data']);
    }

    /**
     * Creates an operation string to pass to the external API controller.
     *
     * @param string $op_format the external API operation. This is used in an
     *        sprintf() call with this as the format string and the values taken
     *        from $parms.
     * @param array<mixed,array<string,mixed>> $parms $operation parameters in
     *        this format:
     *        [
     *          'parm1' => ['parm1_1' => value1_1, 'parm1_2' => value1_2, ...],
     *          'parm2' => ['parm2_1' => value2_1, 'parm2_2' => value2_2, ...],
     *          ...
     *          'parmN' => ['parmN_1' => valueN_1, 'parmN_2' => valueN_2, ...],
     *        ]
     *        Of course that also implies $op_format has placeholders for these
     *        parameters in _this_ order. If any of the parameters have an empty
     *        array as the values, it is skipped - which can be used to simulate
     *        invalid or missing parameters.
     *
     * @return string the operation string to pass to self::make_external_api_request().
     */
    private static function make_external_api_op(
        string $op_format,
        array $parms
    ): string {
        $stringify = function (array $values) use (&$stringify): string {
            $str = '';

            foreach ($values as $field => $raw) {
                $value = null;

                switch (true) {
                    case $field === 'description':
                        $escaped = str_replace("\"", "\\\"", $raw);
                        $value = "\"$escaped\"";
                        break;

                    case is_array($raw):
                        $value = '{' . $stringify($raw) . '}';
                        break;

                    case is_string($raw):
                        $value = "\"$raw\"";
                        break;

                    default:
                        $value = $raw;
                }

                $kvp = "$field: $value";
                $str = $str ? "$str,\n$kvp" : $kvp;
            }

            return $str;
        };

        $final_parms = [];
        foreach ($parms as $parm => $values) {
            $final_parms[] = $values
                ? sprintf('%s: {%s}', $parm, $stringify($values))
                : '';
        }

        return sprintf($op_format, ...$final_parms);
    }

    /**
     * Helper method for setting a header for an external API request.
     *
     * @param bool $assign_staff_manager_role if true makes the API user a staff
     *        manager.
     *
     * @return stdClass the API user.
     */
    final protected static function helper_set_auth_header_get_api_user(
        bool $assign_staff_manager_role = true
    ): stdClass {
        global $DB;
        $generator = oauth2_generator::instance();
        $user = core_generator::instance()->create_user();
        $role = $DB->get_record('role', ['archetype' => 'apiuser'], 'id');
        role_assign($role->id, $user->id, context_system::instance());

        if ($assign_staff_manager_role) {
            $role = $DB->get_record('role', ['shortname' => 'staffmanager']);
            role_assign($role->id, $user->id, context_system::instance()->id);
        }

        $api_client = \totara_api\model\client::create(
            '123',
            $user->id,
            null,
            null,
            1,
            ['create_client_provider' => true]
        );

        $client_provider = $api_client->oauth2_client_providers->first();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $access_token = $generator->create_access_token_from_client_provider($client_provider->get_entity_copy(), time() + HOURSECS);
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer " . $access_token;

        return $user;
    }

    /**
     * Helper method for External API testing.
     *
     * @return api_controller the external API controller.
     */
    final protected static function get_external_api_instance(): api_controller {
        $class = new class() extends external {
            private $input_request_data = '';
            private $query;

            public function set_input_request_data(string $input_request_data): self {
                $this->input_request_data = $input_request_data;
                return $this;
            }

            public function __construct(?bool $stop_execution = true, ?string $query = null) {
                $this->query = $query;
                parent::__construct($stop_execution);
            }

            public function action_graphql_request(): void {
                $execution_context = $this->get_execution_context();
                $request = new request(
                    $execution_context->get_endpoint_type(),
                    [
                        'operationName' => null,
                        'query' => $this->input_request_data
                    ]
                );
                $server = new server($execution_context, DebugFlag::INCLUDE_DEBUG_MESSAGE);
                $result = $server->handle_request($request);
                $server->send_response($result, false);
            }
        };

        return new $class(false);
    }

    /**
     * Helper method to call common things when making an External API request.
     *
     * @param string $op_format the external API operation. This is used in an
     *        sprintf() call with this as the format string and the values taken
     *        from $parms.
     * @param array<mixed,array<string,mixed>> $parms $operation parameters in
     *        this format:
     *        [
     *          'parm1' => ['parm1_1' => value1_1, 'parm1_2' => value1_2, ...],
     *          'parm2' => ['parm2_1' => value2_1, 'parm2_2' => value2_2, ...],
     *          ...
     *          'parmN' => ['parmN_1' => valueN_1, 'parmN_2' => valueN_2, ...],
     *        ]
     *        Of course that also implies $op_format has placeholders for these
     *        parameters in _this_ order. If any of the parameters have an empty
     *        array as the values, it is skipped - which can be used to simulate
     *        invalid or missing parameters.
     * @param null|stdClass $user user (from helper_set_auth_header_get_api_user())
     *        to masquerade as when executing the external API operation. If not
     *        provided, will generate one.
     *
     * @return array<string,mixed> the response.
     */
    final protected static function make_external_api_request(
        string $op_format,
        array $parms,
        ?stdClass $user = null
    ): array {
        $api_user = $user ?? self::helper_set_auth_header_get_api_user();
        self::setUser($api_user);

        $op = self::make_external_api_op($op_format, $parms);

        ob_start();
        self::get_external_api_instance()
            ->set_input_request_data($op)
            ->process('graphql_request');

        $response = ob_get_clean();
        return json_decode($response, true);
    }
}
