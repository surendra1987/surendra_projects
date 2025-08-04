<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Angela Kuznetsova <angela.kuzntetsova@totaralearning.com>
 * @package mod_approval
 */

use core\entity\user;
use core\orm\entity\repository;
use core\orm\query\builder;
use mod_approval\entity\application\application_activity;
use mod_approval\entity\application\application_submission as application_submission_entity;
use mod_approval\model\application\action\approve;
use mod_approval\model\application\action\reject;
use mod_approval\model\application\action\submit;
use mod_approval\model\application\activity\creation as creation_activity;
use mod_approval\model\application\activity\stage_started as stage_started_activity;
use mod_approval\model\application\activity\stage_submitted as stage_submitted_activity;
use mod_approval\model\application\application;
use mod_approval\model\application\application_action;
use mod_approval\model\application\application_submission;
use mod_approval\model\assignment\assignment_type;
use mod_approval\model\form\form_data;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_type\approvals;
use mod_approval\model\workflow\stage_type\finished;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\workflow;
use mod_approval\model\workflow\workflow_stage;
use mod_approval\testing\application_generator_object;
use mod_approval\testing\assignment_generator_object;
use mod_approval\testing\formview_generator_object;
use mod_approval\testing\workflow_generator_object;
use totara_hierarchy\testing\generator as hierarchy_generator;

require_once(__DIR__ . '/testcase.php');

/**
 * @coversDefaultClass \mod_approval\model\application\application_submission
 *
 * @group approval_workflow
 */
class mod_approval_application_submission_model_test extends mod_approval_testcase {

    /**
     * Gets the generator instance
     *
     * @return \mod_approval\testing\generator
     */
    protected function generator(): \mod_approval\testing\generator {
        return \mod_approval\testing\generator::instance();
    }

    /**
     * @return array
     * @throws JsonException
     * @throws coding_exception
     */
    protected function create_application(): array {
        $this->setAdminUser();
        $generator = $this->generator();
        $application_repository = application_submission_entity::repository();

        $workflow_type = $generator->create_workflow_type('test');

        // Create a form and version
        $form_version = $generator->create_form_and_version();
        $form = $form_version->form;

        // Create a workflow and version
        $workflow_go = new workflow_generator_object($workflow_type->id, $form->id, $form_version->id, status::DRAFT);
        $workflow_version = $generator->create_workflow_and_version($workflow_go);
        $workflow = $workflow_version->workflow;

        $workflow_stage = $generator->create_workflow_stage($workflow_version->id, 'Test Stage', form_submission::get_enum());

        $formview_go = new formview_generator_object('request', $workflow_stage->id);
        $generator->create_formview($formview_go);

        $generator->create_approval_level($workflow_stage->id, 'Level 1', 1);

        // Generate a simple organisation hierarchy
        $hierarchy_generator = hierarchy_generator::instance();
        $framework = $hierarchy_generator->create_framework('organisation');
        $organisation = $hierarchy_generator->create_org(['frameworkid' => $framework->id]);

        // Create an assignment
        $assignment_go = new assignment_generator_object(
            $workflow->course_id,
            assignment_type\organisation::get_code(),
            $organisation->id
        );
        $assignment_go->is_default = true;
        $assignment = $generator->create_assignment($assignment_go);

        // Create an application generator object
        $application_go = new application_generator_object($workflow_version->id, $form_version->id, $assignment->id);
        $application = application::load_by_entity($generator->create_application($application_go));

        $workflow_stage = workflow_stage::load_by_entity($workflow_stage);

        return [
            'application_repository' => $application_repository,
            'application' => $application,
            'workflow_stage' => $workflow_stage
        ];
    }

    /**
     * @param application|null $application
     * @return array
     * @throws JsonException
     * @throws \mod_approval\exception\malicious_form_data_exception
     * @throws coding_exception
     */
    protected function create_application_submission(application $application = null): array {
        if (empty($application)) {
            $application = $this->create_application()['application'];
        }

        // Create a user
        $user = $this->create_user();
        $this->setUser($user);
        $form_data = form_data::from_json('{"request":"hurray!"}');
        $application_submission = application_submission::create_or_update($application, $user->id, $form_data);

        return [
            'user' => $user,
            'form_data' => $form_data,
            'application_submission' => $application_submission
        ];
    }

