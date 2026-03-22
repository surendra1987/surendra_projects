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
use approvalform_enrol\observer\user_enrolment_application_created_observer;
use core\entity\user;
use core\entity\user_enrolment;
use core_enrol\enrolment_approval_helper;
use core_enrol\event\user_enrolment_application_approved;
use core_enrol\event\user_enrolment_application_created;
use core_enrol\model\user_enrolment_application;
use core_phpunit\testcase;
use mod_approval\entity\application\application_submission;
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
 * @coversDefaultClass \approvalform_enrol\observer\user_enrolment_application_created_observer
 *
 * @group approval_workflow
 */
class approvalform_enrol_user_enrolment_application_created_observer_test extends testcase {

    use approval_workflow_test_setup;

    private function create_test_workflow(string $plugin) {
        global $CFG;

        $generator = $this->generator();
        $schema_path = $CFG->dirroot . '/mod/approval/tests/fixtures/schema/enrol.json';

        $this->setAdminUser();

        $workflow_type = $generator->create_workflow_type('Testing');
        $form_version = $generator->create_form_and_version($plugin, 'Course enrolment form', $schema_path);
        $form = $form_version->form;
        $workflow_go = new workflow_generator_object($workflow_type->id, $form->id, $form_version->id, status::DRAFT);
        $workflow_go->name = 'Course approval workflow';
        $workflow_version = $generator->create_workflow_and_version($workflow_go);
        $workflow = $workflow_version->workflow;

        // Stages
        installer::configure_publishable_workflow(workflow_version_model::load_by_entity($workflow_version));

        // Assignment
        $cohort1 = $this->getDataGenerator()->create_cohort();
        $assignment_go = new assignment_generator_object($workflow->course_id, cohort::get_code(), $cohort1->id);
        $assignment_go->is_default = true;
        $assignment = $generator->create_assignment($assignment_go);

        // Publish workflow
        workflow_model::load_by_entity($workflow)->publish(workflow_version_model::load_by_entity($workflow_version));

        return $workflow;
    }

    private function create_user_enrolment_application($workflow, $user, $course): user_enrolment_application {
        $this->setUser($user);
        $plugin = enrol_get_plugin('self');
        $instance = \core\entity\enrol::repository()
            ->where('courseid', '=', $course->id)
            ->where('enrol', '=', 'self')
            ->one(true);
        $instance->workflow_id = $workflow->id;
        $instance->save();

        // Redirect events to prevent our test event from being triggered.
        $sink = $this->redirectEvents();
        $plugin->enrol_user((object)$instance->to_array(), $user->id, null, time() - DAYSECS, time() + DAYSECS, 666);
        $sink->close();

        $user_enrolment = user_enrolment::repository()
            ->where('enrolid', '=', $instance->id)
            ->where('userid', '=', $user->id)
            ->one(true);
        return user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);
    }

    private function create_test_event(user_enrolment_application $user_enrolment_application) {
        return user_enrolment_application_created::create([
            'objectid' => $user_enrolment_application->id,
            'other' => [
                'user_enrolments_id' => $user_enrolment_application->user_enrolments_id,
                'approval_application_id' => $user_enrolment_application->approval_application_id,
            ],
            'context' => \context_course::instance($user_enrolment_application->course->id),
        ]);
    }

    public function test_no_submission_created_when_different_approvalform() {
        $workflow = $this->create_test_workflow('simple');
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        // Enrol the user.
        $user_enrolment_application = $this->create_user_enrolment_application($workflow, $user, $course);

        // Create the event.
        $user_enrolment_application_created_event = $this->create_test_event($user_enrolment_application);

        // No submissions before event.
        $this->assertEquals(0, application_submission::repository()->count());

        // Trigger the event.
        $user_enrolment_application_created_event->trigger();

        // No submissions after event, because not approvalform_enrol.
        $this->assertEquals(0, application_submission::repository()->count());
    }

    public function test_trigger_enrolment_approved() {
        $workflow = $this->create_test_workflow('enrol');
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        // Enrol the user.
        $user_enrolment_application = $this->create_user_enrolment_application($workflow, $user, $course);

        // Create the event.
        $user_enrolment_application_created_event = $this->create_test_event($user_enrolment_application);

        // No submissions before event.
        $this->assertEquals(0, application_submission::repository()->count());

        // Trigger the event.
        $user_enrolment_application_created_event->trigger();

        // There is now a draft submission we can inspect.
        $this->assertEquals(1, application_submission::repository()->count());
        $application_submission_entity = application_submission::repository()->where('application_id', '=', $user_enrolment_application->approval_application_id)->one(true);
        $application_submission = \mod_approval\model\application\application_submission::load_by_entity($application_submission_entity);
        $form_data = $application_submission->get_form_data_parsed();
        $this->assertEquals($course->id, $form_data->get_value('course_id'));
        $this->assertStringContainsString($course->fullname, $form_data->get_value('course_link'));
    }
}