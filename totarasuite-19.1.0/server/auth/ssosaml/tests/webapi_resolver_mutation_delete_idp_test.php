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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\model\idp;
use core\orm\query\exceptions\record_not_found_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\webapi\resolver\mutation\delete_idp
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_resolver_mutation_delete_idp_test extends base_saml_testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'auth_ssosaml_delete_idp';

    /**
     * @return void
     */
    public function test_executing_mutation(): void {
        $this->setAdminUser();
        $idp = idp::create(['status' => false], []);
        $args = ['input' => ['id' => $idp->id]];
        $result = $this->execute_graphql_operation(self::MUTATION, $args);
        $data = $result->toArray()['data'];
        $this->assertTrue($data['result']);

        $this->expectException(record_not_found_exception::class);
        $this->expectExceptionMessage('Can not find data record in database');
        idp::load_by_id($idp->id);
    }

    /**
     * @return void
     */
    public function test_query_requires_site_config_capability(): void {
        $this->expectException(required_capability_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION);
    }
}