    /**
     * @covers ::create_or_update
     * @throws coding_exception
     */
    public function test_create_or_update() {
        $time = time();

        [
            'application_repository' => $application_repository,
            'application' => $application,
            'workflow_stage' => $workflow_stage
        ] = $this->create_application();

        [
            'user' => $user,
            'form_data' => $form_data,
            'application_submission' => $application_submission
        ] = $this->create_application_submission($application);

        // We have a repository
        $this->assertEquals(1, $application_repository->count());
        $this->assertInstanceOf(get_class($application_submission), $application_submission);
        $this->assertEquals($user->id, $application_submission->user_id);
        $this->assertEquals($application->id, $application_submission->application_id);
        $this->assertEquals($workflow_stage->id, $application_submission->workflow_stage_id);
        $this->assertEquals($form_data->to_json(), $application_submission->form_data);
        $this->assertGreaterThanOrEqual($time, $application_submission->created);
        $this->assertLessThanOrEqual($application_submission->updated, $application_submission->created);
    }

    /**
     * Test update_assignment_by_id function
     *
     * @return void
     */
    public function test_update_assignment_by_id() {
        [
            'application' => $application, 
            'workflow_stage' => $workflow_stage
        ] = $this->create_application();
        $hierarchy_generator = hierarchy_generator::instance();
        $generator = $this->generator();
        $second_framework = $hierarchy_generator->create_framework('organisation');
        $second_organisation = $hierarchy_generator->create_org(['frameworkid' => $second_framework->id]);
        $second_assignment = $generator->create_assignment(new assignment_generator_object(
            $workflow_stage->workflow_version->workflow->course_id,
            assignment_type\organisation::get_code(),
            $second_organisation->id
        ));
        $application->update_assignment_by_id($second_assignment->id);
        $application_db_record = builder::table(mod_approval\entity\application\application::TABLE)
            ->select('approval_id')
            ->where('id', $application->id)
            ->one();
        $this->assertEquals($second_assignment->id, $application_db_record->approval_id);
    }

    /**
     * @covers ::refresh
     * @throws coding_exception
     */
    public function test_refresh(): void {
        $application_submission = $this->create_application_submission()['application_submission'];
        $this->assertFalse($application_submission->superseded);
        builder::table(application_submission_entity::TABLE)->update(['superseded' => '1']);
        $application_submission->refresh();
        $this->assertTrue($application_submission->superseded);
    }

    /**
     * @covers ::delete
     * @throws coding_exception
     */
    public function test_delete(): void {
        $application_submission = $this->create_application_submission()['application_submission'];
        $this->assertNotEmpty($application_submission->id);
        $application_submission->delete();
        $this->assertEmpty($application_submission->id);
    }

    public static function data_clone(): array {
        return [[false], [true]];
    }

    /**
     * @param bool $superseded
     * @dataProvider data_clone
     * @covers ::clone
     */
    public function test_clone(bool $superseded): void {
        $source = $this->create_application_submission()['application_submission'];
        builder::table(application_submission_entity::TABLE)->update(['superseded' => $superseded, 'submitted' => 1234]);
        $application = application::load_by_entity($this->generator()->create_application(new application_generator_object(
            $source->application->workflow_version->id,
            $source->application->form_version_id,
            $source->application->approval_id
        )));
        $this->waitForSecond();
        $destination = $source->clone($application);
        $this->assertEquals($source->user_id, $destination->user_id);
        $this->assertEquals($source->workflow_stage_id, $destination->workflow_stage_id);
        $this->assertNull($destination->submitted);
        $this->assertFalse($destination->superseded);
        $this->assertEquals($source->form_data, $destination->form_data);
        $this->assertGreaterThan($source->created, $destination->created);
        $this->assertGreaterThan($source->updated, $destination->updated);
    }

