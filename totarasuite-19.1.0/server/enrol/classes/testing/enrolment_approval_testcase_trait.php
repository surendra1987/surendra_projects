<?php
/**
 * This file is part of Totara Core
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_enrol
 */

namespace core_enrol\testing;

use approvalform_enrol\installer;
use context_system;
use core\entity\enrol as enrol_entity;
use core\model\enrol;
use core\model\user_enrolment;
use mod_approval\model\application\application;
use mod_approval\model\assignment\assignment_type;
use mod_approval\model\status;
use mod_approval\model\workflow\workflow;
use mod_approval\model\workflow\workflow_version;
use mod_approval\testing\assignment_generator_object;
use mod_approval\testing\generator as mod_approval_generator;
use mod_approval\testing\workflow_generator_object;
use totara_core\advanced_feature;

trait enrolment_approval_testcase_trait {
    /**
     * Creates a workflow suitable for testing.
     *
     * @return workflow $workflow
     */
    private function create_workflow(): workflow {
        global $CFG;
        $schema_path = $CFG->dirroot . '/mod/approval/tests/fixtures/schema/enrol.json';

        advanced_feature::enable('approval_workflows');

        $this->setAdminUser();
        $generator = mod_approval_generator::instance();

        $workflow_type = $generator->create_workflow_type('test workflow type');

        // Create a form and version.
        $form_version = $generator->create_form_and_version('enrol', 'Course enrolment form', $schema_path);
        $form = $form_version->form;

        // Create a workflow and version.
        $workflow_go = new workflow_generator_object($workflow_type->id, $form->id, $form_version->id, status::DRAFT);
        $workflow_version_entity = $generator->create_workflow_and_version($workflow_go);
        $workflow_version = workflow_version::load_by_entity($workflow_version_entity);
        installer::configure_publishable_workflow($workflow_version);

        // Create a context assignment.
        $assignment_go = new assignment_generator_object(
            $workflow_version->workflow->course_id,
            assignment_type\context::get_code(),
            context_system::instance()->id
        );
        $assignment_go->is_default = true;
        $generator->create_assignment($assignment_go);

        // Publish it.
        $workflow_version->workflow->publish($workflow_version);
        return $workflow_version->workflow;
    }

    /**
     * Generates a self-enrolment instance with a workflow for approval
     *
     * @return enrol
     */
    private function generate_self_enrolment_instance_with_approval(): enrol {
        $course = static::getDataGenerator()->create_course();
        $workflow = $this->create_workflow();

        $enrol_instance = enrol_entity::repository()->find_by_enrol_and_course('self', $course->id)->first();
        $enrol_instance->workflow_id = $workflow->id;
        $enrol_instance->save();

        return enrol::load_by_entity($enrol_instance);
    }

    /**
     * Generates an enrolment on a self-enrolment instance with a workflow for approval
     *
     * @param enrol $instance
     * @param int $user_id
     * @return user_enrolment
     */
    private function enrol_user_on_instance(enrol $instance, int $user_id): user_enrolment {
        $enrol = enrol_get_plugin('self');
        $enrol->enrol_user($instance->to_stdClass(), $user_id);

        return user_enrolment::find_by_instance_and_user($instance->id, $user_id);
    }

    /**
     * Mark the application as completed, for testing purposes. No events generated.
     *
     * @param application $application
     * @return void
     */
    private function complete_application(application $application): void {
        $application_entity = new \mod_approval\entity\application\application($application->id);
        $application_entity->is_draft = 0;
        $application_entity->completed = time();
        $application_entity->save();
        $application->refresh(true);
    }
}