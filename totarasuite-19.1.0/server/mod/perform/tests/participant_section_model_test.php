<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Jaron Steenson <jaron.steenson@totaralearning.com>
 * @package mod_perform
 * @category test
 */

use core\orm\entity\entity;
use core\orm\query\builder;
use core_phpunit\testcase;
use mod_perform\constants as mod_perform_constants;
use mod_perform\constants;
use mod_perform\entity\activity\element as element_entity;
use mod_perform\entity\activity\element_response;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section as participant_section_entity;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\element;
use mod_perform\models\activity\participant_instance as participant_instance_model;
use mod_perform\models\response\participant_section;
use mod_perform\state\participant_instance\closed;
use mod_perform\state\participant_section\closed as participant_section_closed;
use mod_perform\state\participant_section\progress_not_applicable;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\testing\generator as perform_generator;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use performelement_linked_review\entity\linked_review_content_response;
use performelement_linked_review\models\linked_review_content;
use mod_perform\state\participant_section\complete as participant_section_complete;

/**
 * @group perform
 */
class mod_perform_participant_section_model_test extends testcase {

    use mod_perform\testing\multisection_activity_trait;

    public function test_get_participant_section_multiple_answerable_participant_instances(): void {
        self::setAdminUser();

        $data_generator = self::getDataGenerator();

        /** @var perform_generator $perform_generator */
        $perform_generator = $data_generator->get_plugin_generator('mod_perform');

        $subject_user = $data_generator->create_user();
        $manager_appraiser_user = $data_generator->create_user();

        [
            $subject_section,
            $manager_section,
            $appraiser_section
        ] = $perform_generator->create_section_with_combined_manager_appraiser($subject_user, $manager_appraiser_user);

        $subject_answerable_participants = (new participant_section($subject_section))->get_answerable_participant_instances();
        $manager_answerable_participants = (new participant_section($manager_section))->get_answerable_participant_instances();
        $appraiser_answerable_participants = (new participant_section($appraiser_section))->get_answerable_participant_instances();

        self::assertCount(1, $subject_answerable_participants);
        self::assertSame($subject_user->id, $subject_answerable_participants->first()->participant_id);

        self::assertCount(2, $manager_answerable_participants);
        self::assertCount(2, $appraiser_answerable_participants);

        self::assertEquals(
            $manager_answerable_participants,
            $appraiser_answerable_participants,
            'Both manager and appraiser should have the same answerable participants'
        );

        self::assertNotEquals(
            $manager_answerable_participants->first()->id,
            $manager_answerable_participants->last()->id,
            'Should be two distinct participant instance records for the manager answerable participants'
        );

        self::assertNotEquals(
            $appraiser_answerable_participants->first()->id,
            $appraiser_answerable_participants->last()->id,
            'Should be two distinct participant instance records for the appraiser answerable participants'
        );

        self::assertNotEquals(
            $manager_answerable_participants->first()->id,
            $manager_answerable_participants->last()->id,
            'Should be two distinct participant instance records for the manager answerable participants'
        );

        $all = $appraiser_answerable_participants->all();
        array_push($all, ...$manager_answerable_participants);

        foreach ($all as $answerable_participant) {
            self::assertEquals($manager_appraiser_user->id, $answerable_participant->participant_id);
        }
    }

    /**
     * @param string[] $expected_responses_are_visible_to
     * @dataProvider get_responses_are_visible_to_provider
     */
    public function test_get_responses_are_visible_to(array $expected_responses_are_visible_to): void {
        [
            $manager_section,
            $appraiser_section,
            $subject_section,
        ] = $this->set_up_responses_are_visible($expected_responses_are_visible_to);

        // All participant section should have their responses visible to the same relationships.
        $subject_responses_are_visible_to = (new participant_section($subject_section))
            ->get_responses_are_visible_to();

        $manager_responses_are_visible_to = (new participant_section($manager_section))
            ->get_responses_are_visible_to();

        $appraiser_responses_are_visible_to = (new participant_section($appraiser_section))
            ->get_responses_are_visible_to();

        // Note we are using assertEquals because the ordering matters here.
        self::assertEquals(
            $expected_responses_are_visible_to,
            $subject_responses_are_visible_to->pluck('idnumber')
        );

        self::assertEquals(
            $expected_responses_are_visible_to,
            $manager_responses_are_visible_to->pluck('idnumber')
        );

        self::assertEquals(
            $expected_responses_are_visible_to,
            $appraiser_responses_are_visible_to->pluck('idnumber')
        );
    }