    public function test_submit_supersedes_drafts(): void {
        // Create a submitter
        $submitter_user = new user($this->getDataGenerator()->create_user());

        // Act as admin sometimes.
        $this->setAdminUser();
        $admin_user = user::logged_in();

        // Create a workflow and application.
        $application = $this->create_application_for_user();

        // Check the application_submission repository.
        $this->assertEquals(0, application_submission_entity::repository()->count());

        // Save a draft as submitter_user.
        $submission = application_submission::create_or_update(
            $application,
            $submitter_user->id,
            form_data::from_json('{"kia":"kaha"}')
        );
        $this->assertEquals(1, application_submission_entity::repository()->count());

        // Save a draft as admin.
        $submission = application_submission::create_or_update(
            $application,
            $admin_user->id,
            form_data::from_json('{"kia":"kaha"}')
        );
        $this->assertEquals(2, application_submission_entity::repository()->count());

        // The new one is a draft, un-superseded submissions.
        $this->assertEquals(1, application_submission_entity::repository()
            ->where_null('submitted')
            ->where('superseded', '=', 0)
            ->count()
        );

        // The old one is a draft, superseded.
        $this->assertEquals(1, application_submission_entity::repository()
            ->where_null('submitted')
            ->where('superseded', '=', 1)
            ->count()
        );

        // Update the submitter user's draft.
        $submission = application_submission::create_or_update(
            $application,
            $submitter_user->id,
            form_data::from_json('{"kia":"ora"}')
        );
        // Now 3 submissions.
        $this->assertEquals(3, application_submission_entity::repository()->count());
        // One draft, un-superseded submission.
        $this->assertEquals(1, application_submission_entity::repository()
            ->where_null('submitted')
            ->where('superseded', '=', 0)
            ->count()
        );
        // Two draft, superseded submissions.
        $this->assertEquals(2, application_submission_entity::repository()
            ->where_null('submitted')
            ->where('superseded', '=', 1)
            ->count()
        );

        // Submit the submitter's draft.
        $submission->publish($submitter_user->id);
        submit::execute($application, $submitter_user->id);

        // Still 3 submissions.
        $this->assertEquals(3, application_submission_entity::repository()->count());

        // But only two are draft, and they are superseded.
        $this->assertEquals(2, application_submission_entity::repository()
            ->where_null('submitted')
            ->where('superseded', '=', 1)
            ->count()
        );

        // The other is submitted, and not superseded.
        $submission = application_submission_entity::repository()->where_not_null('submitted')->one(true);
        $this->assertEquals(0, $submission->superseded);

        // It has the submitter's latest answer.
        $this->assertEquals('{"kia":"ora"}', $submission->form_data);
    }

    /**
     * @covers ::publish
     */
    public function test_submit_supersedes_reject_actions(): void {
        // Create a submitter.
        $submitter_user = new user($this->getDataGenerator()->create_user());

        // Act as admin sometimes.
        $this->setAdminUser();
        $admin_user = user::logged_in();

        // Create application
        $application = $this->create_application_for_user();

        // Submit the application.
        $submission_1 = application_submission::create_or_update(
            $application,
            $submitter_user->id,
            form_data::from_json('{"kia":"kaha"}')
        );
        $submission_1->publish($submitter_user->id);
        submit::execute($application, $submitter_user->id);

        // One submission, not draft not superseded.
        $this->assertEquals(1, application_submission_entity::repository()->count());
        $submission_1->refresh(true);
        $this->assertNotNull($submission_1->submitted);
        $this->assertEquals(false, $submission_1->superseded);

        // Reject application as admin.
        reject::execute($application, $admin_user->id);
        $application->refresh(true);

        // Still one submission, because 'reject' doesn't change the application answers.
        $this->assertEquals(1, application_submission_entity::repository()->count());
        $submission_1->refresh(true);
        $this->assertNotNull($submission_1->submitted);
        $this->assertEquals(false, $submission_1->superseded);

        // Submit again with different data.
        $form_data = form_data::from_json('{"kia":"karana"}');
        $submission_2 = application_submission::create_or_update(
            $application,
            $submitter_user->id,
            $form_data
        );

        // Now there are two submissions. One unsuperseded submitted, and one unsuperseded draft.
        $this->assertEquals(2, application_submission_entity::repository()->count());
        $this->assertEquals(2, application_submission_entity::repository()
            ->where('superseded', '=', 0)
            ->count()
        );
        $this->assertEquals(1, application_submission_entity::repository()
            ->where_null('submitted')
            ->count()
        );

        $submission_2->publish($submitter_user->id);
        submit::execute($application, $submitter_user->id);
        $application->refresh();

        // Now there are still two submissions. One superseded submitted, and one unsuperseded submitted.
        $this->assertEquals(2, application_submission_entity::repository()->count());

        // One previous submission.
        $previous_submission = application_submission_entity::repository()
            ->where('superseded', '=', true)
            ->where('application_id', '=', $application->id)
            ->where('user_id', '=', $submitter_user->id)
            ->get();
        self::assertCount(1, $previous_submission);

        // One current submission
        $current_submission = application_submission_entity::repository()
            ->where('superseded', '=', false)
            ->where('application_id', '=', $application->id)
            ->where('user_id', '=', $submitter_user->id)
            ->one();

        $last_submission = application_submission::load_by_entity($current_submission);
        // Check correct form_data was saved
        $this->assertEquals($form_data->to_json(), $last_submission->form_data);

        // Check that there is a superseded reject action.
        $this->assertCount(1, $application->actions);
        /** @var application_action $action*/
        $action = $application->actions->first();
        $this->assertEquals(reject::get_code(), $action->code);
        $this->assertTrue($action->superseded);
    }

