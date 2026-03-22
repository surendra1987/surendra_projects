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

/**
 * @coversDefaultClass \totara_reportbuilder\output\create_report
 * @group totara_reportbuilder
 */
class totara_reportbuilder_output_create_report_test extends testcase {

    /**
     * Assert report builder filter for admin
     *
     * @return void
     */
    public function test_report_builder_filter_admin(): void {
        $this->setAdminUser();
        $output = \totara_reportbuilder\output\create_report::create(0);
        $data = $output->get_template_data();

        $expected_result = [];
        foreach ($data['filter_data']['selectors'] as $item) {
            $expected_result[] = $item->template_data['key'];
        }

        $this->assertEquals(0, $data['tenantid']);
        $this->assertSame("Filters", $data['filter_data']['title']);
        $this->assertCount(3, $data['filter_data']['selectors']);
        $this->assertSame(['search', 'template', 'source'], $expected_result);
    }

    /**
     * Assert report builder filter for tenant
     *
     * @return void
     */
    public function test_report_builder_filter_tenant(): void {
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

        $this->setUser($user1);

        $output = \totara_reportbuilder\output\create_report::create($tenant1->id);
        $data = $output->get_template_data();

        $expected_result = [];
        foreach ($data['filter_data']['selectors'] as $item) {
            $expected_result[] = $item->template_data['key'];
        }

        $this->assertEquals($tenant1->id, $data['tenantid']);
        $this->assertSame("Filters", $data['filter_data']['title']);
        $this->assertCount(2, $data['filter_data']['selectors']);
        $this->assertSame(['search', 'source'], $expected_result);
    }

}