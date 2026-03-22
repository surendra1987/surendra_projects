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
 * @package totara_reportbuilder
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \totara_reportbuilder\webapi\resolver\query\creation_sources
 * @group totara_reportbuilder
 */
class totara_reportbuilder_webapi_resolver_query_creation_sources_test extends testcase {

    use webapi_phpunit_helper;

    private const QUERY = 'totara_reportbuilder_creation_sources';

    /**
     * Assert we can query for tenant reports
     *
     * @return void
     */
    public function test_query_tenant_has_sources(): void {
        global $DB;
        /** @var \totara_tenant\testing\generator $tenantgenerator */
        $tenantgenerator = \totara_tenant\testing\generator::instance();
        $tenantgenerator->enable_tenants();
        $this->setAdminUser();

        $tdm_role = $DB->get_record('role', ['shortname' => 'tenantdomainmanager']);

        $tenant1 = $tenantgenerator->create_tenant();
        $user1 = $this->getDataGenerator()->create_user(['tenantid' => $tenant1->id]);

        $tenant = \core\record\tenant::fetch($tenant1->id);
        $tenant_category_context = \context_coursecat::instance($tenant->categoryid);

        role_assign($tdm_role->id, $user1->id, $tenant_category_context);

        assign_capability('totara/reportbuilder:managereports', CAP_ALLOW, $tdm_role->id, $tenant_category_context);

        $this->setUser($user1);

        $args = ['tenantid' => (int) $tenant1->id];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $data = $this->get_webapi_operation_data($result);

        $this->assertEmpty($data['templates']);
        $this->assertNotEmpty($data['sources']);
    }

    /**
     * Assert we can query for admin reports
     *
     * @return void
     */
    public function test_query_admin_can_access_template_and_sources(): void {
        $this->setAdminUser();
        $result = $this->parsed_graphql_operation(self::QUERY);
        $data = $this->get_webapi_operation_data($result);
        $this->assertNotEmpty($data['templates']);
        $this->assertNotEmpty($data['sources']);
    }
}