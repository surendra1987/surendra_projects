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

namespace approvalform_enrol;

use container_approval\approval as workflow_container;
use core\entity\tenant;
use core\orm\entity\traits\json_trait;
use mod_approval\entity\workflow\workflow_stage_interaction;
use mod_approval\entity\workflow\workflow_stage_interaction_transition;
use mod_approval\exception\validation_exception;
use mod_approval\model\application\action\withdraw_before_submission;
use mod_approval\model\assignment\approver_type\relationship as relationship_approver_type;
use mod_approval\model\assignment\assignment_type;
use mod_approval\model\status;
use mod_approval\model\workflow\interaction\transition\stage;
use mod_approval\model\workflow\stage_feature\formviews;
use mod_approval\model\workflow\stage_type\approvals;
use mod_approval\model\workflow\stage_type\finished;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\workflow;
use mod_approval\model\workflow\workflow_stage as workflow_stage_model;
use mod_approval\model\workflow\workflow_stage_interaction_transition as workflow_stage_interaction_transition_model;
use mod_approval\model\workflow\workflow_version;
use mod_approval\testing\assignment_approver_generator_object;
use mod_approval\testing\assignment_generator_object;
use mod_approval\testing\generator as mod_approval_generator;
use mod_approval\testing\workflow_generator_object;
use stdClass;
use totara_core\advanced_feature;
use totara_core\entity\relationship;

/**
 * Installer class, for installing default Enrolment Workflow. Note that this does not extend
 * the installer base class, because that assumes an audience-assigned workflow.
 */
class installer {

    use json_trait;

    /**
     * Gets the generator instance
     *
     * @return mod_approval_generator
     */
    protected static function generator(): mod_approval_generator {
        return mod_approval_generator::instance();
    }

    /**
     * Get stages for default workflow
     *
     * @return string[]
     */
    public static function get_default_stages(): array {
        return [
            'stage1' => [
                'name' => 'Request',
                'type' => form_submission::get_enum(),
            ],
            'stage2' => [
                'name' => 'Approval',
                'type' => approvals::get_enum(),
            ],
            'stage3' => [
                'name' => enrol::APPROVED_END_STAGE,
                'type' => finished::get_enum(),
            ],
            'stage4' => [
                'name' => enrol::ARCHIVED_END_STAGE,
                'type' => finished::get_enum(),
            ],
        ];
    }

