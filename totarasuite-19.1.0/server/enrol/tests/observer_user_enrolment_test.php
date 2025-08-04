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

use core\orm\query\exceptions\record_not_found_exception;
use core_enrol\event\pre_user_enrolment_bulk_deleted;
use core_enrol\event\pre_user_enrolment_deleted;
use core_enrol\model\user_enrolment_application;
use core_phpunit\testcase;
use mod_approval\form\approvalform_core_enrol_base;
use mod_approval\model\application\application;
use mod_approval\model\application\application_state;
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
 * Class core_enrol_observer_user_enrolment_test
 *
 * @package core
 */
class core_enrol_observer_user_enrolment_test extends testcase {

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

    public function test_pre_user_enrolment_deleted(): void {
        global $DB;

        // Set up some stuff.
        $workflow = $this->create_workflow();
        $target_user = self::getDataGenerator()->create_user();
        $control1_user = self::getDataGenerator()->create_user();
        $control2_user = self::getDataGenerator()->create_user();
        $target_application = $this->create_application($target_user, $workflow);
        $control1_application = $this->create_application($control1_user, $workflow);
        $control2_application = $this->create_application($control2_user, $workflow);

        // Enrol the users in the course.
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($target_user->id, $course->id, null, 'self');
        self::getDataGenerator()->enrol_user($control1_user->id, $course->id, null, 'self');
        self::getDataGenerator()->enrol_user($control2_user->id, $course->id, null, 'self');

        // Find the user enrolment records just created.
        $target_user_enrolment = $DB->get_record('user_enrolments', ['userid' => $target_user->id]);
        $control1_user_enrolment = $DB->get_record('user_enrolments', ['userid' => $control1_user->id]);
        $control2_user_enrolment = $DB->get_record('user_enrolments', ['userid' => $control2_user->id]);

        // Create the user enrolment applications.
        $target_uea = user_enrolment_application::create($target_user_enrolment->id, $target_application->id);
        $control1_uea = user_enrolment_application::create($control1_user_enrolment->id, $control1_application->id);
        $control2_uea = user_enrolment_application::create($control2_user_enrolment->id, $control2_application->id);

        // Set the target and control1 applications to not draft.
        $target_application->set_current_state(new application_state($target_application->get_current_state()->get_stage_id()));
        $control1_application->set_current_state(new application_state($control1_application->get_current_state()->get_stage_id()));

        // Check initial state.
        self::assertEquals('Stage 1', $target_application->refresh()->get_current_state()->get_stage()->name);
        self::assertFalse($target_application->refresh()->get_current_state()->is_draft());
        self::assertEquals('Stage 1', $control1_application->refresh()->get_current_state()->get_stage()->name);
        self::assertFalse($control1_application->refresh()->get_current_state()->is_draft());
        self::assertTrue($control2_application->refresh()->get_current_state()->is_draft());

        // Trigger the event.
        pre_user_enrolment_deleted::create([
            'objectid' => $target_user_enrolment->id,
            'context' => context_course::instance($course->id),
        ])->trigger();

        // Check the correct application was archived.
        $archived_stage = approvalform_core_enrol_base::get_archived_stage($workflow->get_active_version());
        self::assertEquals($archived_stage->id, $target_application->refresh()->get_current_state()->get_stage()->id);

        // Check the control was unaffected.
        self::assertEquals('Stage 1', $control1_application->refresh()->get_current_state()->get_stage()->name);
        self::assertTrue($control2_application->refresh()->get_current_state()->is_draft());

        // Mark control1's application complete.
        $control1_application->mark_completed();

        // Trigger the event.
        pre_user_enrolment_deleted::create([
            'objectid' => $control1_user_enrolment->id,
            'context' => context_course::instance($course->id),
        ])->trigger();

        // Check the controls unaffected.
        self::assertEquals('Stage 1', $control1_application->refresh()->get_current_state()->get_stage()->name);
        self::assertTrue($control2_application->refresh()->get_current_state()->is_draft());

        // Remove control1's user enrolment application link.
        $control1_application->get_entity_copy()
            ->set_attribute('completed', false)
            ->save();
        $control1_uea->get_entity_copy()->delete();

        // Trigger the event.
        pre_user_enrolment_deleted::create([
            'objectid' => $control1_user_enrolment->id,
            'context' => context_course::instance($course->id),
        ])->trigger();

        // Nothing could have happened because there was no link - we just checked that nothing exploded.
        self::assertTrue($control2_application->refresh()->get_current_state()->is_draft());

        // Trigger the event for control2.
        pre_user_enrolment_deleted::create([
            'objectid' => $control2_user_enrolment->id,
            'context' => context_course::instance($course->id),
        ])->trigger();

        // Check that the control2 application has been deleted.
        self::expectException(record_not_found_exception::class);
        self::expectExceptionMessage('Can not find data record in database');
        $control2_application->refresh();
    }

