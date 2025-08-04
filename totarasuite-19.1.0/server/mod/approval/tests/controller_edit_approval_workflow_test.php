<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totaralearning.com>
 * @package mod_approval
 */

use container_approval\approval as approval_container;
use core\entity\user as user_entity;
use core\entity\tenant as tenant_entity;
use core\orm\query\builder;
use core\record\tenant;
use core_phpunit\testcase;
use mod_approval\controllers\workflow\edit;
use mod_approval\model\assignment\assignment_type\cohort as cohort_type;
use mod_approval\model\assignment\assignment_type\organisation as organisation_type;
use mod_approval\model\assignment\assignment_type\position as position_type;
use mod_approval\model\workflow\workflow;
use mod_approval\testing\approval_workflow_test_setup;


/**
 * @group approval_workflow
 * @coversDefaultClass mod_approval\controllers\workflow\edit
 */
class mod_approval_controller_edit_approval_workflow_test extends testcase {

    use approval_workflow_test_setup;

    /**
     * Gets the approval workflow generator instance
     *
     * @return \mod_approval\testing\generator
     */
    protected function generator(): \mod_approval\testing\generator {
        return \mod_approval\testing\generator::instance();
    }

    public function test_context(): void {
        $this->setAdminUser();
        $workflow = $this->set_application();
        $workflow_model = workflow::load_by_entity($workflow);
        $_POST['workflow_id'] = $workflow->id;
        self::assertSame($workflow_model->get_context(), (new edit())->get_context());
    }

    public function test_edit_application(): void {
        global $CFG;
        $workflow = $this->set_application();
        $this->setAdminUser();
        $_POST['workflow_id'] = $workflow->id;

        $edit = (new edit())->action();
        $data = $edit->get_data();

        $this->assertArrayHasKey('back-url', $data);
        $this->assertEquals($CFG->wwwroot . '/mod/approval/workflow/index.php', $data['back-url']);

        $this->assertArrayHasKey('stage-types', $data);
        foreach ($data['stage-types'] as $stage_type) {
            $this->assertArrayHasKey('label', $stage_type);
            $this->assertArrayHasKey('enum', $stage_type);
        }
    }

    public function test_invalid_param(): void {
        $workflow = $this->set_application();
        $this->setAdminUser();
        $_POST['workflow_id'] = 9999999;

        $this->expectException(moodle_exception::class);
        (new edit())->process();
    }

    private function set_application() {
        $this->setAdminUser();
        list($workflow, $framework, $assignment) = $this->create_workflow_and_assignment();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $application = $this->create_application($workflow, $assignment, user_entity::logged_in());

        return $workflow;
    }

    public function test_assigment_types(): void {
        $this->setAdminUser();
        list($workflow, $framework, $assignment) = $this->create_workflow_and_assignment();
        $_POST['workflow_id'] = $workflow->id;
        $user = user_entity::logged_in();

        $source = new edit();
        $context = approval_container::get_default_category_context();
        $reflection = new ReflectionClass($source);
        $method =  $reflection->getMethod('get_assignment_types');
        $method->setAccessible(true);
        $output = $method->invoke($source, $context, $user);

        $this->assertEquals(3, count($output));

        foreach ($output as $item) {
            $this->assertContains($item['enum'], [cohort_type::get_enum(), organisation_type::get_enum(), position_type::get_enum()]);
        }
    }

    public function test_assigment_types_with_tenant_domain_manager(): void {
        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $this->getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();
        list($workflow, $framework, $assignment) = $this->create_workflow_and_assignment();
        $_POST['workflow_id'] = $workflow->id;


        $tenant_user1 =  $this->getDataGenerator()->create_user(
            ['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]
        );

        $tenant = tenant::fetch($tenant1->id);
        $cat_id = approval_container::get_category_id_from_tenant_category($tenant->categoryid);
        $context = context_coursecat::instance($cat_id);

        $this->setUser($tenant_user1);

        $user = user_entity::logged_in();
        $source = new edit();

        $reflection = new ReflectionClass($source);
        $method =  $reflection->getMethod('get_assignment_types');
        $method->setAccessible(true);
        $output = $method->invoke($source, $context, $user);

        //By default tenant domain manager only have access to COHORT
        $this->assertEquals(1, count($output));
        foreach ($output as $item) {
            $this->assertContains($item['enum'], [cohort_type::get_enum()]);
        }

        $tenant = new tenant_entity($tenant->id);
        $context = \context_coursecat::instance($tenant->categoryid);

        $domain_manager_role = builder::table('role')->where('shortname', 'tenantdomainmanager')->one();

        // Add view organisation capability to tenant domain manager
        assign_capability('totara/hierarchy:vieworganisationframeworks', CAP_ALLOW, $domain_manager_role->id, $context);
        role_assign($domain_manager_role->id, $user->id, $context);

        $reflection = new ReflectionClass($source);
        $method =  $reflection->getMethod('get_assignment_types');
        $method->setAccessible(true);
        $output = $method->invoke($source, $context, $user);

        // After assigning new capability to tenant domain manager
        $this->assertEquals(2, count($output));
        foreach ($output as $item) {
            $this->assertContains($item['enum'], [cohort_type::get_enum(), organisation_type::get_enum()]);
        }
    }
}