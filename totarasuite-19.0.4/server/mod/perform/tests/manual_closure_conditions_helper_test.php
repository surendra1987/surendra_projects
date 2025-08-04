<?php
/**
 * This file is part of Totara Perform
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\entity\activity\element;
use mod_perform\entity\activity\external_participant;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\response\helpers\manual_closure\closure_evaluation_result;
use mod_perform\models\response\helpers\manual_closure\closure_conditions as manual_closure_conditions;
use mod_perform\models\response\participant_section;
use mod_perform\entity\activity\participant_section as participant_section_entity;
use mod_perform\state\participant_instance\open;
use mod_perform\state\participant_section\complete as participant_section_complete;
use mod_perform\testing\generator as perform_generator;
use mod_perform\testing\multisection_activity_trait;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\state\participant_instance\closed;

/**
 * @group perform
 */
class mod_perform_manual_closure_conditions_helper_test extends testcase {

    use multisection_activity_trait;

    public function test_with_non_mandatory_unanswered_questions(): void {
        [
            $activity,
            $participant_instance,
        ] = $this->create_multi_section_activity();
        $this->set_all_sections_as_progress_complete($participant_instance);

        // Turn manual closure setting on.
        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);
        /** @var \mod_perform\models\activity\participant $participant */
        $participant = $participant_instance->get_participant();
        static::setUser($participant->user->id);

        // Check that participant instance availability is open. Should be so by default, but let's verify.
        /** @var participant_instance $participant_instance */
        static::assertEquals(open::get_code(), $participant_instance->get_current_state_code('availability'));