    /**
     * @covers ::publish
     */
    public function test_publish(): void {
        $this->setAdminUser();
        $application_submission = $this->create_submission_for_user_input();

        // Submission and application are not submitted.
        $this->assertFalse($application_submission->is_published());
        $this->assertFalse($application_submission->application->current_state->is_stage_type(approvals::get_code()));

        $activity_repository = function () use ($application_submission): repository {
            return application_activity::repository()->where('application_id', $application_submission->application_id);
        };

        // Submit submission without transition (no stage_submitted activity).
        $this->assertEquals(2, $activity_repository()->count());
        $this->assertEquals(1, $activity_repository()->where('activity_type', creation_activity::get_type())->count());
        $this->assertEquals(1, $activity_repository()->where('activity_type', stage_started_activity::get_type())->count());
        $this->assertEquals(0, $activity_repository()->where('activity_type', stage_submitted_activity::get_type())->count());
        $application_submission->publish(user::logged_in()->id);
        $this->assertEquals(2, $activity_repository()->count());
        $this->assertEquals(1, $activity_repository()->where('activity_type', creation_activity::get_type())->count());
        $this->assertEquals(1, $activity_repository()->where('activity_type', stage_started_activity::get_type())->count());
        $this->assertEquals(0, $activity_repository()->where('activity_type', stage_submitted_activity::get_type())->count());

        // Submission is submitted.
        $this->assertTrue($application_submission->is_published());

        // Application is submitted.
        $this->assertFalse($application_submission->application->current_state->is_stage_type(approvals::get_code()));
    }

    /**
     * @return application_submission
     */
    private function create_submission_for_user_input(): application_submission {
        $application = $this->create_application_for_user();
        return application_submission::create_or_update(
            $application,
            user::logged_in()->id,
            form_data::from_json('{"kia":"ora"}')
        );
    }

