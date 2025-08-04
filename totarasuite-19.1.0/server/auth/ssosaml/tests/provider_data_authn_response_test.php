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

use auth_ssosaml\model\idp\config\nameid;
use auth_ssosaml\provider\data\authn_response;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\provider\data\authn_response
 * @group auth_ssosaml
 */
class auth_ssosaml_provider_data_authn_response_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_attributes_and_properties(): void {
        $response = authn_response::make([
            'in_response_to' => 'a_response_string',
            'expired_at' => gmdate('Y-m-d\TH:i:s\Z', 1520590256),
            'status' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
            'issuer' => 'idp_issuer',
            'name_id' => 'example_name_id',
            'name_id_format' => nameid::FORMAT_ENTITY,
            'attributes' => [
                'name' => ['Johnny', 'Rapu'],
            ],
        ]);

        // test get nameid as an attribute
        $this->assertEquals('example_name_id', $response->get_attribute('nameid'));

        // test attributes
        $this->assertEquals(['Johnny', 'Rapu'], $response->get_attribute('name'));
        $this->assertNull($response->get_attribute('lorem_ipsum'));

        // test defined properties
        $this->assertEquals('example_name_id', $response->name_id);
        $this->assertEquals(nameid::FORMAT_ENTITY, $response->name_id_format);
        $this->assertEquals('a_response_string', $response->in_response_to);
        $this->assertEquals('idp_issuer', $response->issuer);

        // test undefined properties
        $this->assertNull($response->relay_state);
        $this->assertNull($response->session_index);
    }

    /**
     * @return array[]
     */
    public static function validation_provider(): array {
        return [
            ['name_id'],
            ['name_id_format'],
            ['issuer'],
            ['status'],
        ];
    }

    /**
     * @dataProvider validation_provider
     * @param string $field
     * @return void
     */
    public function test_required_fields_are_validated(string $field): void {
        $props = [
            'name_id' => 'a_random_nameid',
            'name_id_format' => nameid::FORMAT_PERSISTENT,
            'session_index' => 'a_random_index',
            'issuer' => 'issuer_id_value',
            'status' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
        ];
        unset($props[$field]);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("$field field is required in data provided");
        authn_response::make($props);
    }
}