        // It's allowed because all the unanswered questions were created as not required.
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::ALLOWED, $result);
    }

    public function test_with_mandatory_unanswered_question(): void {
        [
            $activity,
            $participant_instance,
            $participant_section1,
        ] = $this->create_multi_section_activity();
        // Turn manual closure setting on.
        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        // Make one of the questions required.
        /** @var participant_section $participant_section1 */
        $section_element = $participant_section1->section->section_elements->first();
        element::repository()
            ->where('id', $section_element->element->id)
            ->update(['is_required' => 1]);

        // Refresh model.
        $participant_instance = participant_instance::load_by_id($participant_instance->id);
        /** @var \mod_perform\models\activity\participant $participant */
        $participant = $participant_instance->get_participant();
        static::setUser($participant->user->id);

        // Closure is not allowed because we made one unanswered question required.
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::UNANSWERED_MANDATORY_QUESTIONS, $result);
    }

    public function test_with_mandatory_answered_questions(): void {
        [
            $activity,
            $participant_instance,
        ] = $this->create_multi_section_activity();
        $this->set_all_sections_as_progress_complete($participant_instance);

        // Turn manual closure setting on.
        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        // Make all the questions required.
        element::repository()
            ->update(['is_required' => 1]);

        // Answer all questions.
        perform_generator::instance()->create_responses($participant_instance->subject_instance->get_entity_copy());

        // Refresh model.
        $participant_instance = participant_instance::load_by_id($participant_instance->id);
        /** @var \mod_perform\models\activity\participant $participant */
        $participant = $participant_instance->get_participant();
        static::setUser($participant->user->id);

        // Closure is allowed because all the required questions have responses.
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::ALLOWED, $result);
    }

    public function test_with_manual_closure_setting_off(): void {
        [
            $activity,
            $participant_instance,
        ] = $this->create_multi_section_activity();

        // Turn manual closure setting on.
        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => false
        ]);
        /** @var \mod_perform\models\activity\participant $participant */
        $participant = $participant_instance->get_participant();
        static::setUser($participant->user->id);

        // NOT_APPLICABLE expected because the setting is off.
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::NOT_APPLICABLE, $result);
    }

    public function test_with_participant_instance_already_closed(): void {
        [
            $activity,
            $participant_instance,
        ] = $this->create_multi_section_activity();

        // Turn manual closure setting on.
        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        /** @var participant_instance $participant_instance */
        $participant_instance->manually_close();
        /** @var \mod_perform\models\activity\participant $participant */
        $participant = $participant_instance->get_participant();
        static::setUser($participant->user->id);

        // ALREADY_CLOSED expected because the participant instance is closed.
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::ALREADY_CLOSED, $result);
    }

    public function test_external_participant_can_manually_close(): void {
        $participant_instance = $this->setup_external_data();
        $this->set_all_sections_as_progress_complete($participant_instance);
        // We assume that the participant instance passed a middleware resolver check for external participants.
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::ALLOWED, $result);
    }

    public function test_someone_else_cannot_manually_close(): void {
        [
            $activity,
            $participant_instance,
        ] = $this->create_multi_section_activity();
        $this->set_all_sections_as_progress_complete($participant_instance);

        // Turn manual closure setting on.
        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        // Just other dude.
        $other_user = static::getDataGenerator()->create_user();
        static::setUser($other_user->id);
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::INSUFFICIENT_PERMISSIONS, $result);

        // No user
        static::setUser(null);
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::INSUFFICIENT_PERMISSIONS, $result);

        // Set guest user
        static::setGuestUser();
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::INSUFFICIENT_PERMISSIONS, $result);
    }

    /**
     * Generates test data.
     *
     * @return participant_instance
     */
    private function setup_external_data(): participant_instance {
        static::setAdminUser();

        $generator = perform_generator::instance();

        $configuration = activity_generator_configuration::new()
            ->enable_creation_of_manual_participants()
            ->set_relationships_per_section(
                [
                    constants::RELATIONSHIP_EXTERNAL,
                    constants::RELATIONSHIP_SUBJECT
                ]
            );

        $activities = $generator->create_full_activities($configuration);
        foreach ($activities as $activity) {
            $activity->settings->update([
                    activity_setting::MANUAL_CLOSE => true
                ]
            );
        }

        /** @var external_participant $external_participant */
        $external_participant = external_participant::repository()->get()->first();

        return participant_instance::load_by_entity(
            $external_participant->participant_instance
        );
    }

    public function test_with_participant_instance_and_required_section_already_manually_closed(): void {
        [
            $activity,
            $participant_instance,
        ] = $this->create_multi_section_activity();
        $this->set_all_sections_as_progress_complete($participant_instance);

        // Turn manual closure setting on.
        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        // Set one section up with a required question element, and act as if the section has then been manually closed
        // by an Admin user.
        static::setAdminUser();
        $participant_section1 = $participant_instance->participant_sections->first();
        $section_element = $participant_section1->section->section_elements->first();
        element::repository()
            ->where('id', $section_element->element->id)
            ->update(['is_required' => 1]);
        $participant_section1->manually_close();

        $participant_section1->refresh();
        static::assertEquals(closed::get_code(), $participant_section1->availability);

        /** @var \mod_perform\models\activity\participant $participant */
        $participant = $participant_instance->get_participant();
        static::setUser($participant->user->id);

        // ALLOWED expected because the participant should still be able to manually close their activity participant
        // instance.
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        static::assertEquals(closure_evaluation_result::ALLOWED, $result);
    }

    public function test_with_participant_instance_section_only_saved_as_draft(): void {
        [
            $activity,
            $participant_instance,
        ] = $this->create_multi_section_activity();
        foreach ($participant_instance->participant_sections as $participant_section) {
            // Each section needs to act like it has not been submitted, only 'saved as draft'
            static::assertNotEquals(participant_section_complete::get_code(), (int)$participant_section->progress);
        }

        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        // Make all the questions required.
        element::repository()->update(['is_required' => 1]);
        // Answer all questions.
        perform_generator::instance()->create_responses($participant_instance->subject_instance->get_entity_copy());

        // Refresh model.
        $participant_instance = participant_instance::load_by_id($participant_instance->id);
        /** @var \mod_perform\models\activity\participant $participant */
        $participant = $participant_instance->get_participant();
        static::setUser($participant->user->id);

        // Closure is allowed because all the required questions have responses.
        $result = manual_closure_conditions::create($participant_instance)->evaluate();
        // This should not succeed because every $participant_section->progress was only 'saved as draft', i.e.
        // progress not complete - which would only get set if the section was properly submitted.
        static::assertEquals(closure_evaluation_result::UNANSWERED_MANDATORY_QUESTIONS, $result);
    }

    public function test_error_for_manually_close_a_different_participant_instance(): void {
        // Set up. Create 2 performance activities with difference participant/subject users.
        [
            $activity,
            $participant_instance,
        ] = $this->create_multi_section_activity();
        $this->set_all_sections_as_progress_complete($participant_instance);
        [
            $activity2,
            $participant_instance2,
        ] = $this->create_multi_section_activity();
        $this->set_all_sections_as_progress_complete($participant_instance2, true);

        // Turn manual closure setting on for them.
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);
        $activity2->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        $participant1 = $participant_instance->get_participant();
        static::setUser($participant1->user->id);

        // Operate.
        // We want to act like a request to manually close a P.A has been made by the $participant1 user. However,
        // it's for closing a participant_instance for the participant2 user. We should not see any other error messages leaked,
        // e.g. about the participant2 user's already closed status for the P.A, only a get a code that tells us the
        // user is not the same participant user as for the participant_instance.
        $result = manual_closure_conditions::create($participant_instance2)->evaluate();
        // Assert.
        static::assertEquals(closure_evaluation_result::INSUFFICIENT_PERMISSIONS, $result);
    }

    /**
     * @param participant_instance $participant_instance
     * @param bool $mark_closed
     * @return void
     */
    private function set_all_sections_as_progress_complete(participant_instance $participant_instance,
                                                           bool $mark_closed = false
    ): void {
        if ($mark_closed) {
            $participant_instance->manually_close_and_complete();
            $participant_instance->refresh();
            return;
        }
        foreach ($participant_instance->participant_sections as $participant_section) {
            $participant_section_entity = participant_section_entity::repository()->find($participant_section->id);
            // closure_conditions verify_required_questions_completed requires this.
            $participant_section_entity->progress = participant_section_complete::get_code();
            $participant_section_entity->save();
            $participant_section->refresh();
        }
    }
}
