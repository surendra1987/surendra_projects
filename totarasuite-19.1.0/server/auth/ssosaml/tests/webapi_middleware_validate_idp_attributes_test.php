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

use auth_ssosaml\webapi\middleware\validate_idp_attributes;
use core\webapi\resolver\result;
use totara_webapi\client_aware_exception;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\webapi\middleware\validate_idp_attributes
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_middleware_validate_idp_attributes_test extends base_saml_testcase {
    /**
     * Assert that we cannot accidentally map the identifier field to another value.
     *
     * @return void
     */
    public function test_identifier_fields_incorrect(): void {
        $input = [
            'attributes' => [
                'metadata' => [
                    'source' => 'URL',
                    'url' => 'http://example.com',
                ],
                'idp_user_id_field' => 'uid',
                'totara_user_id_field' => 'email',
                'field_mapping_config' => [
                    'deliminator' => ',',
                    'field_maps' => [
                        [
                            'internal' => 'username',
                            'external' => 'uid',
                            'update' => 'CREATE'
                        ],
                        [
                            'internal' => 'email',
                            'external' => 'email',
                            'update' => 'LOGIN'
                        ],
                    ]
                ]
            ]
        ];

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('The User Identifier configuration and field mappings must match.');

        $this->call_middleware(new validate_idp_attributes(), $input);
    }

    /**
     * Assert that we cannot accidentally map the identifier field to another value.
     *
     * @return void
     */
    public function test_identifier_fields_correct(): void {
        $input = [
            'attributes' => [
                'metadata' => [
                    'source' => 'URL',
                    'url' => 'http://example.com',
                ],
                'idp_user_id_field' => 'uid',
                'totara_user_id_field' => 'username',
                'field_mapping_config' => [
                    'deliminator' => ',',
                    'field_maps' => [
                        [
                            'internal' => 'username',
                            'external' => 'uid',
                            'update' => 'CREATE'
                        ],
                        [
                            'internal' => 'email',
                            'external' => 'email',
                            'update' => 'LOGIN'
                        ],
                    ]
                ]
            ]
        ];

        $result = $this->createMock(result::class);

        $count = 0;
        $callback = function () use (&$count, $result) {
            $count++;
            return $result;
        };

        $this->call_middleware(new validate_idp_attributes(), $input, $callback);
        $this->assertSame(1, $count);
    }
}
