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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package core_enrol
 */

use core_enrol\event\user_enrolment_application_approved;
use core_enrol\model\user_enrolment_application;
use core_phpunit\testcase;
use mod_approval\form\approvalform_core_enrol_base;
use mod_approval\model\application\application;
use mod_approval\model\assignment\assignment_type;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_type\finished;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\workflow;
use mod_approval\testing\assignment_generator_object;
use mod_approval\testing\generator as mod_approval_generator;
use mod_approval\testing\workflow_generator_object;
use totara_hierarchy\testing\generator as totara_hierarchy_generator;

defined('MOODLE_INTERNAL') || die();

/**
 * Class core_enrol_observer_user_enrolment_application_test
 *
 * @package core
 */
class core_enrol_observer_user_enrolment_application_test extends testcase {

    /**
     * @return workflow $workflow
     */
    private function create_workflow(): workflow {
        $this->setAdminUser();
        $generator = mod_approval_generator::instance();

        $workflow_type = $generator->create_workflow_type('test workflow type');

        // Create a form and version
        $form_version = $generator->create_form_and_version();
        $form = $form_version->form;

        // Create a workflow and version
        $workflow_go = new workflow_generator_object($workflow_type->id, $form->id, $form_version->id, status::DRAFT);
        $workflow_version = $generator->create_workflow_and_version($workflow_go);
        $workflow = $workflow_version->workflow;
        $generator->create_workflow_stage($workflow_version->id, 'Stage 1', form_submission::get_enum());
        $generator->create_workflow_stage($workflow_version->id, approvalform_core_enrol_base::ARCHIVED_END_STAGE, finished::get_enum());

        $workflow_version->status = status::ACTIVE;
        $workflow_version->save();

        // Generate a simple organisation hierarchy
        /** @var totara_hierarchy_generator $hierarchy_generator */
        $hierarchy_generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $hierarchy_generator->create_framework('organisation');
        $agency = $hierarchy_generator->create_org(
            [
                'frameworkid' => $framework->id,
                'fullname' => 'Agency',
                'idnumber' => '001',
                'shortname' => 'org'
            ]
        );

        // Create a default assignment
        $assignment_go = new assignment_generator_object(
            $workflow->course_id,
            assignment_type\organisation::get_code(),
            $agency->id
        );
        $assignment_go->is_default = true;
        $generator->create_assignment($assignment_go);

        return workflow::load_by_entity($workflow);
    }

    /**
     * @param stdClass $user
     * @param workflow $workflow
     * @return application
     */
    private function create_application(stdClass $user, workflow $workflow): application {
        return application::create($workflow->get_active_version(), $workflow->default_assignment, $user->id);
    }

    public function test_approved(): void {
        global $DB;

        // Set up some stuff.
        $workflow = $this->create_workflow();
        $target_user = self::getDataGenerator()->create_user();
        $control_user = self::getDataGenerator()->create_user();
        $target_application = $this->create_application($target_user, $workflow);
        $control_application = $this->create_application($control_user, $workflow);

        // If not enabled all completion settings will be ignored.
        set_config('enablecompletion', COMPLETION_ENABLED);

        // Enrol the users in the course.
        $course = self::getDataGenerator()->create_course(['enablecompletion' => COMPLETION_ENABLED]);
        self::getDataGenerator()->enrol_user($target_user->id, $course->id, null, 'self', 0, 0, ENROL_USER_PENDING_APPLICATION);
        self::getDataGenerator()->enrol_user($control_user->id, $course->id, null, 'self', 0, 0, ENROL_USER_PENDING_APPLICATION);

        // Find the user enrolment records just created.
        $target_enrolment = $DB->get_record('user_enrolments', ['userid' => $target_user->id]);
        $control_enrolment = $DB->get_record('user_enrolments', ['userid' => $control_user->id]);

        // Create the user enrolment applications.
        $target_user_enrolment_application = user_enrolment_application::create($target_enrolment->id, $target_application->id);
        $control_user_enrolment_application = user_enrolment_application::create($control_enrolment->id, $control_application->id);

        // Check the enrolment state before firing the event.
        self::assertEquals(ENROL_USER_PENDING_APPLICATION, $target_enrolment->status);
        self::assertEquals(ENROL_USER_PENDING_APPLICATION, $control_enrolment->status);

        // Check that there is no course completion before firing the event.
        self::assertEmpty($DB->get_records('course_completions'));

        // Trigger the event.
        user_enrolment_application_approved::create([
            'objectid' => $target_user_enrolment_application->id,
            'other' => [
                'user_enrolments_id' => $target_user_enrolment_application->user_enrolments_id,
                'approval_application_id' => $target_user_enrolment_application->approval_application_id,
            ],
            'context' => context_course::instance($course->id),
        ])->trigger();

        // Check the enrolment state after firing the event.
        $target_enrolment = $DB->get_record('user_enrolments', ['userid' => $target_user->id]);
        $control_enrolment = $DB->get_record('user_enrolments', ['userid' => $control_user->id]);
        self::assertEquals(ENROL_USER_ACTIVE, $target_enrolment->status);
        self::assertEquals(ENROL_USER_PENDING_APPLICATION, $control_enrolment->status);

        // Check that the course completion was created.
        $course_completions = $DB->get_records('course_completions');
        self::assertCount(1, $course_completions);
        $course_completion = reset($course_completions);
        self::assertEquals($target_user->id, $course_completion->userid);
        self::assertEquals($course->id, $course_completion->course);
    }
}
