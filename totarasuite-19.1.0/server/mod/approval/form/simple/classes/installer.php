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
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package approvalform_simple
 */

namespace approvalform_simple;

use container_approval\approval as workflow_container;
use core\entity\cohort;
use mod_approval\form\installer as installer_base;
use mod_approval\entity\workflow\workflow;
use mod_approval\exception\model_exception;
use mod_approval\exception\validation_exception;
use mod_approval\model\assignment\approver_type\relationship as relationship_approver_type;
use mod_approval\model\assignment\approver_type\user as user_approver_type;
use mod_approval\model\assignment\assignment_type;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_feature\formviews;
use mod_approval\model\workflow\workflow as workflow_model;
use mod_approval\model\workflow\workflow_version as workflow_version_model;
use mod_approval\model\workflow\workflow_stage as workflow_stage_model;
use mod_approval\testing\assignment_approver_generator_object;
use mod_approval\testing\assignment_generator_object;
use mod_approval\testing\workflow_generator_object;
use totara_core\advanced_feature;
use totara_core\entity\relationship;

/**
 * Installer class, for installing default Simple Workflow, demo assignments, and demo applications.
 */
class installer extends installer_base {

    /**
     * Creates a demo simple workflow with a default assignment.
     *
     * @param cohort $cohort Cohort to use for default assignment
     * @param string $type_name Workflow_type name
     * @return workflow
     * @throws \JsonException
     * @throws \coding_exception
     * @throws model_exception
     * @throws validation_exception
     */
    public function install_demo_workflow(cohort $cohort, string $type_name = 'Simple'): workflow {
        global $CFG;

        advanced_feature::enable('approval_workflows');

        $generator = $this->generator();

        // Create a workflow_type
        $workflow_type = $generator->create_workflow_type($type_name);

        // Create a form and form_version
        $form_version = $generator->create_form_and_version(
            'simple',
            'Simple Request Form',
            $CFG->dirroot . '/mod/approval/form/simple/form.json'
        );
        $form = $form_version->form;
        // Do this again in case the form_version already existed.
        $form_version->json_schema = file_get_contents($CFG->dirroot . '/mod/approval/form/simple/form.json');
        $schema = json_decode($form_version->json_schema);
        if (empty($schema->version)) {
            throw new validation_exception();
        }
        $form_version->version = $schema->version;
        $form_version->status = status::ACTIVE;
        $form_version->save();

        // Create a workflow and workflow_version
        $workflow_go = new workflow_generator_object($workflow_type->id, $form->id, $form_version->id);
        $workflow_go->name = "Default Simple Workflow";
        $workflow_go->status = status::DRAFT;
        if ($this->tenant_mode) {
            // Currently the generator wants to create course containers in the system category,
            //   so create one here first.
            $workflow_go->name = "Tenant Simple Workflow";
            $container_data = new \stdClass();
            $container_data->fullname = $workflow_go->name . " Generated Container";
            $container_data->category = workflow_container::get_category_id_from_tenant_category($this->tenant->categoryid);
            $container = workflow_container::create($container_data);
            $workflow_go->course_id = $container->id;
        }
        $workflow_version = $generator->create_workflow_and_version($workflow_go);
        $workflow = $workflow_version->workflow;

        // Create Stages
        $stages = installer::get_default_stages();
        foreach ($stages as $stage => $stage_details) {
            ${$stage} = $generator->create_workflow_stage($workflow_version->id, $stage_details['name'], $stage_details['type']);
        }

        // Configure formviews & approval levels
        // Request

        $stage1 = workflow_stage_model::load_by_id($stage1->id);
        $stage1->configure_formview([
            [
                'field_key' => 'request',
                'visibility' => formviews::EDITABLE_AND_REQUIRED,
            ],
            [
                'field_key' => 'notes',
                'visibility' => formviews::EDITABLE,
            ],
            [
                'field_key' => 'complete',
                'visibility' => formviews::HIDDEN,
            ],
        ]);

        // Approvals
        $stage2 = workflow_stage_model::load_by_id($stage2->id);
        $stage2->configure_formview([
            [
                'field_key' => 'request',
                'visibility' => formviews::EDITABLE_AND_REQUIRED,
            ],
            [
                'field_key' => 'notes',
                'visibility' => formviews::EDITABLE,
            ],
            [
                'field_key' => 'complete',
                'visibility' => formviews::HIDDEN,
            ],
        ]);

        // Create 2 approval levels
        $level2_1 = $stage2->approval_levels->first();
        $level2_2 = $generator->create_approval_level($stage2->id, 'Level 2', 2);

        // Followup
        $stage3 = workflow_stage_model::load_by_id($stage3->id);
        $stage3->configure_formview([
            [
                'field_key' => 'request',
                'visibility' => formviews::READ_ONLY,
            ],
            [
                'field_key' => 'complete',
                'visibility' => formviews::EDITABLE_AND_REQUIRED,
            ],
            [
                'field_key' => 'notes',
                'visibility' => formviews::EDITABLE,
            ],
        ]);

        // Verify followup
        $stage4 = workflow_stage_model::load_by_id($stage4->id);
        $stage4->configure_formview([
            [
                'field_key' => 'request',
                'visibility' => formviews::READ_ONLY,
            ],
            [
                'field_key' => 'complete',
                'visibility' => formviews::EDITABLE_AND_REQUIRED,
            ],
            [
                'field_key' => 'notes',
                'visibility' => formviews::EDITABLE,
            ],
        ]);

        // Delete default approval level
        $stage4->approval_levels->first()->delete();

        // Create 1 approval level
        $level4_1 = $generator->create_approval_level($stage4->id, 'Verification', 1);

        // Create default assignment
        $assignment_go = new assignment_generator_object($workflow->course_id, assignment_type\cohort::get_code(), $cohort->id);
        $assignment_go->id_number = '001';
        $assignment_go->is_default = true;
        $agency = $generator->create_assignment($assignment_go);

        // Create assignment approver manager for stage 1 level 1
        $manager = relationship::repository()->where('idnumber', '=', 'manager')->one();
        $approver_go = new assignment_approver_generator_object(
            $agency->id,
            $level2_1->id,
            relationship_approver_type::TYPE_IDENTIFIER,
            $manager->id
        );
        $generator->create_assignment_approver($approver_go);

        // Create approver for $level2_2 and also $level4_1
        $data = $this->get_user_data($this->usernames);
        $approver1_2 = $this->load_or_create_user($data);
        $approver_go = new assignment_approver_generator_object(
            $agency->id,
            $level2_2->id,
            user_approver_type::TYPE_IDENTIFIER,
            $approver1_2->id
        );
        $assignment_approver1_2 = $generator->create_assignment_approver($approver_go);
        $approver_go = new assignment_approver_generator_object(
            $agency->id,
            $level4_1->id,
            user_approver_type::TYPE_IDENTIFIER,
            $approver1_2->id
        );
        $assignment_approver3_1 = $generator->create_assignment_approver($approver_go);

        workflow_model::load_by_entity($workflow)->publish(workflow_version_model::load_by_entity($workflow_version));

        return $workflow;
    }
}