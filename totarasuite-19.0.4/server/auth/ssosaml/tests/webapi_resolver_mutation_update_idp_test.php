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

use totara_webapi\phpunit\webapi_phpunit_helper;
use auth_ssosaml\model\idp;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\webapi\resolver\mutation\update_idp
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_resolver_mutation_update_idp_test extends base_saml_testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'auth_ssosaml_update_idp';
    private const QUERY = 'auth_ssosaml_idp_for_editing';

    /**
     * @return void
     */
    public function test_executing_mutation(): void {
        $this->setAdminUser();
        $create_idp = $this->ssosaml_generator->create_idp(['status' => false]);

        $original_certificate = $create_idp->certificates;
        $this->assertNotEmpty($original_certificate);

        $args = [
            "input" => [
                'id' => $create_idp->id,
                "attributes" => [
                    "metadata" => ["source" => "URL", "url" => "http://example.come/sso/metadata", "xml" => ""],
                    "label" => "test",
                    "idp_user_id_field" => "uid",
                    "totara_user_id_field" => "username",
                    "status" => true,
                    "debug" => false,
                    "login_hide" => false,
                    "create_users" => true,
                    "autolink_users" => "NO_LINK",
                    "field_mapping_config" => [
                        "field_maps" => [
                            ["internal" => "username", "external" => "uid", "update" => "CREATE"],
                            ["internal" => "email", "external" => "email", "update" => "LOGIN"],
                        ],
                    ],
                ],
                "saml_config" => [
                    "sign_metadata" => true,
                    "signing_algorithm" => 'SHA256',
                    "wants_assertions_signed" => true,
                ],
            ],
        ];

        \curl::mock_response(file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml'));

        $result = $this->execute_graphql_operation(self::MUTATION, $args);
        $id = $result->toArray()['data']['idp']['id'];

        // Reload the IdP
        $idp = idp::load_by_id($create_idp->id);
        $new_certificates = $idp->certificates;
        $this->assertNotEmpty($new_certificates);

        // Confirm the certificates did not change during the update
        $this->assertSame($original_certificate, $new_certificates);

        $result = $this->execute_graphql_operation(self::QUERY, ['id' => $id]);
        $data = $result->data;
        $this->test_assertions($args, $data);

        $args['input']["attributes"]["metadata"]['source'] = 'XML';
        $args['input']["attributes"]["metadata"]['url'] = '';
        $args['input']["attributes"]["metadata"]['xml'] = file_get_contents(__DIR__ . '/fixtures/idp/sample_idp_2.xml');

        $result = $this->execute_graphql_operation(self::MUTATION, $args);
        $id = $result->data['idp']['id'];
        $result = $this->execute_graphql_operation(self::QUERY, ['id' => $id]);
        $data = $result->data;
        $this->test_assertions($args, $data);

        $args['input']["attributes"]["metadata"]['xml'] = '<xml></xml>';
        $result = $this->execute_graphql_operation(self::MUTATION, $args);
        $this->assertEquals($result->toArray()['errors'][0]['message'], get_string('error:invalid_metadata', 'auth_ssosaml'));

        $args['input']['id'] = 20000;
        $result = $this->execute_graphql_operation(self::MUTATION, $args);
        $this->stringContains($result->toArray()['errors'][0]['extensions']['debugMessage'], 'Can not find data record in database');
    }

    /**
     * @return void
     */
    public function test_query_requires_site_config_capability(): void {
        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage('Sorry, but you do not currently have permissions to do that (Manage SAML Configuration)');
        $this->resolve_graphql_mutation(self::MUTATION);
    }

    /**
     * @param array $args
     * @param array $data
     * @return void
     */
    protected function test_assertions(array $args, array $data): void {
        if ($args['input']["attributes"]["metadata"]['source'] == 'URL') {
            $this->assertEmpty($data['idp']['metadata']['xml']);
            $this->assertSame($args['input']["attributes"]["metadata"]['url'], $data['idp']['metadata']['url']);
        } else {
            $this->assertSame($args['input']["attributes"]["metadata"]['xml'], $data['idp']['metadata']['xml']);
            $this->assertEmpty($data['idp']['metadata']['url']);
        }
        $this->assertSame($args['input']["attributes"]["metadata"]['source'], $data['idp']['metadata']['source']);
        $this->assertSame($args['input']["attributes"]['label'], $data['idp']['label']);
        $this->assertSame($args['input']["attributes"]['idp_user_id_field'], $data['idp']['idp_user_id_field']);
        $this->assertSame($args['input']["attributes"]['totara_user_id_field'], $data['idp']['totara_user_id_field']);
        $this->assertTrue($data['idp']['status']);
        foreach ($data['idp']["field_mapping_config"]['field_maps'] as $key => $value) {
            $this->assertSame($args['input']["attributes"]["field_mapping_config"]['field_maps'][$key], $value);
        }
        $this->assertTrue($data['idp']["saml_config"]['sign_metadata']);
        $this->assertSame($args['input']['saml_config']['signing_algorithm'], $data['idp']["saml_config"]['signing_algorithm']);
        $this->assertTrue($data['idp']["saml_config"]['wants_assertions_signed']);
    }
}
