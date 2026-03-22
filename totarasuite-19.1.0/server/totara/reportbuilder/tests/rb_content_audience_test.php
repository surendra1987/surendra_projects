<?php
/*
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
 */

use core_phpunit\testcase;
use totara_reportbuilder\rb\content\audience;

/**
 * @group totara_reportbuilder
 */
class totara_reportbuilder_rb_content_audience_test extends testcase {

    /**
     * Test the get_cohort_select_options returns only cohorts
     * within the provided context or all cohorts when no context is provided.
     * @return void
     */
    public function test_get_cohort_select_options(): void {
        $this->setAdminUser();

        $generator = self::getDataGenerator();
        $system_cohort_1 = $generator->create_cohort();
        $system_cohort_2 = $generator->create_cohort();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $tenant_1 = $tenant_generator->create_tenant('t1');
        $tenant_audiences = [];

        for ($i = 0; $i < 2; $i++) {
            $tenant_audiences[] = $generator->create_cohort([
                'contextid' => $tenant_1->category_context->id,
            ])->id;
        }

        // getting audiences with the system context.
        $cohort_options = audience::get_cohort_select_options();
        foreach ($tenant_audiences as $tenant_audience_id) {
            $this->assertArrayHasKey($tenant_audience_id, $cohort_options);
        }
        $this->assertArrayHasKey($system_cohort_1->id, $cohort_options);
        $this->assertArrayHasKey($system_cohort_2->id, $cohort_options);
        $this->assertArrayHasKey($tenant_1->cohortid, $cohort_options);

        // Pass the tenant category context
        $tenant_cohort_options = audience::get_cohort_select_options($tenant_1->category_context->id);
        foreach ($tenant_audiences as $tenant_audience_id) {
            $this->assertArrayHasKey($tenant_audience_id, $tenant_cohort_options);
        }

        // Tenant audience included
        $this->assertArrayHasKey($tenant_1->cohortid, $tenant_cohort_options);

        // System audiences not included
        $this->assertArrayNotHasKey($system_cohort_1->id, $tenant_cohort_options);
        $this->assertArrayNotHasKey($system_cohort_2->id, $tenant_cohort_options);
    }
}
