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
 * @package mod_approval
 */

use core\entity\course;
use core\entity\user;
use core\orm\query\builder;
use core_enrol\model\user_enrolment_application;
use mod_approval\entity\workflow\workflow_stage_interaction_transition;
use mod_approval\exception\model_exception;
use mod_approval\form\approvalform_core_enrol_base;
use mod_approval\interactor\application_interactor;
use mod_approval\model\application\application;
use mod_approval\model\form\form_data;
use mod_approval\model\workflow\stage_type\approvals;
use mod_approval\model\workflow\workflow;

require_once(__DIR__ . '/testcase.php');

/**
 * @group approval_workflow
 * @coversDefaultClass mod_approval\form\approvalform_core_enrol_base
 */
class mod_approval_approvalform_core_enrol_base_test extends mod_approval_testcase {

    /**
     * @covers ::enables_component
     */
    public function test_enables_component(): void {
        $this->assertTrue(approvalform_core_enrol_base::enables_component('core_enrol'));
        $this->assertFalse(approvalform_core_enrol_base::enables_component('something_else'));
    }

    private function create_user_enrolment_application(): user_enrolment_application {
        global $DB;
        $this->setAdminUser();

        // Create a workflow and application.
        $application = $this->create_application_for_user();
        $user = $application->get_user();
        $course = $this->getDataGenerator()->create_course();

        // Enrol the user.
        $plugin = enrol_get_plugin('self');
        $instance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'self'));
        $plugin->enrol_user($instance, $user->id, null, time() - DAYSECS, time() + DAYSECS, 42);
        $user_enrolment = $DB->get_record('user_enrolments', ['enrolid' => $instance->id, 'userid' => $user->id]);

        return user_enrolment_application::create($user_enrolment->id, $application->id);
    }

    private function get_new_instance(): approvalform_core_enrol_base {
        return \mod_approval\model\form\approvalform_base::from_plugin_name('enrol');
    }

    public function test_observe_form_data_for_application() {
        $plugin = $this->get_new_instance();
        $user_enrolment_application = $this->create_user_enrolment_application();

        $application = application::load_by_id($user_enrolment_application->approval_application_id);
        $form_data = form_data::create_empty();
        $course = $user_enrolment_application->get_course();

        $application_title = get_string('form_title', 'approvalform_enrol', $course->fullname);
        $this->assertNotEquals($application_title, $application->title);

        $plugin->observe_form_data_for_application($application, $form_data);
        $application->refresh();
        $this->assertEquals($application_title, $application->title);
    }

    public function test_adjust_form_schema_for_application() {
        $plugin = $this->get_new_instance();
        $user_enrolment_application = $this->create_user_enrolment_application();

        $application_interactor = application_interactor::from_application_id($user_enrolment_application->approval_application_id, user::logged_in()->id);
        $form_schema = $plugin->get_form_schema();
        $course = $user_enrolment_application->get_course();
        $this->assertEmpty($form_schema->get_field('course_link')->default);

        $form_schema = $plugin->adjust_form_schema_for_application($application_interactor, $form_schema);
        $this->assertEquals(approvalform_core_enrol_base::encode_course_link_data($course), $form_schema->get_field('course_link')->default);
    }

    public function test_prepare_raw_form_data_for_edit() {
        $plugin = $this->get_new_instance();
        $user_enrolment_application = $this->create_user_enrolment_application();

        $application_interactor = application_interactor::from_application_id($user_enrolment_application->approval_application_id, user::logged_in()->id);
        $course = $user_enrolment_application->get_course();

        // Test with empty data.
        $data1 = $plugin->prepare_raw_form_data_for_edit([], $application_interactor);
        $this->assertEquals($course->id, $data1['course_id']);
        $this->assertEquals(approvalform_core_enrol_base::encode_course_link_data($course), $data1['course_link']);

        // Test with existing data.
        $raw_data2 = ['course_id' => 42, 'course_link' => 'foo', 'another_field' => 'bar'];
        $data2 = $plugin->prepare_raw_form_data_for_edit($raw_data2, $application_interactor);
        $this->assertEquals($course->id, $data2['course_id']);
        $this->assertEquals(approvalform_core_enrol_base::encode_course_link_data($course), $data2['course_link']);
        $this->assertEquals('bar', $data2['another_field']);

        delete_course($course->id, false);
        $raw_data3 = ['course_id' => $course->id, 'course_link' => json_encode(['id' => $course->id, 'url' => 'url']), 'another_field' => 'bar'];
        $data3 = $plugin->prepare_raw_form_data_for_edit($raw_data3, $application_interactor);
        $course_link = json_decode($data3['course_link']);
        $this->assertTrue($course_link->course_deleted);
    }

    public function test_prepare_raw_form_data_for_view() {
        $plugin = $this->get_new_instance();
        $user_enrolment_application = $this->create_user_enrolment_application();

        $application_interactor = application_interactor::from_application_id($user_enrolment_application->approval_application_id, user::logged_in()->id);
        $course = $user_enrolment_application->get_course();

        // Test with empty data.
        $data1 = $plugin->prepare_raw_form_data_for_view([], $application_interactor);
        $this->assertEquals($course->id, $data1['course_id']);
        $this->assertEquals(approvalform_core_enrol_base::encode_course_link_data($course), $data1['course_link']);

        // Test with existing data.
        $raw_data2 = ['course_id' => 42, 'course_link' => 'foo', 'another_field' => 'bar'];
        $data2 = $plugin->prepare_raw_form_data_for_view($raw_data2, $application_interactor);
        $this->assertEquals($course->id, $data2['course_id']);
        $this->assertEquals(approvalform_core_enrol_base::encode_course_link_data($course), $data2['course_link']);
        $this->assertEquals('bar', $data2['another_field']);
    }

    public function test_encode_course_link_data() {
        $course_rec = $this->getDataGenerator()->create_course();
        $course = new course($course_rec->id);

        $encoded_data = approvalform_core_enrol_base::encode_course_link_data($course);
        $data = approvalform_core_enrol_base::json_decode($encoded_data);
        $this->assertEquals($course->id, $data->id);
        $this->assertEquals(format_string($course->fullname), $data->name);
        $this->assertStringContainsString('/course/view.php?id=' . $course->id, $data->url);
    }

    public function test_encode_course_link_data_multilang() {
        // Enable the multilang filter and set it to apply to headings and content.
        filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', true);
        filter_manager::reset_caches();

        $this->setAdminUser();
        $course_rec = $this->getDataGenerator()->create_course(['fullname' => '<span lang="en" class="multilang">English</span><span lang="de" class="multilang">German</span>']);
        $course = new course($course_rec->id);

        $encoded_data = approvalform_core_enrol_base::encode_course_link_data($course);
        $data = approvalform_core_enrol_base::json_decode($encoded_data);
        $this->assertEquals('English', $data->name);
    }

    public function test_verify_workflow_structure_wrong_workflow() {
        $workflow = $this->create_workflow_for_user();
        $this->assertNotEquals('enrol', $workflow->form->plugin_name);
        $workflow_version = $workflow->get_active_version();
        try {
            $warnings = approvalform_core_enrol_base::verify_workflow_structure($workflow_version);
            $this->fail('Expected exception');
        } catch (model_exception $e) {
            $this->assertStringContainsString('the approvalform plugin does not extend approvalform_core_enrol_base', $e->getMessage());
        }
    }

    public function test_verify_workflow_structure_no_stages() {
        // Need to redirect hooks here to allow the workflow to be published.
        $hook_sink = $this->redirectHooks();
        $workflow = $this->create_workflow_for_user('enrol', null, 'enrol');
        $hook_sink->close();
        $this->assertEquals('enrol', $workflow->form->plugin_name);
        $warnings = approvalform_core_enrol_base::verify_workflow_structure($workflow->get_active_version());
        $this->assertCount(2, $warnings);
        $expected = [
            get_string('workflow_verification_warning:no_approved_stage', 'approvalform_enrol'),
            get_string('workflow_verification_warning:no_archived_stage', 'approvalform_enrol')
        ];
        $this->assertEqualsCanonicalizing($expected, $warnings);
    }

    public function test_verify_workflow_structure_no_approved() {
        // Workflow with no approved stage.
        $workflow2 = $this->create_workflow_for_user('enrol', [\approvalform_enrol\installer::class, 'configure_publishable_workflow'], 'enrol');
        \mod_approval\entity\workflow\workflow_stage::repository()
            ->where('workflow_version_id', '=', $workflow2->get_active_version()->id)
            ->where('name', '=', \approvalform_enrol\enrol::APPROVED_END_STAGE)
            ->delete();
        $warnings = approvalform_core_enrol_base::verify_workflow_structure($workflow2->get_active_version());
        $this->assertCount(1, $warnings);
        $expected = [
            get_string('workflow_verification_warning:no_approved_stage', 'approvalform_enrol'),
        ];
        $this->assertEqualsCanonicalizing($expected, $warnings);
    }

    public function test_verify_workflow_structure_no_archived() {
        // Workflow with no archived stage.
        $workflow1 = $this->create_workflow_for_user('enrol', [\approvalform_enrol\installer::class, 'configure_publishable_workflow'], 'enrol');
        \mod_approval\entity\workflow\workflow_stage::repository()
            ->where('workflow_version_id', '=', $workflow1->get_active_version()->id)
            ->where('name', '=', \approvalform_enrol\enrol::ARCHIVED_END_STAGE)
            ->delete();
        $warnings = approvalform_core_enrol_base::verify_workflow_structure($workflow1->get_active_version());
        $this->assertCount(1, $warnings);
        $expected = [
            get_string('workflow_verification_warning:no_archived_stage', 'approvalform_enrol'),
        ];
        $this->assertEqualsCanonicalizing($expected, $warnings);
    }

    public function test_verify_workflow_structure_no_transition() {
        // Workflow with no archived stage.
        $workflow1 = $this->create_workflow_for_user('enrol', [\approvalform_enrol\installer::class, 'configure_publishable_workflow'], 'enrol');
        $transition = $this->fetch_approval_transition_entity($workflow1);
        $transition->transition = 'PREVIOUS';
        $transition->save();
        $warnings = approvalform_core_enrol_base::verify_workflow_structure($workflow1->get_active_version());
        $this->assertCount(1, $warnings);
        $expected = [
            get_string('workflow_verification_warning:no_approval_transition', 'approvalform_enrol'),
        ];
        $this->assertEqualsCanonicalizing($expected, $warnings);
    }

    private function fetch_approval_transition_entity(workflow $workflow): workflow_stage_interaction_transition {
        $approval_stage = \mod_approval\entity\workflow\workflow_stage::repository()
            ->where('workflow_version_id', '=', $workflow->get_active_version()->id)
            ->where('type_code', '=', approvals::get_code())
            ->one(true);
        $transition_rec = builder::table(workflow_stage_interaction_transition::TABLE, 'transition')
            ->join([\mod_approval\entity\workflow\workflow_stage_interaction::TABLE, 'interaction'], 'transition.workflow_stage_interaction_id', 'interaction.id')
            ->where('interaction.workflow_stage_id', '=', $approval_stage->id)
            ->where('interaction.action_code', '=', \mod_approval\model\application\action\approve::get_code())
            ->one(true);
        return new workflow_stage_interaction_transition($transition_rec->id);
    }

    public function test_verify_workflow_structure_ok() {
        // Workflow with both stages.
        $workflow = $this->create_workflow_for_user('enrol', [\approvalform_enrol\installer::class, 'configure_publishable_workflow'], 'enrol');
        $this->assertEquals('enrol', $workflow->form->plugin_name);
        $warnings = approvalform_core_enrol_base::verify_workflow_structure($workflow->get_active_version());
        $this->assertCount(0, $warnings);
    }

    public function test_verify_workflow_structure_ok_implicit_transition() {
        // Workflow with both stages.
        $workflow = $this->create_workflow_for_user('enrol', [\approvalform_enrol\installer::class, 'configure_publishable_workflow'], 'enrol');
        $this->assertEquals('enrol', $workflow->form->plugin_name);
        $approved_stage = \mod_approval\entity\workflow\workflow_stage::repository()
            ->where('workflow_version_id', '=', $workflow->latest_version->id)
            ->where('name', '=', approvalform_core_enrol_base::APPROVED_END_STAGE)
            ->one();
        $transition = $this->fetch_approval_transition_entity($workflow);
        $transition->transition = $approved_stage->id;
        $transition->save();
        $warnings = approvalform_core_enrol_base::verify_workflow_structure($workflow->get_active_version());
        $this->assertCount(0, $warnings);
    }
}