    public static function get_responses_are_visible_to_provider(): array {
        return [
            'Responses are not visible to anyone' => [[]],
            'Responses are visible to everyone' => [
                [constants::RELATIONSHIP_SUBJECT, constants::RELATIONSHIP_MANAGER, constants::RELATIONSHIP_APPRAISER]
            ],
            'Responses are visible to just managers' => [
                [constants::RELATIONSHIP_MANAGER]
            ],
            'Responses are visible to just appraisers' => [
                [constants::RELATIONSHIP_APPRAISER]
            ],
            'Responses are visible to just subjects' => [
                [constants::RELATIONSHIP_SUBJECT]
            ],
        ];
    }

    /**
     * @param string[] $responses_are_visible_to
     * @param bool $expected_result
     * @throws coding_exception
     * @dataProvider can_view_others_responses_provider
     */
    public function test_can_view_others_responses(array $responses_are_visible_to, bool $expected_result): void {
        [$manager_section, $appraiser_section, $subject_section] = $this->set_up_responses_are_visible($responses_are_visible_to);

        $subject_can_view_others_responses = (new participant_section($subject_section))
            ->can_view_others_responses();

        self::assertEquals(
            $expected_result,
            $subject_can_view_others_responses
        );
    }

    public static function can_view_others_responses_provider(): array {
        // All cases are acting as subject.
        return [
            'Responses are not visible to anyone' => [
                [], false
            ],
            'Responses are visible to everyone' => [
                [constants::RELATIONSHIP_SUBJECT, constants::RELATIONSHIP_MANAGER, constants::RELATIONSHIP_APPRAISER], true
            ],
            'Responses are visible to just managers' => [
                [constants::RELATIONSHIP_MANAGER], false
            ],
            'Responses are visible to just appraisers' => [
                [constants::RELATIONSHIP_APPRAISER], false
            ],
            'Responses are visible to just subjects' => [
                [constants::RELATIONSHIP_SUBJECT], true
            ],
        ];
    }

