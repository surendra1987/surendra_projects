<?php
/**
 * This file is part of Totara Core
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package container_course
 */

use container_course\external as external_api;
use core\entity\enrol;
use core\entity\user_enrolment;
use core_phpunit\testcase;
use mod_approval\testing\workflow_generator_object;
use mod_approval\testing\generator as mod_approval_generator;
use mod_approval\model\workflow\workflow;
use mod_approval\testing\assignment_generator_object;
use mod_approval\model\assignment\assignment_type;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_type\form_submission;

class container_course_external_test extends testcase {
    /**
     * @return void
     */
    public function test_external_with_admin(): void {
        self::setAdminUser();
        $generator = self::getDataGenerator();
        $course = $generator->create_course();

        self::assertEquals(0, user_enrolment::repository()->count());

        $enrol = enrol_get_plugin("self");
        $instance = enrol::repository()->find_enrol("self", $course->id);
        $enrol->update_status($instance->to_record(true), ENROL_INSTANCE_ENABLED);
        $result = external_api::process_non_interactive_enrol($course->id);
        self::assertTrue($result['success']);
        self::assertTrue(user_enrolment::repository()->where('userid', get_admin()->id)->exists());
    }

    /**
     * @return void
     */
    public function test_external_with_site_guest(): void {
        self::setGuestUser();
        $generator = self::getDataGenerator();
        $course = $generator->create_course();

        self::assertEquals(0, user_enrolment::repository()->count());

        $enrol = enrol_get_plugin("self");
        $instance = enrol::repository()->find_enrol("self", $course->id);
        $enrol->update_status($instance->to_record(true), ENROL_INSTANCE_ENABLED);

        self::expectExceptionMessage('Not support to site guest');
        self::expectException(coding_exception::class);
        external_api::process_non_interactive_enrol($course->id);
    }

    /**
     * @return void
     */
    public function test_external_with_enrolled_user(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course();

        self::setUser($user);
        $enrol = enrol_get_plugin("self");
        $instance = enrol::repository()->find_enrol("self", $course->id);
        $instance = $instance->to_record(true);
        $enrol->update_status($instance, ENROL_INSTANCE_ENABLED);

        // Enrol user.
        $enrol->enrol_user($instance, $user->id);

        self::expectExceptionMessage('You have already enrolled');
        self::expectException(coding_exception::class);
        external_api::process_non_interactive_enrol($course->id);
    }

    /**
     * @return void
     */
    public function test_external_with_password_change(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course();

        self::setUser($user);
        $enrol = enrol_get_plugin("self");
        $instance = enrol::repository()->find_enrol("self", $course->id);
        $update_instance = $instance->to_record(true);
        $update_instance->password = 'password';
        $enrol->update_instance($instance->to_record(true), $update_instance);


        self::expectExceptionMessage('Not support non interactive enrol');
        self::expectException(coding_exception::class);
        external_api::process_non_interactive_enrol($course->id);
    }

    /**
     * @return void
     */
    public function test_external_new_enrol_setting(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course();

        self::setUser($user);
        $enrol = enrol_get_plugin("self");
        $instance = enrol::repository()->find_enrol("self", $course->id);
        $update_instance = $instance->to_record(true);
        $update_instance->customint6 = '0';
        $enrol->update_instance($instance->to_record(true), $update_instance);

        self::expectExceptionMessage('Not support non interactive enrol');
        self::expectException(coding_exception::class);
        external_api::process_non_interactive_enrol($course->id);
    }

    /**
     * @return void
     */
    public function test_external_with_audience_enabled(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course();

        self::setUser($user);
        $enrol = enrol_get_plugin("self");
        $instance = enrol::repository()->find_enrol("self", $course->id);
        $update_instance = $instance->to_record(true);
        $update_instance->customint5 = '1';
        $enrol->update_instance($instance->to_record(true), $update_instance);

        self::expectException(coding_exception::class);
        self::expectExceptionMessage('Not support non interactive enrol');
        external_api::process_non_interactive_enrol($course->id);
    }

    /**
     * @return void
     */
    public function test_process_non_interactive_enrol_requires_approval(): void {
        global $DB;

        $gen = self::getDataGenerator();
        $user = $gen->create_user();

        self::setUser($user);
        $course = $gen->create_course();
        $this->enable_non_interactive_enrol($course->id);

        $workflow = $this->create_workflow();
        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'self']);
        $instance->workflow_id = $workflow->id;
        $DB->update_record('enrol', $instance);
        $result = external_api::process_non_interactive_enrol($course->id);

        self::assertTrue($result['success']);
        self::assertNotEmpty($result['redirect_url']);
        self::assertStringContainsString('mod/approval/application/edit.php?application_id=', $result['redirect_url']);
    }

    /**
     * @return workflow $workflow
     */
    private function create_workflow(): workflow {
        self::setAdminUser();
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
     * @param int $course_id
     * @return void
     */
    private function enable_non_interactive_enrol(int $course_id): void {
        global $DB;

        // Enabled self enrolment.
        $self_plugin = enrol_get_plugin('self');
        $instance = $DB->get_record(
            'enrol',
            [
                'courseid' => $course_id,
                'enrol' => 'self'
            ],
            '*',
            MUST_EXIST
        );

        $self_plugin->update_status($instance, ENROL_INSTANCE_ENABLED);

        // Enabled guest access.
        $enrol_instance = $DB->get_record(
            'enrol',
            [
                'enrol' => 'guest',
                'courseid' => $course_id
            ],
            '*',
            MUST_EXIST
        );

        $guest_plugin = enrol_get_plugin('guest');
        $guest_plugin->update_status($enrol_instance, ENROL_INSTANCE_ENABLED);
    }
}