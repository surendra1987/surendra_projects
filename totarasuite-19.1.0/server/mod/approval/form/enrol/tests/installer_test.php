<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package approvalform_enrol
 */

use approvalform_enrol\installer;
use core_phpunit\testcase;
use mod_approval\entity\form\form;
use mod_approval\entity\workflow\workflow_type;
use mod_approval\model\status;
use mod_approval\model\workflow\workflow_stage;
use totara_core\entity\relationship;
use totara_tenant\testing\generator as tenant_generator;

/**
 * @coversDefaultClass \approvalform_enrol\installer
 *
 * @group approval_workflow
 */
class approvalform_enrol_installer_test extends testcase {

    /**
     * @covers ::install_demo_workflow
     * @covers ::configure_publishable_workflow
     */
    public function test_install_enrolment_workflow() {
        global $CFG;
        $this->setAdminUser();

        // Install a testing workflow
        $installer = new installer();
        $workflow = $installer->install_demo_workflow('Testing');

        // Check workflow_type
        $workflow_type = new workflow_type($workflow->workflow_type_id);
        $this->assertEquals('Testing', $workflow_type->name);

        // Check form
        $form = new form($workflow->form_id);
        $this->assertEquals('enrol', $form->plugin_name);
        $this->assertEquals('Enrolment approval form', $form->title);

        // Check form_version
        $this->assertCount(1, $form->versions);
        $form_version = $form->versions->first();
        $json_schema = file_get_contents($CFG->dirroot . '/mod/approval/form/enrol/form.json');
        $this->assertEquals($json_schema, $form_version->json_schema);

        // Check workflow
        $this->assertEquals('Enrolment workflow', $workflow->name);

        // Check workflow_version
        $this->assertCount(1, $workflow->versions);
        $workflow_version = $workflow->versions->first();

        // Check stages
        $this->assertCount(4, $workflow_version->stages);
        $stages = installer::get_default_stages();
        $ix = 0;
        foreach ($workflow_version->stages as $stage) {
            $ix++;
            $expected = array_shift($stages);
            $this->assertEquals($expected['name'], $stage->name);
            $this->assertEquals($expected['type'], $stage->type::get_enum());
            $this->assertEquals($ix, $stage->ordinal_number);
            ${'stage' . $ix} = $stage;
        }

        // Check stage1 formviews

        // Check stage2 approval levels
        $this->assertCount(1, $stage2->approval_levels);
        $stage2_1 = $stage2->approval_levels->first();

        // Check default assignment & approvers
        $this->assertCount(1, $workflow->assignments);
        $default_assignment = $workflow->assignments->first();
        $this->assertTrue($default_assignment->is_default);
        $this->assertEquals('System', $default_assignment->name);
        $this->assertCount(1, $default_assignment->approvers);

        $manager = relationship::repository()->where('idnumber', '=', 'manager')->one();
        $manager_approver = $default_assignment->approvers->first();
        $this->assertEquals($stage2_1->id, $manager_approver->workflow_stage_approval_level_id);
        $this->assertEquals($manager->id, $manager_approver->identifier);

        // Is a system workflow.
        $this->assertNull($workflow->get_context()->tenantid);
    }

    /**
     * @covers ::install_demo_workflow
     */
    public function test_install_enrolment_workflow_twice() {
        $this->setAdminUser();

        // Install a testing workflow
        $installer = new installer();
        $workflow1 = $installer->install_demo_workflow('Testing');
        $workflow2 = $installer->install_demo_workflow('Testing', true);

        // Same type, same form, different workflows
        $this->assertEquals($workflow1->workflow_type_id, $workflow2->workflow_type_id);
        $this->assertEquals($workflow1->form_id, $workflow2->form_id);
        $this->assertNotEquals($workflow1->id, $workflow2->id);

        // Different workflow versions, same form version
        $this->assertNotEquals($workflow1->versions->first()->id, $workflow2->versions->first()->id);
        $this->assertEquals($workflow1->versions->first()->form_version_id, $workflow2->versions->first()->form_version_id);

        // Default assignment should be the same
        $this->assertNotEquals($workflow1->assignments->first()->id, $workflow2->assignments->first()->id);
        $this->assertEquals($workflow1->assignments->first()->name, $workflow2->assignments->first()->name);
        $this->assertEquals(
            $workflow1->assignments->first()->assignment_identifier,
            $workflow2->assignments->first()->assignment_identifier
        );

        // Workflow 1 is not published, workflow 2 is.
        $this->assertEquals($workflow1->latest_version->status, status::DRAFT);
        $this->assertEquals($workflow2->latest_version->status, status::ACTIVE);
    }

    /**
     * @covers ::install_demo_workflow
     */
    public function test_install_tenant_enrolment_workflow() {
        $tenant_generator = tenant_generator::instance();
        $tenant_generator->enable_tenants();
        $tenant = $tenant_generator->create_tenant();
        $tenant_entity = new \core\entity\tenant($tenant->id);

        $this->setAdminUser();

        // Install a testing workflow
        $installer = new installer();
        $workflow = $installer->install_demo_workflow('Testing', false, $tenant_entity);

        $this->assertEquals($tenant->id, $workflow->get_context()->tenantid);
    }
}