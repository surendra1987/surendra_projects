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
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use totara_reportbuilder\rb\access\tenant;

/**
 * @coversDefaultClass \totara_reportbuilder\rb\access\tenant
 *
 * @group totara_reportbuilder
 */
class totara_reportbuilder_rb_access_tenant_test extends testcase {
    use totara_reportbuilder\phpunit\report_testing;

    /**
     * @return void
     */
    public function test_form_process(): void {
        $this->setAdminUser();
        $rid = $this->create_report('user', 'User report');

        $access_data = new \stdClass();
        $access_data->accessenabled = 1;
        $access_data->role_enable = 1;
        $access_data->role_activeroles = [1 => 1, 2 => 0, 3 => 1];

        $this->assertTrue((new tenant())->form_process($rid, $access_data));
        $data = reportbuilder::get_setting($rid, 'tenant_access', 'activeroles');

        $this->assertSame("1|3", $data);
    }

    /**
     * @return void
     */
    public function test_get_accessible_reports(): void {
        $this->setAdminUser();
        /** @var $tenant_generator \totara_tenant\testing\generator */
        $tenant_generator = \totara_tenant\testing\generator::instance();
        $tenant_generator->enable_tenants();
        $tenant1 = $tenant_generator->create_tenant();
        // tenant member and TDM
        $user1 = $this->getDataGenerator()->create_user();
        $tenant_generator->migrate_user_to_tenant($user1->id, $tenant1->id);
        // system user
        $user2 = $this->getDataGenerator()->create_user();
        // tenant member
        $user3 = $this->getDataGenerator()->create_user();
        $tenant_generator->migrate_user_to_tenant($user3->id, $tenant1->id);

        $db = builder::get_db();
        $tenant_category_context = \context_coursecat::instance($tenant1->categoryid);
        $tdm_role = $db->get_record('role', ['shortname' => 'tenantdomainmanager']);

        role_assign($tdm_role->id, $user1->id, $tenant_category_context);

        assign_capability('totara/reportbuilder:managereports', CAP_ALLOW, $tdm_role->id, $tenant_category_context);
        $rid = $this->create_report('user', 'User report', false, 0, $tenant1->id);

        $report_access = new tenant();

        // Reportbuilder created just for TDM
        $this->assertEquals([$rid], $report_access->get_accessible_reports($user1->id));
        $this->assertEmpty($report_access->get_accessible_reports($user2->id));
        $this->assertEmpty($report_access->get_accessible_reports($user3->id));

        // Enable it for tenantusersonly
        $db->update_record('report_builder', ['id' => $rid, 'accessmode' => REPORT_BUILDER_ACCESS_MODE_NONE]);
        $this->assertEquals([$rid], $report_access->get_accessible_reports($user1->id));
        $this->assertEmpty($report_access->get_accessible_reports($user2->id));
        $this->assertEquals([$rid], $report_access->get_accessible_reports($user3->id));
    }

    public function test_get_tenant_roles() {
        $tenant_access = new tenant();
        $reflection = new ReflectionMethod($tenant_access, 'get_tenant_roles');
        $reflection->setAccessible(true);
        $tenant_roles = $reflection->invoke($tenant_access);

        $roles = [];
        foreach ($tenant_roles as $tenant_role) {
            $roles[$tenant_role->id][] = $tenant_role;
        }

        // Assert roles are not duplicated.
        foreach ($roles as $role) {
            $this->assertCount(1, $role);
        }
    }
}