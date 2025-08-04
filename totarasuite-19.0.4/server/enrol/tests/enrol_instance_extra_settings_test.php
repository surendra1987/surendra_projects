<?php
/*
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_enrol
 */

use core_enrol\model\user_enrolment_application;
use core_enrol\hook\enrol_instance_extra_settings_definition;
use core_enrol\hook\enrol_instance_extra_settings_display;
use core_enrol\hook\enrol_instance_extra_settings_save;
use core_enrol\hook\enrol_instance_extra_settings_validation;
use core_enrol\testing\enrol_instance_edit_form_test_setup;
use core_phpunit\testcase;
use mod_approval\model\application\action\withdraw_in_approvals;
use mod_approval\model\application\application;
use mod_approval\model\assignment\assignment_type;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\workflow;
use mod_approval\testing\assignment_generator_object;
use mod_approval\testing\generator as mod_approval_generator;
use mod_approval\testing\workflow_generator_object;
use totara_hierarchy\testing\generator as totara_hierarchy_generator;

/**
 * Test enrolment instance settings hooks
 */
class core_enrol_enrol_instance_extra_settings_test extends testcase {

    use enrol_instance_edit_form_test_setup;

    public function test_definition_hook_getters() {
        $params = $this->enrol_instance_edit_form_params();
        list($instance, $plugin, $context, $type, $return) = $params;
        $form = new enrol_instance_edit_form(null, $params);
        $hook = new enrol_instance_extra_settings_definition($form, $instance, $plugin, $context);

        $this->assertEquals($form, $hook->enrolment_form);
        $this->assertEquals($instance, $hook->get_enrolment_instance());
        $this->assertEquals($plugin, $hook->get_plugin());
        $this->assertInstanceOf(\enrol_self_plugin::class, $hook->get_plugin());
        $this->assertEquals($context, $hook->get_context());
        $this->assertInstanceOf(context_course::class, $hook->get_context());
    }

    public function test_display_hook_getters() {
        $params = $this->enrol_instance_edit_form_params();
        list($instance, $plugin, $context, $type, $return) = $params;
        $form = new enrol_instance_edit_form(null, $params);
        $hook = new enrol_instance_extra_settings_display($form, $instance, $plugin, $context);

        $this->assertEquals($form, $hook->enrolment_form);
        $this->assertEquals($instance, $hook->get_enrolment_instance());
        $this->assertEquals($plugin, $hook->get_plugin());
        $this->assertInstanceOf(\enrol_self_plugin::class, $hook->get_plugin());
        $this->assertEquals($context, $hook->get_context());
        $this->assertInstanceOf(context_course::class, $hook->get_context());
    }

    public function test_save_hook_getters() {
        $params = $this->enrol_instance_edit_form_params();
        list($instance, $plugin, $context, $type, $return) = $params;
        $data = (object)['foo' => 'bar'];
        $hook = new enrol_instance_extra_settings_save($data, $instance);

        $this->assertEquals($data, $hook->get_data());
        $this->assertEquals($instance, $hook->get_enrolment_instance());
        $this->assertInstanceOf(\enrol_self_plugin::class, $hook->get_plugin());
        $this->assertEquals($context, $hook->get_context());
        $this->assertInstanceOf(context_course::class, $hook->get_context());
    }

    public function test_validation_hook_getters() {
        list($instance, $plugin, $context, $type, $return) = $this->enrol_instance_edit_form_params();
        $data = ['foo' => 'bar'];
        $files = ['zapf' => 'quux'];
        $hook = new enrol_instance_extra_settings_validation($data, $files, $instance, $plugin, $context);

        $this->assertEquals($data, $hook->get_data());
        $this->assertEquals($files, $hook->get_files());
        $this->assertEquals($instance, $hook->get_enrolment_instance());
        $this->assertEquals($plugin, $hook->get_plugin());
        $this->assertInstanceOf(\enrol_self_plugin::class, $hook->get_plugin());
        $this->assertEquals($context, $hook->get_context());
        $this->assertInstanceOf(context_course::class, $hook->get_context());
    }

    public function test_validation_hook_errors() {
        list($instance, $plugin, $context, $type, $return) = $this->enrol_instance_edit_form_params();
        $data = ['foo' => 'bar'];
        $files = ['zapf' => 'quux'];
        $hook = new enrol_instance_extra_settings_validation($data, $files, $instance, $plugin, $context);

        // No errors
        $this->assertIsArray($hook->get_errors());
        $this->assertEmpty($hook->get_errors());

        // Set one
        $hook->set_error('foo', 'alice');
        $this->assertNotEmpty($hook->get_errors());
        $this->assertEquals(['foo' => 'alice'], $hook->get_errors());

        // Set a different one
        $hook->set_error('bar', 'bob');
        $this->assertEquals(['foo' => 'alice', 'bar' => 'bob'], $hook->get_errors());

        // Set the first one to something else
        $hook->set_error('foo', 'zapf');
        $this->assertEquals(['foo' => 'zapf', 'bar' => 'bob'], $hook->get_errors());

        // Try to set the second to null
        try {
            $hook->set_error('foo', null);
            $this->fail('Expected TypeError exception when passing null to set_error()');
        } catch (TypeError $e) {
            $this->assertStringContainsString('must be of type string', $e->getMessage());
        }
        $this->assertEquals(['foo' => 'zapf', 'bar' => 'bob'], $hook->get_errors());
    }

    public function test_save(): void {
        global $DB;

        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        self::setUser($user);
        $course = $gen->create_course();
        $course1 = $gen->create_course();

        $workflow = $this->create_workflow();
        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'self']);
        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'self']);
        $instance->workflow_id = $workflow->id;
        $instance1->workflow_id = $workflow->id;
        $DB->update_record('enrol', $instance);
        $DB->update_record('enrol', $instance1);

        $plugin = enrol_get_plugin('self');
        // Enrol pending status.
        $plugin->enrol_user($instance, $user->id, null, time() - DAYSECS, time() + DAYSECS, ENROL_USER_PENDING_APPLICATION);
        $plugin->enrol_user($instance1, $user->id, null, time() - DAYSECS, time() + DAYSECS, ENROL_USER_PENDING_APPLICATION);

        $enrolment = $DB->get_record('user_enrolments', ['userid' => $user->id, 'enrolid' => $instance1->id]);
        $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($enrolment->id);
        $application = $user_enrolment_application->approval_application;
        \mod_approval\model\application\application_action::create($application, $user->id, new withdraw_in_approvals());

        // 1 record and the application is draft.
        self::assertEquals(2, $DB->count_records('user_enrolments_application'));
        self::assertEquals(2, $DB->count_records('approval_application'));

        // Modify the workflow id
        $new_workflow = $this->create_workflow();
        $instance->workflow_id = $new_workflow->id;
        $hook = new enrol_instance_extra_settings_save(new stdClass(), $instance);

        \mod_approval\watcher\enrol_instance_extra_settings::save($hook);

        // No record.
        self::assertEquals(1, $DB->count_records('user_enrolments_application'));
        self::assertEquals(1, $DB->count_records('approval_application'));

        // Enrol pending status.
        $plugin->enrol_user($instance, $user->id, null, time() - DAYSECS, time() + DAYSECS, ENROL_USER_PENDING_APPLICATION);

        $instance->workflow_id = null;
        $hook = new enrol_instance_extra_settings_save(new stdClass(), $instance);
        \mod_approval\watcher\enrol_instance_extra_settings::save($hook);
        $record = $DB->get_record('user_enrolments', ['userid' => $user->id, 'enrolid' => $instance->id]);
        self::assertEquals(0, $record->status);
    }

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
}