    /**
     * @covers ::publish
     */
    public function test_supersede_submissions(): void {
        $mod_approval_generator = $this->generator();
        $submitter_user = self::getDataGenerator()->create_user();
        $approver_user = self::getDataGenerator()->create_user();
        $resetter_user = self::getDataGenerator()->create_user();

        // Create a workflow with two stages containing one approval level each.
        $workflow_type = $mod_approval_generator->create_workflow_type('test workflow type');

        $form_version = $mod_approval_generator->create_form_and_version();
        $form = $form_version->form;

        $workflow_go = new workflow_generator_object($workflow_type->id, $form->id, $form_version->id, status::DRAFT);
        $workflow_version = $mod_approval_generator->create_workflow_and_version($workflow_go);

        // Stage 1
        $workflow_stage1 = $mod_approval_generator->create_workflow_stage(
            $workflow_version->id,
            'Stage 1',
            form_submission::get_enum()
        );
        $formview_go = new formview_generator_object('agency_code', $workflow_stage1->id);
        $mod_approval_generator->create_formview($formview_go);

        // Stage 2
        $workflow_stage2 = $mod_approval_generator->create_workflow_stage(
            $workflow_version->id,
            'Stage 2',
            approvals::get_enum()
        );

        // Stage 3
        $workflow_stage3 = $mod_approval_generator->create_workflow_stage(
            $workflow_version->id,
            'Stage 3',
            form_submission::get_enum()
        );
        $formview_go = new formview_generator_object('request_status', $workflow_stage3->id);
        $mod_approval_generator->create_formview($formview_go);

        // Stage 4
        $workflow_stage4 = $mod_approval_generator->create_workflow_stage(
            $workflow_version->id,
            'Stage 4',
            approvals::get_enum()
        );

        // Stage 5
        $workflow_stage5 = $mod_approval_generator->create_workflow_stage(
            $workflow_version->id,
            'Stage 5',
            form_submission::get_enum()
        );
        $formview_go = new formview_generator_object('request_status', $workflow_stage5->id);
        $mod_approval_generator->create_formview($formview_go);

        // End stage.
        $mod_approval_generator->create_workflow_stage(
            $workflow_version->id,
            'End',
            finished::get_enum()
        );
        $workflow = workflow::load_by_entity($workflow_version->workflow);
        $workflow->publish($workflow->get_latest_version());

        // Create applications.
        $this->setAdminUser();
        $application = $this->create_application_for_user_on($workflow);
        $control_application = $this->create_application_for_user_on($workflow);

        // Submit stage 1 - creates submissions.
        $form_data = form_data::from_json('{"agency_code":"hurray!"}');
        $submission = application_submission::create_or_update(
            $application,
            $submitter_user->id,
            $form_data
        );
        $submission->publish($submitter_user->id);
        submit::execute($application, $submitter_user->id);

        $control_form_data = form_data::from_json('{"agency_code":"oh no!"}');
        $control_submission = application_submission::create_or_update(
            $control_application,
            $submitter_user->id,
            $control_form_data
        );
        $control_submission->publish($submitter_user->id);
        submit::execute($control_application, $submitter_user->id);

        // Approve the first level of stage 2 - creates an action.
        approve::execute($application, $approver_user->id);
        approve::execute($control_application, $approver_user->id);

        // Submit stage 3 - creates submissions.
        $form_data = form_data::from_json('{"request_status":"yeah okay!"}');
        $submission = application_submission::create_or_update(
            $application,
            $submitter_user->id,
            $form_data
        );
        $submission->publish($submitter_user->id);
        submit::execute($application, $submitter_user->id);

        $control_form_data = form_data::from_json('{"request_status":"woops!"}');
        $control_submission = application_submission::create_or_update(
            $control_application,
            $submitter_user->id,
            $control_form_data
        );
        $control_submission->publish($submitter_user->id);
        submit::execute($control_application, $submitter_user->id);

        // Approve the first level of stage 4 - creates an action.
        approve::execute($application, $approver_user->id);
        approve::execute($control_application, $approver_user->id);

        // Verify that stage one has a superseded, submitted submission.
        $submissions_stage1 = application_submission_entity::repository()
            ->where('superseded', '=', true)
            ->where_not_null('submitted')
            ->where('application_id', '=', $application->id)
            ->where('workflow_stage_id', '=', $workflow_stage1->id)
            ->get();
        self::assertCount(1, $submissions_stage1);
        // Verify same for stage 3, but submission is un-superseded.
        $submissions_stage3 = application_submission_entity::repository()
            ->where('superseded', '=', false)
            ->where_not_null('submitted')
            ->where('application_id', '=', $application->id)
            ->where('workflow_stage_id', '=', $workflow_stage3->id)
            ->get();
        self::assertCount(1, $submissions_stage3);

        // And same for the control application.
        $control_submissions_stage1 = application_submission_entity::repository()
            ->where('superseded', '=', true)
            ->where_not_null('submitted')
            ->where('application_id', '=', $control_application->id)
            ->where('workflow_stage_id', '=', $workflow_stage1->id)
            ->get();
        self::assertCount(1, $control_submissions_stage1);
        $control_submissions_stage3 = application_submission_entity::repository()
            ->where('superseded', '=', false)
            ->where_not_null('submitted')
            ->where('application_id', '=', $control_application->id)
            ->where('workflow_stage_id', '=', $workflow_stage3->id)
            ->get();
        self::assertCount(1, $control_submissions_stage3);

        // There are four total submissions.
        self::assertEquals(4, application_submission_entity::repository()->count());
    }
}
