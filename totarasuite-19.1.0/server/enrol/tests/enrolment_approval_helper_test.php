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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_enrol
 */

use core\entity\enrol as enrol_entity;
use core\entity\user;
use core\model\enrol;
use core\orm\query\builder;
use core_enrol\model\user_enrolment_application;
use core_enrol\testing\enrolment_approval_testcase_trait;
use core_phpunit\testcase;
use mod_approval\controllers\application\edit as application_edit;
use mod_approval\controllers\application\view as application_view;
use mod_approval\model\application\action\submit;
use mod_approval\model\application\action\withdraw_before_submission;
use mod_approval\model\application\application;
use mod_approval\model\application\application_submission;
use mod_approval\model\assignment\approver_type\relationship;
use mod_approval\model\form\form_data;
use mod_approval\model\workflow\workflow;
use totara_core\advanced_feature;
use core_enrol\enrolment_approval_helper;
use totara_job\job_assignment;

/**
 * @coversDefaultClass \core_enrol\enrolment_approval_helper
 */
class core_enrol_enrolment_approval_helper_test extends testcase {

    use enrolment_approval_testcase_trait;

    /**
     * @covers ::user_enrolment_pending
     */
    public function test_user_enrolment_pending(): void {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $course = $instance->course;
        $course_id = $course->id;
        $user = self::getDataGenerator()->create_user();
        $self_enrol = enrol_get_plugin('self');

        // Just a pending enrolment.
        $this->enrol_user_on_instance($instance, $user->id);
        self::assertTrue(enrolment_approval_helper::user_enrolment_pending($course_id, $user->id));

        // Create self enrol instance without approval, and enrol on that - enrolment will be active.
        $enrol_no_application_id = $self_enrol->add_instance($course, []);
        $enrol_no_application = enrol::load_by_id($enrol_no_application_id);
        $self_enrol->enrol_user($enrol_no_application->to_stdClass(), $user->id);

        self::assertEquals(2, enrol_entity::repository()->find_by_enrol_and_course('self', $course_id)->count());
        self::assertFalse(enrolment_approval_helper::user_enrolment_pending($course_id, $user->id));

        // Disable the second (non-approval) self enrol instance - enrolment will be pending again.
        $self_enrol->update_status($enrol_no_application->to_stdClass(), ENROL_USER_SUSPENDED);

        self::assertEquals(2, enrol_entity::repository()->find_by_enrol_and_course('self', $course_id)->count());
        self::assertTrue(enrolment_approval_helper::user_enrolment_pending($course_id, $user->id));

        // Create a second self enrol instance with course approval and mark it approved - the function returns false
        // despite the first self enrolment still pending.
        $workflow2 = $this->create_workflow();
        $enrol_instance3_id = $self_enrol->add_instance($course, []);
        $enrol_instance3 = new enrol_entity($enrol_instance3_id);
        $enrol_instance3->workflow_id = $workflow2->id;
        $enrol_instance3->save();
        $enrol_application_approved = $this->enrol_user_on_instance(new enrol($enrol_instance3), $user->id);
        self::assertEquals(ENROL_USER_PENDING_APPLICATION, $enrol_application_approved->status);
        $enrol_application_approved_entity = $enrol_application_approved->get_entity_copy();
        $enrol_application_approved_entity->status = ENROL_USER_ACTIVE;
        $enrol_application_approved_entity->save();

        self::assertEquals(3, enrol_entity::repository()->find_by_enrol_and_course('self', $course_id)->count());
        self::assertFalse(enrolment_approval_helper::user_enrolment_pending($course_id, $user->id));
    }

