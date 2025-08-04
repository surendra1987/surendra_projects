<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_cohort
 */

use core_phpunit\testcase;
use totara_cohort\cohort_interactor;

class totara_cohort_cohort_interactor_test extends testcase {
    /**
     * @return void
     */
    public function test_for_user(): void {
        self::setAdminUser();

        $interactor = cohort_interactor::for_user();
        self::assertTrue($interactor->can_view_cohort());
        self::assertTrue($interactor->can_manage_cohort());
        self::assertTrue($interactor->can_assign_cohort());

        $user = self::getDataGenerator()->create_user();

        self::setUser($user);
        $interactor = cohort_interactor::for_user();
        self::assertFalse($interactor->can_view_cohort());
        self::assertFalse($interactor->can_manage_cohort());
        self::assertFalse($interactor->can_assign_cohort());
    }

    /**
     * @return void
     */
    public function test_for_context(): void {
        $gen = self::getDataGenerator();
        /** @var \totara_cohort\testing\generator $generator */
        $generator = $gen->get_plugin_generator('totara_cohort');
        $cohort = $generator->create_cohort();

        self::setAdminUser();
        $cohort = new \core\entity\cohort($cohort->id);
        $interactor = cohort_interactor::for_cohort($cohort);

        self::assertTrue($interactor->can_view_cohort());
        self::assertTrue($interactor->can_manage_cohort());
        self::assertTrue($interactor->can_assign_cohort());

        $system_user = $gen->create_user();
        self::setUser($system_user);
        $interactor = cohort_interactor::for_cohort($cohort);

        self::assertFalse($interactor->can_view_cohort());
        self::assertFalse($interactor->can_manage_cohort());
        self::assertFalse($interactor->can_assign_cohort());
    }

    /**
     * @return void
     */
    public function test_for_tenancy(): void {
        $gen = self::getDataGenerator();
        /** @var \totara_cohort\testing\generator $generator */
        $generator = $gen->get_plugin_generator('totara_cohort');

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        $user1 = $gen->create_user(['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]);
        $user2 = $gen->create_user(['tenantid' => $tenant2->id]);

        $tenant1_category_context = context_coursecat::instance($tenant1->categoryid);
        $tenant2_category_context = context_coursecat::instance($tenant2->categoryid);

        $cohort1 = $generator->create_cohort(['contextid' => $tenant1_category_context->id]);
        $cohort2 = $generator->create_cohort(['contextid' => $tenant2_category_context->id]);


        self::setAdminUser();
        $interactor = cohort_interactor::for_cohort(new \core\entity\cohort($cohort1->id));
        self::assertTrue($interactor->can_view_cohort());
        self::assertTrue($interactor->can_manage_cohort());
        self::assertTrue($interactor->can_assign_cohort());


        self::setUser($user1);
        $interactor = cohort_interactor::for_cohort(new \core\entity\cohort($cohort1->id));
        self::assertTrue($interactor->can_view_cohort());
        self::assertTrue($interactor->can_manage_cohort());
        self::assertTrue($interactor->can_assign_cohort());

        $interactor = cohort_interactor::for_cohort(new \core\entity\cohort($cohort1->id), new \core\entity\user($user2->id));
        self::assertFalse($interactor->can_view_cohort());
        self::assertFalse($interactor->can_manage_cohort());
        self::assertFalse($interactor->can_assign_cohort());

    }
}