    private function set_up_responses_are_visible(array $expected_responses_are_visible_to): array {
        self::setAdminUser();
        $data_generator = self::getDataGenerator();

        $subject_user = $data_generator->create_user();
        $manager_appraiser_user = $data_generator->create_user();

        /** @var perform_generator $perform_generator */
        $perform_generator = $data_generator->get_plugin_generator('mod_perform');

        $subject_instance = $perform_generator->create_subject_instance([
            'activity_name' => 'activity 1',
            'subject_is_participating' => false, // The subject actually is participating, but we will create the instance below.
            'subject_user_id' => $subject_user->id,
            'other_participant_id' => null,
            'include_questions' => false,
        ]);

        $activity = new activity($subject_instance->activity());

        $section = $perform_generator->create_section($activity, ['title' => 'Part one']);

        $manager_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => constants::RELATIONSHIP_MANAGER],
            in_array(constants::RELATIONSHIP_MANAGER, $expected_responses_are_visible_to, true)
        );

        $appraiser_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => constants::RELATIONSHIP_APPRAISER],
            in_array(constants::RELATIONSHIP_APPRAISER, $expected_responses_are_visible_to, true)
        );

        $subject_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => constants::RELATIONSHIP_SUBJECT],
            in_array(constants::RELATIONSHIP_SUBJECT, $expected_responses_are_visible_to, true)
        );

        $element = $perform_generator->create_element(['title' => 'Question one']);
        $perform_generator->create_section_element($section, $element);

        $manager_section = $perform_generator->create_participant_instance_and_section(
            $activity,
            $manager_appraiser_user,
            $subject_instance->id,
            $section,
            $manager_section_relationship->core_relationship_id
        );

        $appraiser_section = $perform_generator->create_participant_instance_and_section(
            $activity,
            $manager_appraiser_user,
            $subject_instance->id,
            $section,
            $appraiser_section_relationship->core_relationship_id
        );

        $subject_section = $perform_generator->create_participant_instance_and_section(
            $activity,
            $subject_user,
            $subject_instance->id,
            $section,
            $subject_section_relationship->core_relationship_id
        );

        return [$manager_section, $appraiser_section, $subject_section];
    }

    public static function will_completion_close_participant_instance_provider(): array {
        return [
            [false, false, false],
            [false, true, true],
            [true, true, true],
        ];
    }

    /**
     * @dataProvider will_completion_close_participant_instance_provider
     * @param bool $close_on_section_submission
     * @param bool $close_on_completion
     * @param bool $expected_result_for_last_incomplete_section
     * @return void
     */
    public function test_will_completion_close_participant_instance(
        bool $close_on_section_submission,
        bool $close_on_completion,
        bool $expected_result_for_last_incomplete_section
    ): void {
        /** @var participant_section $participant_section1 */
        /** @var participant_section $participant_section2 */
        /** @var participant_section $participant_section3 */
        [
            $activity,
            $participant_instance,
            $participant_section1,
            $participant_section2,
            $participant_section3,
        ] = $this->create_multi_section_activity();

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => $close_on_section_submission,
            activity_setting::CLOSE_ON_COMPLETION => $close_on_completion,
        ]);

        $this->assert_will_completion_close_participant_instance($participant_section1, false);
        $this->assert_will_completion_close_participant_instance($participant_section2, false);
        $this->assert_will_completion_close_participant_instance($participant_section3, false);

        $this->complete_section($participant_section1);
        $this->assert_will_completion_close_participant_instance($participant_section1, false);
        $this->assert_will_completion_close_participant_instance($participant_section2, false);
        $this->assert_will_completion_close_participant_instance($participant_section3, false);

        $this->complete_section($participant_section2);
        $this->assert_will_completion_close_participant_instance($participant_section1, false);
        $this->assert_will_completion_close_participant_instance($participant_section2, false);
        $this->assert_will_completion_close_participant_instance($participant_section3, $expected_result_for_last_incomplete_section);
    }

    public function test_will_completion_close_participant_instance_for_closed_participant_instance(): void {
        /** @var participant_section $participant_section1 */
        /** @var participant_section $participant_section2 */
        /** @var participant_section $participant_section3 */
        [
            $activity,
            $participant_instance,
            $participant_section1,
            $participant_section2,
            $participant_section3,
        ] = $this->create_multi_section_activity();

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
            activity_setting::CLOSE_ON_COMPLETION => true,
        ]);

        $this->complete_section($participant_section1);
        $this->complete_section($participant_section2);
        $this->assert_will_completion_close_participant_instance($participant_section3, true);

        // Mark participant instance as closed directly in DB.
        builder::table(participant_instance::TABLE)
            ->where('id', $participant_instance->id)
            ->update(['availability' => closed::get_code()]);

        $this->assert_will_completion_close_participant_instance($participant_section3, false);
    }

    public function test_will_completion_close_participant_instance_for_view_only_participant_section(): void {
        /** @var participant_section $participant_section1 */
        /** @var participant_section $participant_section2 */
        /** @var participant_section $participant_section3 */
        [
            $activity,
            $participant_instance,
            $participant_section1,
            $participant_section2,
            $participant_section3,
        ] = $this->create_multi_section_activity();

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
            activity_setting::CLOSE_ON_COMPLETION => true,
        ]);

        $this->complete_section($participant_section1);
        $this->complete_section($participant_section2);
        $this->assert_will_completion_close_participant_instance($participant_section3, true);

        // Mark participant section as 'progress not applicable' (view only) directly in DB.
        builder::table(participant_section_entity::TABLE)
            ->where('id', $participant_section3->id)
            ->update(['progress' => progress_not_applicable::get_code()]);

        $this->assert_will_completion_close_participant_instance($participant_section3, false);
    }

    public function test_will_completion_close_participant_instance_when_other_sections_are_view_only(): void {
        /** @var participant_section $participant_section1 */
        /** @var participant_section $participant_section2 */
        /** @var participant_section $participant_section3 */
        [
            $activity,
            $participant_instance,
            $participant_section1,
            $participant_section2,
            $participant_section3,
        ] = $this->create_multi_section_activity();

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
            activity_setting::CLOSE_ON_COMPLETION => true,
        ]);

        $this->assert_will_completion_close_participant_instance($participant_section3, false);

        // Mark participant sections 1 'progress not applicable' (view only) directly in DB.
        builder::table(participant_section_entity::TABLE)
            ->where('id', $participant_section1->id)
            ->update(['progress' => progress_not_applicable::get_code()]);

        $this->assert_will_completion_close_participant_instance($participant_section3, false);

        // Mark participant sections 2 'progress not applicable' (view only) directly in DB.
        builder::table(participant_section_entity::TABLE)
            ->where('id', $participant_section2->id)
            ->update(['progress' => progress_not_applicable::get_code()]);

        // Completing section 3 should lead to participant instance closure now.
        $this->assert_will_completion_close_participant_instance($participant_section3, true);
    }

    public function test_will_completion_close_participant_instance_with_changed_settings(): void {
        /** @var participant_section $participant_section1 */
        /** @var participant_section $participant_section2 */
        /** @var participant_section $participant_section3 */
        [
            $activity,
            $participant_instance,
            $participant_section1,
            $participant_section2,
            $participant_section3,
        ] = $this->create_multi_section_activity();

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::CLOSE_ON_COMPLETION => false,
        ]);

        $this->complete_section($participant_section1);
        $this->complete_section($participant_section2);
        $this->complete_section($participant_section3);
        $this->assert_will_completion_close_participant_instance($participant_section1, false);
        $this->assert_will_completion_close_participant_instance($participant_section2, false);
        $this->assert_will_completion_close_participant_instance($participant_section3, false);

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::CLOSE_ON_COMPLETION => true,
        ]);

        // Sections are complete, but they can be re-completed which will lead to closure now.
        $this->assert_will_completion_close_participant_instance($participant_section1, true);
        $this->assert_will_completion_close_participant_instance($participant_section2, true);
        $this->assert_will_completion_close_participant_instance($participant_section3, true);

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
            activity_setting::CLOSE_ON_COMPLETION => true,
        ]);

        $this->assert_will_completion_close_participant_instance($participant_section1, true);
        $this->assert_will_completion_close_participant_instance($participant_section2, true);
        $this->assert_will_completion_close_participant_instance($participant_section3, true);
    }

    /**
     * @param participant_section $participant_section
     * @param bool $expected_result
     * @return void
     */
    private function assert_will_completion_close_participant_instance(participant_section $participant_section, bool $expected_result): void {
        // Have to completely refresh the model.
        $participant_section_refreshed = participant_section::load_by_id($participant_section->id);
        static::assertSame($expected_result, $participant_section_refreshed->get_will_completion_close_participant_instance());
    }

    public function test_has_required_element(): void {
        /** @var participant_section $participant_section */
        [, , $participant_section] = $this->create_multi_section_activity();
        $participant_section->refresh();

        static::assertFalse($participant_section->get_has_required_element());
    }

    public function test_has_required_element_parent_no_response(): void {
        /** @var participant_section $participant_section */
        [, , $participant_section] = $this->create_multi_section_activity();
        $section_elements = $participant_section->section->section_elements;
        foreach ($section_elements as $section_element) {
            $element = $section_element->element;
            $element->update_details(
                $element->title,
                $element->data,
                true,
                $element->get_identifier()
            );
        }
        $participant_section->refresh();

        static::assertTrue($participant_section->get_has_required_element());
    }

    public function test_has_required_element_parent_with_response(): void {
        /** @var participant_section $participant_section */
        [, , $participant_section] = $this->create_multi_section_activity();
        $section_elements = $participant_section->section->section_elements;
        foreach ($section_elements as $section_element) {
            $element = $section_element->element;
            $element->update_details(
                $element->title,
                $element->data,
                true,
                $element->get_identifier()
            );
            $element_response = new element_response();
            $element_response->participant_instance_id = $participant_section->participant_instance_id;
            $element_response->section_element_id = $section_element->id;
            $element_response->response_data = json_encode('3');
            $element_response->save();
        }
        $participant_section->refresh();

        // Whether it has a response on it should not matter.
        static::assertTrue($participant_section->get_has_required_element());
    }

    public function test_with_optional_confirmed_review_item_and_optional_child_element(): void {
        $participant_section_model = $this->setup_section_and_review_item_with_child_element(false, true);

        // Assert - should be false because the review item content element has been confirmed but its child element is not required.
        static::assertFalse($participant_section_model->get_has_required_element());
    }

    public function test_with_optional_confirmed_review_item_and_required_child_element(): void {
        $participant_section_model = $this->setup_section_and_review_item_with_child_element(true, true);

        // Assert - should be true because the review item content element has been confirmed and its child element is required.
        static::assertTrue($participant_section_model->get_has_required_element());
    }

    public function test_with_optional_review_item_not_confirmed_and_required_child_element(): void {
        $participant_section_model = $this->setup_section_and_review_item_with_child_element(true, false);

        // Assert - should be false because the review item content element has not been confirmed, although it has a child element
        // that is required.
        static::assertFalse($participant_section_model->get_has_required_element());
    }

    protected function setup_section_and_review_item_with_child_element(
        bool $child_element_required,
        bool $confirmed_review_content
    ): participant_section {
        self::setAdminUser();
        $subject_user = self::getDataGenerator()->create_user(['firstname' => 'Subject', 'lastname' => 'User']);

        $perform_generator = perform_generator::instance();
        $activity = $perform_generator->create_activity_in_container(['activity_name' => 'Test activity']);
        $section = $perform_generator->create_section($activity);

        $subject_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => mod_perform_constants::RELATIONSHIP_SUBJECT]
        );
        $appraiser_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => mod_perform_constants::RELATIONSHIP_APPRAISER]
        );

        $goal_generator = goal_generator::instance();
        $goal_subject_user = $subject_user;
        $user_context = context_user::instance($subject_user->id);
        $goal_category = $goal_generator->create_goal_category();

        $now = time();
        $goal1_test_config = goal_generator_config::with_category($goal_category, [
            'context' => $user_context,
            'name' => 'Test goal 1 name',
            'user_id' => $goal_subject_user->id,
            'description' => 'Test goal 1 description',
            'target_date' => $now + YEARSECS,
            'target_value' => 1.11,
            'current_value' => 0.11,
            'status' => 'in_progress',
        ]);
        $goal1 = goal_generator::instance()->create_goal($goal1_test_config);

        // Add a linked review item parent element.
        $element = element::create(
            $activity->get_context(),
            'linked_review',
            'title',
            '',
            json_encode([
                'content_type' => 'perform_goal',
                'content_type_settings' => [
                    'enable_status_change' => false
                ],
                'selection_relationships' => [$subject_section_relationship->core_relationship_id],
            ], JSON_THROW_ON_ERROR),
            false
        );
        $section_element = $perform_generator->create_section_element($section, $element);

        // Add a short_text question as a subelement.
        $child_element = $element->get_child_element_manager()->create_child_element(
            [
                'title' => 'Subquestion A',
                'is_required' => $child_element_required
            ],
            'short_text'
        );

        $subject_instance1 = $perform_generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_user_id' => $subject_user->id
        ]);

        $subject_participant_section1 = $perform_generator->create_participant_instance_and_section(
            $activity,
            $subject_user,
            $subject_instance1->id,
            $section,
            $subject_section_relationship->core_relationship->id
        );

        $linked_review_content = null;
        if ($confirmed_review_content) {
            // Like the subject participant would, add selected & confirmed content to the linked review item.
            $linked_review_content = linked_review_content::create(
                $goal1->id,
                $section_element->id,
                $subject_participant_section1->participant_instance_id,
                false
            );
        }

        $pse = participant_section_entity::repository()->find($subject_participant_section1->id);
        $pse->progress = participant_section_complete::get_code();
        $pse->save();
        $pse->refresh();
        return participant_section::load_by_id($pse->id);
    }

    public function test_has_unanswered_required_element_with_linked_review_no_responses(): void {
        $participant_section_model = $this->setup_section_and_review_item_with_child_element(false, false);
        static::assertFalse($participant_section_model->get_has_unanswered_required_element());

        $participant_section_model = $this->setup_section_and_review_item_with_child_element(false, true);
        static::assertFalse($participant_section_model->get_has_unanswered_required_element());

        $participant_section_model = $this->setup_section_and_review_item_with_child_element(true, false);
        static::assertFalse($participant_section_model->get_has_unanswered_required_element());

        $participant_section_model = $this->setup_section_and_review_item_with_child_element(true, true);
        static::assertTrue($participant_section_model->get_has_unanswered_required_element());
    }

    public function test_has_unanswered_required_element_with_linked_review_with_response(): void {
        static::setAdminUser();

        $subject = self::getDataGenerator()->create_user();

        $generator = perform_generator::instance();

        $subject_instance = $generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject->id,
            'include_questions' => true,
            'include_review_element' => true
        ]);

        $participant_instances = $subject_instance->participant_instances;
        static::assertCount(1, $participant_instances);
        $participant_sections = $participant_instances->first()->participant_sections;
        static::assertCount(1, $participant_sections);
        $participant_section = $participant_sections->first();

        // Generated questions are not required.
        $participant_section_model_subject = new participant_section($this->refresh_participant_section_entity($participant_section->id));
        static::assertFalse($participant_section_model_subject->get_has_unanswered_required_element());

        // Make all the questions required.
        element_entity::repository()->update(['is_required' => 1]);
        $participant_section_model_subject = new participant_section($this->refresh_participant_section_entity($participant_section->id));
        static::assertTrue($participant_section_model_subject->get_has_unanswered_required_element());

        // Create responses
        $generator->create_responses($subject_instance);
        $participant_section_model_subject = new participant_section($this->refresh_participant_section_entity($participant_section->id));
        // Act as if the section with the responses has been submitted too.
        $participant_section_model_subject->complete();
        $participant_section_model_subject->refresh();
        static::assertFalse($participant_section_model_subject->get_has_unanswered_required_element());

        // Delete just the linked review response.
        linked_review_content_response::repository()->delete();
        $participant_section_model_subject = new participant_section($this->refresh_participant_section_entity($participant_section->id));
        static::assertTrue($participant_section_model_subject->get_has_unanswered_required_element());

        // Leave only the linked review sub-question required. Make sure it still counts as unanswered.
        element_entity::repository()->where_null('parent')->update(['is_required' => 0]);
        $participant_section_model_subject = new participant_section($this->refresh_participant_section_entity($participant_section->id));
        static::assertTrue($participant_section_model_subject->get_has_unanswered_required_element());
    }

    public function test_manually_open_does_nothing_when_access_removed(): void {
        static::setAdminUser();

        $configuration = activity_generator_configuration::new()
            ->set_number_of_activities(1)
            ->set_number_of_sections_per_activity(1)
            ->set_relationships_per_section(['subject'])
            ->set_number_of_users_per_user_group_type(1)
            ->set_number_of_elements_per_section(0);

        $generator = perform_generator::instance();
        $generator->create_full_activities($configuration);

        // Close the participant section.
        /** @var participant_section_entity $participant_section */
        $participant_section = participant_section_entity::repository()->one(true);
        $participant_section_model = participant_section::load_by_entity($participant_section);
        $participant_section_model->manually_close();

        // Remove access for the corresponding participant instance.
        $participant_instance_model = participant_instance_model::load_by_entity($participant_section->participant_instance);
        $participant_instance_model->manually_close();
        $participant_instance_model->set_access_removed(true);

        // Try to re-open section.
        $participant_section_model->manually_open();
        static::assertDebuggingCalled(
            'mod_perform\models\response\participant_section::manually_open called for a participant instance with access removed. Cannot open. Doing nothing.'
        );

        // Should still be closed.
        /** @var participant_section_entity $participant_section_entity */
        $participant_section_entity = participant_section_entity::repository()->find($participant_section_model->id);
        static::assertSame(participant_section_closed::get_code(), (int)$participant_section_entity->availability);
    }

    /**
     * @param int $id
     * @return participant_section_entity|entity
     */
    private function refresh_participant_section_entity(int $id): participant_section_entity|entity {
        return participant_section_entity::repository()
            ->with(['section_elements', 'participant_instance'])
            ->where('id', $id)
            ->one(true);
    }
}
