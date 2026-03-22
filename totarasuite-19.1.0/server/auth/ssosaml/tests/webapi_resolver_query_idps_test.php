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

use auth_ssosaml\model\idp;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\webapi\resolver\query\idps
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_resolver_query_idps_test extends base_saml_testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'auth_ssosaml_idps';

    /**
     * @return void
     */
    public function test_query_successful(): void {
        $this->setAdminUser();

        idp::create(['status' => true, 'label' => 'test', 'idp_user_id_field' => '', 'totara_user_id_field' => 'username'], []);

        $result = $this->parsed_graphql_operation(self::QUERY);
        $data = $this->get_webapi_operation_data($result);
        $this->assertCount(1, $data['items']);

        foreach ($data['items'] as $idp) {
            $this->assertSame('auth_ssosaml_idp', $idp['__typename']);
            $this->assertArrayHasKey('id', $idp);
            $this->assertArrayHasKey('source', $idp['metadata']);
            $this->assertArrayHasKey('url', $idp['metadata']);
            $this->assertArrayHasKey('xml', $idp['metadata']);
            $this->assertArrayHasKey('label', $idp);
            $this->assertArrayHasKey('idp_user_id_field', $idp);
            $this->assertArrayHasKey('totara_user_id_field', $idp);
            $this->assertArrayHasKey('status', $idp);
            $this->assertArrayHasKey('field_mapping_config', $idp);
        }
    }

    /**
     * @return void
     */
    public function test_query_requires_site_config_capability() {
        idp::create(['status' => true, 'label' => 'test', 'idp_user_id_field' => '', 'totara_user_id_field' => 'username'], []);

        $this->expectException(required_capability_exception::class);
        $this->resolve_graphql_query(self::QUERY);
    }
}
