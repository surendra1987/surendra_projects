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

use approvalform_enrol\enrol;
use approvalform_enrol\installer;
use approvalform_enrol\observer\application_completed_observer;
use core\entity\user;
use core_enrol\event\user_enrolment_application_approved;
use core_phpunit\testcase;
use mod_approval\event\application_completed;
use mod_approval\model\assignment\assignment_type\cohort;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_type\approvals;
use mod_approval\model\workflow\stage_type\finished;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\workflow as workflow_model;
use mod_approval\model\workflow\workflow_version as workflow_version_model;
use mod_approval\testing\approval_workflow_test_setup;
use mod_approval\testing\assignment_generator_object;
use mod_approval\testing\workflow_generator_object;

/**
 * @coversDefaultClass \approvalform_enrol\observer\application_completed_observer
 *
 * @group approval_workflow
 */
class approvalform_enrol_application_completed_observer_test extends testcase {

    use approval_workflow_test_setup;

    public function test_no_trigger_enrolment_approved_when_different_approvalform() {
        list($workflow, $framework, $assignment) = $this->create_workflow_and_assignment();
        $user1 = $this->getDataGenerator()->create_user();
        $user_entity1 = new user($user1->id);
        $this->setAdminUser();
        $application = $this->create_submitted_application($workflow, $assignment, $user_entity1);

        $saa_event = application_completed::create_from_application($application);

        // Redirect events
        $sink = $this->redirectEvents();

        application_completed_observer::trigger_enrolment_approved($saa_event);

        // Collect any events triggered.
        $events = $sink->get_events();
        $sink->close();

        $this->assertEquals(0, count($events));
    }

    public function test_trigger_enrolment_approved() {
        global $CFG;

        $generator = $this->generator();
        $schema_path = $CFG->dirroot . '/mod/approval/tests/fixtures/schema/enrol.json';

        $this->setAdminUser();

        $workflow_type = $generator->create_workflow_type('Testing');
        $form_version = $generator->create_form_and_version('enrol', 'Course enrolment form', $schema_path);
        $form = $form_version->form;
        $workflow_go = new workflow_generator_object($workflow_type->id, $form->id, $form_version->id, status::DRAFT);
        $workflow_go->name = 'Course approval workflow';
        $workflow_version = $generator->create_workflow_and_version($workflow_go);
        $workflow = $workflow_version->workflow;

        // Stages
        installer::configure_publishable_workflow(workflow_version_model::load_by_entity($workflow_version));
        $approved_stage = $workflow_version->stages()->where('name', '=', enrol::APPROVED_END_STAGE)->one(true);

        // Assignment
        $cohort1 = $this->getDataGenerator()->create_cohort();
        $assignment_go = new assignment_generator_object($workflow->course_id, cohort::get_code(), $cohort1->id);
        $assignment_go->is_default = true;
        $assignment = $generator->create_assignment($assignment_go);

        // Publish workflow
        workflow_model::load_by_entity($workflow)->publish(workflow_version_model::load_by_entity($workflow_version));

        // Create application via enrolment
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $plugin = enrol_get_plugin('self');
        $instance = \core\entity\enrol::repository()
            ->where('courseid', '=', $course->id)
            ->where('enrol', '=', 'self')
            ->one(true);
        $instance->workflow_id = $workflow->id;
        $instance->save();
        $plugin->enrol_user((object)$instance->to_array(), $user->id);

        // Approve the application so that it lands in the 'Approved' stage.
        $application = \mod_approval\entity\application\application::repository()->one(true);
        $application->current_stage_id = $approved_stage->id;
        $application->completed = time();
        $application->save();
        $application_completed_event = application_completed::create_from_application(\mod_approval\model\application\application::load_by_entity($application));

        // Redirect events
        $sink = $this->redirectEvents();

        application_completed_observer::trigger_enrolment_approved($application_completed_event);

        // Collect any events triggered.
        $events = $sink->get_events();
        $sink->close();

        $this->assertEquals(1, count($events));
        $ea_event = $events[0];
        $this->assertInstanceOf(user_enrolment_application_approved::class, $ea_event);
        $this->assertEquals($application->id, $ea_event->get_data()['other']['approval_application_id']);
    }
}