    public function test_pre_user_enrolment_bulk_deleted(): void {
        global $DB;

        // Set up some stuff.
        $workflow = $this->create_workflow();
        $user1 = self::getDataGenerator()->create_user(); // Should be archived.
        $user2 = self::getDataGenerator()->create_user(); // Should be archived.
        $user3 = self::getDataGenerator()->create_user(); // Not archived because already complete.
        $user4 = self::getDataGenerator()->create_user(); // No user enrolment application link record.
        $user5 = self::getDataGenerator()->create_user(); // Deleted because draft.
        $user1_application = $this->create_application($user1, $workflow);
        $user2_application = $this->create_application($user2, $workflow);
        $user3_application = $this->create_application($user3, $workflow);
        $user5_application = $this->create_application($user5, $workflow);

        // Enrol the users in the course.
        $course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user1->id, $course->id, null, 'self');
        self::getDataGenerator()->enrol_user($user2->id, $course->id, null, 'self');
        self::getDataGenerator()->enrol_user($user3->id, $course->id, null, 'self');
        self::getDataGenerator()->enrol_user($user4->id, $course->id, null, 'self');
        self::getDataGenerator()->enrol_user($user5->id, $course->id, null, 'self');

        // Find the user enrolment records just created.
        $user1_enrolment = $DB->get_record('user_enrolments', ['userid' => $user1->id]);
        $user2_enrolment = $DB->get_record('user_enrolments', ['userid' => $user2->id]);
        $user3_enrolment = $DB->get_record('user_enrolments', ['userid' => $user3->id]);
        $user4_enrolment = $DB->get_record('user_enrolments', ['userid' => $user4->id]);
        $user5_enrolment = $DB->get_record('user_enrolments', ['userid' => $user5->id]);

        // Create the user enrolment applications.
        user_enrolment_application::create($user1_enrolment->id, $user1_application->id);
        user_enrolment_application::create($user2_enrolment->id, $user2_application->id);
        user_enrolment_application::create($user3_enrolment->id, $user3_application->id);
        user_enrolment_application::create($user5_enrolment->id, $user5_application->id);

        // Set the applications to not draft.
        $user1_application->set_current_state(new application_state($user1_application->get_current_state()->get_stage_id()));
        $user2_application->set_current_state(new application_state($user2_application->get_current_state()->get_stage_id()));
        $user3_application->set_current_state(new application_state($user3_application->get_current_state()->get_stage_id()));

        // Mark user3's application complete.
        $user3_application->mark_completed();

        // Check initial state.
        self::assertEquals('Stage 1', $user1_application->refresh()->get_current_state()->get_stage()->name);
        self::assertFalse($user1_application->refresh()->get_current_state()->is_draft());
        self::assertEquals('Stage 1', $user2_application->refresh()->get_current_state()->get_stage()->name);
        self::assertFalse($user2_application->refresh()->get_current_state()->is_draft());
        self::assertEquals('Stage 1', $user3_application->refresh()->get_current_state()->get_stage()->name);
        self::assertFalse($user3_application->refresh()->get_current_state()->is_draft());
        self::assertEquals('Stage 1', $user5_application->refresh()->get_current_state()->get_stage()->name);
        self::assertTrue($user5_application->refresh()->get_current_state()->is_draft());

        // Trigger the event.
        pre_user_enrolment_bulk_deleted::create([
            'objectid' => $user1_enrolment->enrolid,
            'other' => [
                'user_enrolment_ids' => [
                    $user1_enrolment->id,
                    $user2_enrolment->id,
                    $user3_enrolment->id,
                    $user4_enrolment->id,
                    $user5_enrolment->id,
                ]
            ],
            'context' => context_course::instance($course->id),
        ])->trigger();

        // Check the correct applications were archived.
        $archived_stage = approvalform_core_enrol_base::get_archived_stage($workflow->get_active_version());
        self::assertEquals($archived_stage->id, $user1_application->refresh()->get_current_state()->get_stage()->id);
        self::assertEquals($archived_stage->id, $user2_application->refresh()->get_current_state()->get_stage()->id);
        self::assertEquals('Stage 1', $user3_application->refresh()->get_current_state()->get_stage()->name);

        // Check that user5's application has been deleted.
        self::expectException(record_not_found_exception::class);
        self::expectExceptionMessage('Can not find data record in database');
        $user5_application->refresh();
    }
}
