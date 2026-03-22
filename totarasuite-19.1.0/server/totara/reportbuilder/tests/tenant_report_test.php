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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package totara_reportbuilder
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use totara_reportbuilder\phpunit\report_testing;
use totara_reportbuilder\report_helper;

/**
 * Covers query execution paths for tenant reports.
 *
 * @group totara_reportbuilder
 */
class totara_reportbuilder_tenant_report_test extends testcase {
    use report_testing;

    public function test_generating_tenant_report(): void {
        global $CFG;
        $this->setAdminUser();
        set_config('allowtotalcount', 1, 'totara_reportbuilder');

        $generator = $this->getDataGenerator();
        $participant_tdm = $generator->create_user();
        $member_tdm = $this->getDataGenerator()->create_user();

        /** @var \totara_tenant\testing\generator $tenantgenerator */
        $tenantgenerator = $generator->get_plugin_generator('totara_tenant');
        $tenantgenerator->enable_tenants();
        $tenant1 = $tenantgenerator->create_tenant();
        $tenantgenerator->migrate_user_to_tenant($member_tdm->id, $tenant1->id);
        $tenantgenerator->set_user_participation($participant_tdm->id, [$tenant1->id]);

        // make users tenant domain managers
        $domain_manager_role = builder::table('role')->where('shortname', 'tenantdomainmanager')->one(true);
        $generator->role_assign($domain_manager_role->id, $participant_tdm->id, $tenant1->category_context);
        $generator->role_assign($domain_manager_role->id, $member_tdm->id, $tenant1->category_context);

        $report_sources = report_helper::get_sources();
        $tdms = [$member_tdm->id, $participant_tdm->id];

        foreach ($report_sources as $index => $report_source) {
            $src = \reportbuilder::get_source_object($report_source);
            if (!$src->is_source_tenant_compatible()) {
                continue;
            }

            foreach ($tdms as $tdm) {
                $this->setUser($tdm);
                $report_id = $this->create_report($report_source, "test report for $index : $report_source", true, 0, $tenant1->id);

                $report = reportbuilder::create($report_id);

                set_config('tenantsisolated', 0);
                $this->check_query_execution($report);

                // test execution with tenant isolation.
                set_config('tenantsisolated', 1);
                $CFG->tenantsisolated = true;
                $this->check_query_execution($report);
            }

        }
    }

    private function check_query_execution(reportbuilder $report): void {
        // test the execution of count.
        $count = $report->get_full_count();
        $this->assertIsInt($count);

        // test the execution of fetching data.
        $getdata = new ReflectionMethod(reportbuilder::class, 'get_data');
        $getdata->setAccessible(true);
        $recordset = $getdata->invoke($report);
        $this->assertInstanceOf(moodle_recordset::class, $recordset);
    }
}
