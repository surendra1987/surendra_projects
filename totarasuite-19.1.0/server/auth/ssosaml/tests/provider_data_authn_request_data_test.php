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

use auth_ssosaml\model\idp\config\bindings;
use auth_ssosaml\provider\data\authn_request_data;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\provider\data\authn_request_data
 * @group auth_ssosaml
 */
class auth_ssosaml_provider_data_authn_request_data_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_creating_with_invalid_bindings(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Invalid AuthnRequest mode");
        authn_request_data::create([
            'binding' => 'invalid_binding',
            'request_id' => 'ABC1234',
        ]);
    }

    /**
     * @return void
     */
    public function test_creating_with_no_url(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Login url required");
        authn_request_data::create([
            'binding' => bindings::SAML2_HTTP_REDIRECT_BINDING,
            'request_id' => 'ABC1234',
        ]);
    }

    /**
     * @return void
     */
    public function test_creating_without_samlrequest(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("SAMLRequest required");
        authn_request_data::create([
            'binding' => bindings::SAML2_HTTP_REDIRECT_BINDING,
            'url' => 'http://example.com',
            'request_id' => 'ABC1234',
        ]);
    }

    /**
     * @return void
     */
    public function test_create_successfully(): void {
        $request_data = authn_request_data::create([
            'binding' => bindings::SAML2_HTTP_REDIRECT_BINDING,
            'url' => 'http://example.com',
            'data' => [
                'SAMLRequest' => 'request_string',
            ],
            'request_id' => 'ABC1234',
        ]);

        $this->assertEquals('http://example.com', $request_data->url);
        $this->assertEquals(bindings::SAML2_HTTP_REDIRECT_BINDING, $request_data->binding);
        $this->assertEqualsCanonicalizing(
            [
                'SAMLRequest' => 'request_string',
            ],
            $request_data->data
        );
    }
}