    /**
     * @covers ::create_application
     */
    public function test_create_application_no_ja() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $application = enrolment_approval_helper::create_application($instance, new user($user->id));
        $this->assertEquals($user->id, $application->user->id);
        $this->assertEquals($user->id, $application->owner->id);
        $this->assertNull($application->job_assignment_id);
    }

    /**
     * @covers ::create_application
     */
    public function test_create_application_with_job_assignment() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $manager = self::getDataGenerator()->create_user();
        $manager_ja = job_assignment::create(['userid' => $manager->id, 'idnumber' => 'job1']);
        $user_ja = job_assignment::create([
            'userid' => $user->id,
            'idnumber' => 'userjob1',
            'managerjaid' => $manager_ja->id
        ]);
        $this->setUser($user);

        $application = enrolment_approval_helper::create_application($instance, new user($user->id), new \totara_job\entity\job_assignment($user_ja->id));
        $this->assertEquals($user->id, $application->user->id);
        $this->assertEquals($user->id, $application->owner->id);
        $this->assertEquals($user_ja->id, $application->job_assignment_id);
    }

    /**
     * @covers ::get_approval_button_name
     */
    public function test_request_form_button_name_for_withdrawn_application(): void {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $application = enrolment_approval_helper::create_application($instance, new user($user->id));

        // Test with draft.
        self::assertEquals('DRAFT', $application->get_overall_progress());
        self::assertEquals(get_string('application:complete_request', 'core_enrol'), enrolment_approval_helper::get_approval_button_name($application, $user->id));

        // Mock to complete.
        $this->complete_application($application);
        self::assertEquals(get_string('application:create_application', 'core_enrol'), enrolment_approval_helper::get_approval_button_name($application, $user->id));

        // Create a new application, and withdraw.
        $workflow = workflow::load_by_id($instance->workflow_id);
        $application = application::create($workflow->get_active_version(), $workflow->default_assignment, $user->id);
        \mod_approval\model\application\application_action::create($application, $user->id, new withdraw_before_submission());
        self::assertEquals('Complete Request', enrolment_approval_helper::get_approval_button_name($application, $user->id));
    }

    /**
     * @covers ::get_approval_button_name
     */
    public function test_request_form_button_name_for_rejected_application(): void {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        // Create a new application, and submit it.
        $workflow = workflow::load_by_id($instance->workflow_id);
        $application = application::create($workflow->get_active_version(), $workflow->default_assignment, $user->id);
        \mod_approval\model\application\application_action::create($application, $user->id, new submit());

        // Admit rejects application
        \mod_approval\model\application\application_action::create($application, get_admin()->id, new \mod_approval\model\application\action\reject());
        self::assertEquals('Resubmit your application', enrolment_approval_helper::get_approval_button_name($application, $user->id));
    }

    /**
     * @covers ::needs_create_new_application
     */
    public function test_needs_create_new_application(): void {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        // Test with completed application.
        $application = enrolment_approval_helper::create_application($instance, new user($user->id));
        $this->complete_application($application);
        self::assertTrue(enrolment_approval_helper::needs_create_new_application($application));

        // Test with application withdrawn and at form stage.
        $application = enrolment_approval_helper::create_application($instance, new user($user->id));
        \mod_approval\model\application\application_action::create($application, $user->id, new withdraw_before_submission());
        self::assertEquals('WITHDRAWN', $application->get_overall_progress());
        self::assertFalse(enrolment_approval_helper::needs_create_new_application($application));

        // Test with draft application.
        $application = enrolment_approval_helper::create_application($instance, new user($user->id));
        self::assertEquals('DRAFT', $application->get_overall_progress());
        self::assertFalse(enrolment_approval_helper::needs_create_new_application($application));
    }

    /**
     * @covers ::workflow_requires_manager
     */
    public function test_workflow_requires_manager(): void {
        $workflow = $this->create_workflow();
        self::assertFalse(enrolment_approval_helper::workflow_requires_manager($workflow->id));

        $approvals = builder::table('approval_workflow', 'workflow')
            ->select_raw('a.id')
            ->join(['course', 'c'], 'c.id', 'workflow.course_id')
            ->join(['approval', 'a'], 'a.course', 'c.id')
            ->where('workflow.id', $workflow->id)
            ->fetch();

        $approval_ids = array_map(function ($approval) {
            return $approval->id;
        }, $approvals);

        self::assertFalse(builder::table('approval_approver')
            ->where_in('approval_id', $approval_ids)
            ->where('type', relationship::get_code())
            ->exists()
        );
    }

    /**
     * @covers ::plugin_form_add_job_selector
     */
    public function test_plugin_form_add_job_selector(): void {
        $workflow = $this->create_workflow();

        $form = new \HTML_QuickForm();
        self::assertFalse(enrolment_approval_helper::plugin_form_add_job_selector($workflow->id, get_admin()->id,$form));
    }

    /**
     * @covers ::approval_available_for
     */
    public function test_approval_available_for() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        // The test setup creates an enrolment instance and workflow that meet all the rules.
        $this->assertTrue(enrolment_approval_helper::approval_available_for($instance->id, $user->id));

        // Create a second enrolment instance with no workflow id, and test that.
        $enrol = enrol_get_plugin('self');
        $instance2_id = $enrol->add_instance($instance->course, []);
        $instance2 = enrol::load_by_id($instance2_id);
        $this->assertFalse(enrolment_approval_helper::approval_available_for($instance2->id, $user->id));

        // Archive the workfow, with no application.
        $workflow = workflow::load_by_id($instance->workflow_id);
        $workflow->latest_version->archive();
        $this->assertFalse(enrolment_approval_helper::approval_available_for($instance->id, $user->id));
        // Unarchive.
        $workflow->unarchive();

        // Not available if approval workflows not enabled
        $this->assertTrue(enrolment_approval_helper::approval_available_for($instance->id, $user->id));
        advanced_feature::disable('approval_workflows');
        $this->assertFalse(enrolment_approval_helper::approval_available_for($instance->id, $user->id));
    }

    /**
     * @covers ::approval_available_for
     */
    public function test_approval_available_for_archived_workflow_in_flight_application() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $this->enrol_user_on_instance($instance, $user->id);

        $user_enrolment_application = user_enrolment_application::find_with_instance_and_user_id($instance->id, $user->id);
        $this->assertNotEmpty($user_enrolment_application->id);

        // Application is now in-flight, archive the workflow.
        $workflow = workflow::load_by_id($instance->workflow_id);
        $workflow->archive();

        // Still available because application in progress.
        $this->assertTrue(enrolment_approval_helper::approval_available_for($instance->id, $user->id));

        // Mock complete the application.
        $this->complete_application($user_enrolment_application->approval_application);
        $this->assertFalse(enrolment_approval_helper::approval_available_for($instance->id, $user->id));
    }

    /**
     * @covers ::get_application_url_after_non_interactive_enrolment
     */
    public function test_get_application_url_after_non_interactive_enrolment() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        // No pending enrolment.
        $this->assertEquals('', enrolment_approval_helper::get_application_url_after_non_interactive_enrolment($instance->id, $user->id));

        // Enrol the user, check again.
        $this->enrol_user_on_instance($instance, $user->id);
        $user_enrolment_application = user_enrolment_application::find_with_instance_and_user_id($instance->id, $user->id);
        $course = $user_enrolment_application->get_course();
        $return_address = '/enrol/index.php?id=' . $course->id;
        $return_address_label = get_string('back_to_course', 'core_enrol');
        $application_url = application_edit::get_url_for($user_enrolment_application->approval_application_id, $return_address, $return_address_label);
        $this->assertEquals(
            $application_url,
            enrolment_approval_helper::get_application_url_after_non_interactive_enrolment($instance->id, $user->id)
        );

        // Mock complete the application, and try again. The method will create a new application.
        $this->complete_application($user_enrolment_application->approval_application);
        $this->assertNotEquals(
            $application_url,
            enrolment_approval_helper::get_application_url_after_non_interactive_enrolment($instance->id, $user->id)
        );
        $refreshed_user_enrolment_application = user_enrolment_application::find_with_instance_and_user_id($instance->id, $user->id);
        $this->assertNotEquals($user_enrolment_application->approval_application_id, $refreshed_user_enrolment_application->approval_application_id);
        $this->assertEquals(
            application_edit::get_url_for($refreshed_user_enrolment_application->approval_application_id, $return_address, $return_address_label),
            enrolment_approval_helper::get_application_url_after_non_interactive_enrolment($instance->id, $user->id)
        );
    }

    /**
     * @covers ::get_application_url
     */
    public function test_get_application_url() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $this->enrol_user_on_instance($instance, $user->id);
        $user_enrolment_application = user_enrolment_application::find_with_instance_and_user_id($instance->id, $user->id);
        $application = $user_enrolment_application->approval_application;
        $course = $user_enrolment_application->get_course();
        $return_address = '/enrol/index.php?id=' . $course->id;
        $return_address_label = get_string('back_to_course', 'core_enrol');

        // Draft.
        $this->assertEquals(
            application_edit::get_url_for($application->id, $return_address, $return_address_label),
            enrolment_approval_helper::get_application_url($application)
        );

        // Submitted.
        $submission = application_submission::create_or_update(
            $application,
            $user->id,
            form_data::from_json('{"rationale":"yes"}')
        );
        $submission->publish($user->id);
        submit::execute($application, $user->id);

        $this->assertEquals(
            application_view::get_url_for($application->id, $return_address, $return_address_label),
            enrolment_approval_helper::get_application_url($application)
        );
    }

    /**
     * @return void
     */
    public function test_delete_or_archive_applications(): void {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $this->enrol_user_on_instance($instance, $user1->id);
        $user_enrolment_application1 = user_enrolment_application::find_with_instance_and_user_id($instance->id, $user1->id);
        $application1 = $user_enrolment_application1->approval_application;

        $this->enrol_user_on_instance($instance, $user2->id);
        $user_enrolment_application2 = user_enrolment_application::find_with_instance_and_user_id($instance->id, $user2->id);
        $application2 = $user_enrolment_application2->approval_application;

        self::assertEquals($user_enrolment_application1->user_enrolment->enrolid, $user_enrolment_application2->user_enrolment->enrolid);
        self::assertEquals(2, \mod_approval\entity\application\application::repository()->count());
        self::assertTrue($application1->current_state->is_draft());

        enrolment_approval_helper::delete_or_archive_applications($instance->id);

        self::assertFalse(\mod_approval\entity\application\application::repository()->where('id', $application1->id)->exists());
        self::assertFalse(\mod_approval\entity\application\application::repository()->where('id', $application2->id)->exists());
    }
}