    /**
     * Creates a standard enrolment workflow.
     *
     * @param string $type_name Workflow_type name
     * @param bool $publish Whether to publish the workflow; false creates a draft.
     * @param tenant $tenant
     * @return workflow
     */
    public function install_demo_workflow(string $type_name = 'Enrolment', bool $publish = false, tenant $tenant = null): workflow {
        global $CFG;

        advanced_feature::enable('approval_workflows');

        $generator = $this->generator();

        // Create a workflow_type
        $workflow_type = $generator->create_workflow_type($type_name);

        // Create a form and form_version
        $form_version = $generator->create_form_and_version(
            'enrol',
            'Enrolment approval form',
            $CFG->dirroot . '/mod/approval/form/enrol/form.json'
        );
        $form = $form_version->form;
        // Do this again in case the form_version already existed.
        $form_version->json_schema = file_get_contents($CFG->dirroot . '/mod/approval/form/enrol/form.json');
        $schema = static::json_decode($form_version->json_schema);
        if (empty($schema->version)) {
            throw new validation_exception();
        }
        $form_version->version = $schema->version;
        $form_version->status = status::ACTIVE;
        $form_version->save();

        $workflow_go = new workflow_generator_object($workflow_type->id, $form->id, $form_version->id);
        $workflow_go->name = "Enrolment workflow";
        $workflow_go->status = status::DRAFT;
        // Create a workflow and workflow_version
        if ($tenant) {
            // Tenant is specified, we need to manually create the course container for the workflow.
            // Create a container
            $container_data = new stdClass();
            $container_data->fullname = $workflow_go->name . " Generated Container";
            $container_data->category = workflow_container::get_category_id_from_tenant_category($tenant->categoryid);
            $container = workflow_container::create($container_data);
            $workflow_go->course_id = $container->id;
            $workflow_go->name = $tenant->name . " enrolment workflow";
        }
        $workflow_version_entity = $generator->create_workflow_and_version($workflow_go);
        $workflow_version = workflow_version::load_by_entity($workflow_version_entity);
        $workflow = $workflow_version->workflow;

        // Return here.
        static::configure_publishable_workflow($workflow_version);

        // First approval level is already created
        $stage2 = $workflow_version_entity->stages()->where('name', '=', 'Approval')->one(true);
        $level2_1 = $stage2->approval_levels->first();

        // Create default assignment
        if ($tenant) {
            $context_id = \context_tenant::instance($tenant->id)->id;
            $id_number = 'context_tenant';
        } else {
            $context_id = \context_system::instance()->id;
            $id_number = 'context_system';
        }
        $assignment_go = new assignment_generator_object($workflow->course_id, assignment_type\context::get_code(), $context_id);
        $assignment_go->id_number = $id_number;
        $assignment_go->is_default = true;
        $default_assignment = $generator->create_assignment($assignment_go);

        // Create assignment approver/manager for that first approval stage.
        $manager = relationship::repository()->where('idnumber', '=', 'manager')->one();
        $approver_go = new assignment_approver_generator_object(
            $default_assignment->id,
            $level2_1->id,
            relationship_approver_type::TYPE_IDENTIFIER,
            $manager->id
        );
        $generator->create_assignment_approver($approver_go);

        if ($publish) {
            $workflow->publish($workflow_version);
        }

        return $workflow;
    }

    /**
     * Configure an enrolment approval workflow_version that passes approvalform_core_enrol_base::verify_workflow_structure()
     *
     * @param workflow_version $workflow_version
     * @return void
     * @throws \coding_exception
     */
    public static function configure_publishable_workflow(workflow_version $workflow_version) {
        $generator = static::generator();

        // Create Stages
        $stages = installer::get_default_stages();
        foreach ($stages as $stage => $stage_details) {
            ${$stage} = $generator->create_workflow_stage($workflow_version->id, $stage_details['name'], $stage_details['type']);
        }

        // Configure form views & approval levels.

        // Request stage.
        $stage1 = workflow_stage_model::load_by_id($stage1->id);
        $stage1->configure_formview([
            [
                'field_key' => 'course_link',
                'visibility' => formviews::READ_ONLY,
            ],
            [
                'field_key' => 'rationale',
                'visibility' => formviews::EDITABLE_AND_REQUIRED,
            ],
        ]);

        // Load the default transition from stage1's withdrawal-before-submission interaction.
        $interaction_entity = workflow_stage_interaction::repository()
            ->where('workflow_stage_id', '=', $stage1->id)
            ->where('action_code', '=', withdraw_before_submission::get_code())
            ->one(true);
        $interaction_transition_entity = workflow_stage_interaction_transition::repository()
            ->where('workflow_stage_interaction_id', '=', $interaction_entity->id)
            ->where_null('condition_key')
            ->one(true);
        $interaction_transition = workflow_stage_interaction_transition_model::load_by_entity($interaction_transition_entity);

        // Create a new 'stage' transition to stage4, update the default transition with it.
        $new_transition = new stage($stage4->id);
        $interaction_transition->set_transition($new_transition);

        // Done with stage 1.

        // Approvals stage.
        $stage2 = workflow_stage_model::load_by_id($stage2->id);
        $stage2->configure_formview([
            [
                'field_key' => 'course_link',
                'visibility' => formviews::READ_ONLY,
            ],
            [
                'field_key' => 'rationale',
                'visibility' => formviews::EDITABLE_AND_REQUIRED,
            ],
        ]);

        // Done with all stages.
    }
}