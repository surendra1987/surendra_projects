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

use auth_ssosaml\entity\idp;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\webapi\resolver\mutation\create_idp
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_resolver_mutation_create_idp_test extends base_saml_testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'auth_ssosaml_create_idp';

    /**
     * Assert that creating a new IdP instance does not require files or anything else.
     *
     * @return void
     */
    public function test_executing_mutation(): void {
        global $DB;

        // Clear out existing idps (there shouldn't be leakage in any case)
        $DB->delete_records(idp::TABLE);
        $count = $DB->count_records(idp::TABLE);
        $this->assertSame(0, $count);

        $this->setAdminUser();
        $result = $this->execute_graphql_operation(self::MUTATION, []);
        $id = $result->toArray()['data']['idp']['id'];

        // Check we have one record now
        $count = $DB->count_records(idp::TABLE);
        $this->assertSame(1, $count);

        // Check it's what we expect
        $DB->record_exists(idp::TABLE, ['id' => $id, 'status' => 0]);
    }

    /**
     * @return void
     */
    public function test_query_requires_site_config_capability(): void {
        $this->expectException(required_capability_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION);
    }
}