<?php

/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

use core\collection;
use core\testing\generator;
use mod_perform\constants;
use mod_perform\data_providers\activity\subject_instance_overview;
use mod_perform\entity\activity\activity as activity_entity;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section as participant_section_entity;
use mod_perform\entity\activity\subject_instance as subject_instance_entity;
use mod_perform\entity\activity\track;
use mod_perform\entity\activity\track_assignment;
use mod_perform\expand_task;
use mod_perform\models\activity\activity as activity_model;
use mod_perform\models\activity\participant_source;
use mod_perform\models\activity\subject_instance as subject_instance_model;
use mod_perform\models\activity\subject_instance_overview_item;
use mod_perform\models\activity\track_assignment_type;
use mod_perform\models\response\participant_section as participant_section_model;
use mod_perform\task\create_subject_instance_task;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\testing\generator as perform_generator;
use mod_perform\user_groups\grouping;

require_once(__DIR__ . '/subject_instance_testcase.php');

class mod_perform_subject_instance_overview_test extends mod_perform_subject_instance_testcase {

    public function test_filter_must_be_set(): void {
        $subject_instance_overview = new subject_instance_overview(self::$user->id, participant_source::INTERNAL);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Period filter must be set to a positive integer.');
        $subject_instance_overview->get_completed_subject_instances();
    }

    public function test_completed_observes_period_restriction(): void {
        // There should be no completed in the initial set up.
        self::assert_completed_subject_instances([], 22);

        $now = time();
        $twenty_days_ago = $now - (20 * DAYSECS);
        perform_generator::set_subject_instance_completed_at(self::$about_user_and_participating, $twenty_days_ago);

        self::assert_completed_subject_instances([self::$about_user_and_participating->id], 22);

        // Set the other subject instances to complete
        perform_generator::set_subject_instance_completed_at(self::$about_user_but_not_participating, $twenty_days_ago);
        perform_generator::set_subject_instance_completed_at(self::$about_someone_else_and_participating, $twenty_days_ago);

        // We don't expect to see:
        //  - activities about someone else even when we're participating
        //  - activities about us when we're not participating
        self::assert_completed_subject_instances([self::$about_user_and_participating->id], 22);

        // Shorter observation period, so the completion date is outside of it.
        self::assert_completed_subject_instances([], 18);
    }

    public function test_completed_correct_order(): void {
        // Create another activity where the user is subject & responding participant.
        $another_subject_instance = $this->create_subject_instance_with_subject_participating();

        // There should be no completed yet.
        self::assert_completed_subject_instances([], 10);

        $now = time();
        perform_generator::set_subject_instance_completed_at(self::$about_user_and_participating, $now - HOURSECS);
        perform_generator::set_subject_instance_completed_at($another_subject_instance, $now - 60);

        // Now check the correct order (last updated first).
        self::assert_completed_subject_instances([
            $another_subject_instance->id,
            self::$about_user_and_participating->id,
        ], 10, true);

        perform_generator::set_subject_instance_completed_at($another_subject_instance, $now - 2 * HOURSECS);

        // Order should be reversed now.
        self::assert_completed_subject_instances([
            self::$about_user_and_participating->id,
            $another_subject_instance->id,
        ], 10, true);
    }

    public function test_progressed_observes_period_restriction(): void {
        // There should be no progressed in the initial set up.
        self::assert_progressed_subject_instances([], 10);

        // Get a section for the subject and advance the progress state.
        $participant_section_model = $this->advance_subject_progress(self::$about_user_and_participating);

        // Subject instance should now be in the result.
        self::assert_progressed_subject_instances([self::$about_user_and_participating->id], 10);

        // Backdate that section update time to before the observation period.
        self::perform_generator()->backdate_participant_section_updated_time($participant_section_model, time() - DAYSECS * 11);
        self::assert_progressed_subject_instances([], 10);
        self::assert_progressed_subject_instances([self::$about_user_and_participating->id], 12);
    }

    public function test_progressed_correct_order(): void {
        // Create another activity where the user is subject & responding participant.
        $another_subject_instance = $this->create_subject_instance_with_subject_participating();

        // There should be no progressed yet.
        self::assert_progressed_subject_instances([], 10);

        $participant_section_model1 = $this->advance_subject_progress(self::$about_user_and_participating);
        $participant_section_model2 = $this->advance_subject_progress($another_subject_instance);

        // Check that both are there (don't check order).
        self::assert_progressed_subject_instances([
            self::$about_user_and_participating->id,
            $another_subject_instance->id,
        ], 10);

        $now = time();
        self::perform_generator()->backdate_participant_section_updated_time($participant_section_model1, $now - HOURSECS);

        // Now check the correct order (last updated first).
        self::assert_progressed_subject_instances([
            $another_subject_instance->id,
            self::$about_user_and_participating->id,
        ], 10, true);

        self::perform_generator()->backdate_participant_section_updated_time($participant_section_model2, $now - 2 * HOURSECS);

        // Order should be reversed now.
        self::assert_progressed_subject_instances([
            self::$about_user_and_participating->id,
            $another_subject_instance->id,
        ], 10, true);
    }

    public function test_not_progressed_observes_period_restriction(): void {
        // There should be no "not progressed" in the initial set up, because they are "not started".
        self::assert_not_progressed_subject_instances([], 10);

        // Get a section for the subject and advance the progress state.
        $participant_section_model = $this->advance_subject_progress(self::$about_user_and_participating);

        // Subject instance should not be in the result.
        self::assert_not_progressed_subject_instances([], 10);

        // Backdate that section update time to before the observation period.
        self::perform_generator()->backdate_participant_section_updated_time($participant_section_model, time() - DAYSECS * 11);
        self::assert_not_progressed_subject_instances([self::$about_user_and_participating->id], 10);
        self::assert_not_progressed_subject_instances([], 12);
    }

    public function test_not_started(): void {
        $another_subject_instance = $this->create_subject_instance_with_subject_participating();

        self::assert_not_started_subject_instances([
            self::$about_user_and_participating->id,
            $another_subject_instance->id,
        ], 10);

        // Check correct sort order (the newest assigned first).
        $now = time();
        $subject_instance = new subject_instance_entity($another_subject_instance->get_id());
        $subject_instance->created_at = $now - DAYSECS;
        $subject_instance->save();

        self::assert_not_started_subject_instances([
            self::$about_user_and_participating->id,
            $another_subject_instance->id,
        ], 10, true);

        $subject_instance = new subject_instance_entity(self::$about_user_and_participating->get_id());
        $subject_instance->created_at = $now - 2 * DAYSECS;
        $subject_instance->save();

        // Order should be reversed.
        self::assert_not_started_subject_instances([
            $another_subject_instance->id,
            self::$about_user_and_participating->id,
        ], 10, true);

        // Make sure it's not included anymore when it has been started.
        $this->advance_subject_progress($another_subject_instance);
        self::assert_not_started_subject_instances([
            self::$about_user_and_participating->id,
        ], 10);
    }

    /**
     * Check the case when "one subject instance per job assignment" is activated.
     * @return void
     */
    public function test_multiple_job_assignments(): void {
        $user = generator::instance()->create_user();
        $manager1 = generator::instance()->create_user();
        $manager2 = generator::instance()->create_user();

        $ja1 = $this->setup_manager_employee_job_assignment($manager1, $user, [
            'fullname' => 'job assignment 1',
            'idnumber' => 'ja1',
        ]);
        $ja2 = $this->setup_manager_employee_job_assignment($manager2, $user, [
            'fullname' => 'job assignment 2',
            'idnumber' => 'ja2',
        ]);

        $perform_generator = perform_generator::instance();

        $activity = $perform_generator->create_activity_in_container();
        $track = $perform_generator
            ->create_activity_tracks($activity)
            ->first();

        $track = new track($track->id);
        $track->subject_instance_generation = track::SUBJECT_INSTANCE_GENERATION_ONE_PER_JOB;
        $track->save();

        $assignment = new track_assignment([
            'track_id' => $track->id,
            'type' => track_assignment_type::ADMIN,
            'user_group_type' => grouping::USER,
            'user_group_id' => $user->id,
            'created_by' => 0,
            'expand' => true,
        ]);
        $assignment->save();

        $section = perform_generator::instance()->create_section($activity);
        perform_generator::instance()->create_section_relationship(
            $section,
            ['relationship' => constants::RELATIONSHIP_SUBJECT]
        );

        expand_task::create()->expand_single($assignment->id);
        (new create_subject_instance_task())->execute();

        $subject_instances = subject_instance_entity::repository()
            ->where('subject_user_id', $user->id)
            ->get();

        self::assertEqualsCanonicalizing(
            [$ja1->id, $ja2->id],
            $subject_instances->pluck('job_assignment_id')
        );

        $subject_instance_overview = new subject_instance_overview($user->id, participant_source::INTERNAL);
        $subject_instance_overview->add_filters(['period' => 10]);
        $result = $subject_instance_overview->get_not_started_overview_items();
        self::assertEqualsCanonicalizing(
            ['job assignment 1', 'job assignment 2'],
            $result->pluck('job_assignment')
        );
    }

    /**
     * Check for subject instances of all states at the same time.
     *
     * @return void
     */
    public function test_mixed_states() {
        $subject_instance_progressed = $this->create_subject_instance_with_subject_participating();
        $subject_instance_not_progressed = $this->create_subject_instance_with_subject_participating();
        $subject_instance_complete = $this->create_subject_instance_with_subject_participating();

        // Progressed
        $this->advance_subject_progress($subject_instance_progressed);

        // Not progressed
        $participant_section_model = $this->advance_subject_progress($subject_instance_not_progressed);
        self::perform_generator()->backdate_participant_section_updated_time($participant_section_model, time() - DAYSECS * 11);

        // Complete
        perform_generator::set_subject_instance_completed_at($subject_instance_complete, time() - HOURSECS);

        self::assert_not_started_subject_instances([self::$about_user_and_participating->id], 10);
        self::assert_progressed_subject_instances([$subject_instance_progressed->id], 10);
        self::assert_not_progressed_subject_instances([$subject_instance_not_progressed->id], 10);
        self::assert_completed_subject_instances([$subject_instance_complete->id], 10);
    }
    /**
     * All activities should be excluded where the subject's last action is more than 2 years ago.
     *
     * @return void
     */
    public function test_two_year_limit() {
        $subject_instance_progressed = $this->create_subject_instance_with_subject_participating();
        $subject_instance_complete = $this->create_subject_instance_with_subject_participating();

        $bit_more_than_two_years_ago = time() - (2 * YEARSECS) - (10 * DAYSECS);

        // Progressed (or not progressed depending on period filter).
        $participant_section_model = $this->advance_subject_progress($subject_instance_progressed);
        self::perform_generator()->backdate_participant_section_updated_time($participant_section_model, $bit_more_than_two_years_ago);

        // Complete
        perform_generator::set_subject_instance_completed_at($subject_instance_complete, $bit_more_than_two_years_ago);

        // Not started
        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = self::$about_user_and_participating->get_entity_copy();
        $subject_instance_entity->created_at = $bit_more_than_two_years_ago;
        $subject_instance_entity->save();

        self::assert_not_started_subject_instances([], 2 * 365);
        self::assert_progressed_subject_instances([], 3 * 365);
        self::assert_not_progressed_subject_instances([], 10);
        self::assert_completed_subject_instances([], 3 * 365);
    }

    /**
     * Check that the latest updated section determines the "last updated" value.
     *
     * @return void
     */
    public function test_multi_section() {
        self::setAdminUser();

        $perform_generator = perform_generator::instance();

        $configuration = activity_generator_configuration::new()
            ->set_number_of_activities(1)
            ->set_number_of_users_per_user_group_type(1)
            ->set_number_of_tracks_per_activity(1)
            ->set_number_of_sections_per_activity(3)
            ->set_cohort_assignments_per_activity(1)
            ->set_number_of_elements_per_section(1)
            ->set_relationships_per_section(
                [
                    constants::RELATIONSHIP_SUBJECT,
                ]
            );

        /** @var activity_model $activity */
        $activity = $perform_generator->create_full_activities($configuration)->first();

        /** @var activity_entity $activity_entity */
        $activity_entity = $activity->get_entity_copy();

        /** @var track $track */
        $track = $activity_entity->tracks->first();
        $subject_instances = $track->subject_instances;
        self::assertCount(1, $subject_instances);

        /** @var subject_instance_entity $subject_instance */
        $subject_instance = $subject_instances->first();
        $participant_instances = $subject_instance->participant_instances;
        self::assertCount(1, $participant_instances);

        $subject_user_id = $subject_instance->subject_user_id;
        self::assert_not_started_subject_instances([$subject_instance->id], 10, false, $subject_user_id);
        self::assert_not_progressed_subject_instances([], 10, false, $subject_user_id);
        self::assert_completed_subject_instances([], 10, false, $subject_user_id);
        self::assert_progressed_subject_instances([], 10, false, $subject_user_id);

        /** @var participant_instance $participant_instance */
        $participant_instance = $participant_instances->first();
        $participant_sections = $participant_instance->participant_sections->all();
        self::assertCount(3, $participant_sections);

        // Progress first and second section
        $participant_section1 = participant_section_model::load_by_entity($participant_sections[0]);
        $participant_section1->get_progress_state()->on_participant_access();
        $participant_section2 = participant_section_model::load_by_entity($participant_sections[1]);
        $participant_section2->get_progress_state()->on_participant_access();

        $now = time();
        participant_section_entity::repository()
            ->where('id', $participant_section1->id)
            ->update([
                'updated_at' => $now - 3 * DAYSECS,
                'progress_updated_at' => $now - 3 * DAYSECS
            ]);
        participant_section_entity::repository()
            ->where('id', $participant_section2->id)
            ->update([
                'updated_at' => $now - 2 * DAYSECS,
                'progress_updated_at' => $now - 2 * DAYSECS
            ]);

        self::assert_not_started_subject_instances([], 10, false, $subject_user_id);
        self::assert_not_progressed_subject_instances([], 10, false, $subject_user_id);
        self::assert_completed_subject_instances([], 10, false, $subject_user_id);

        // Get the actual overview item to check that the right 'last update' time is returned.
        $subject_instance_overview = new subject_instance_overview($subject_user_id, participant_source::INTERNAL);
        $subject_instance_overview->add_filters(['period' => 10]);
        $subject_instances_result = $subject_instance_overview->get_progressed_overview_items();
        self::assertCount(1, $subject_instances_result);
        /** @var subject_instance_overview_item $subject_instance_result */
        $subject_instance_result = $subject_instances_result->first();
        self::assertEquals($subject_instance->id, $subject_instance_result->id);
        self::assertEquals($now - 2 * DAYSECS, $subject_instance_result->last_update['date']);

        // Change the updated time for one participant section, so the other section's time should be first in the result.
        participant_section_entity::repository()
            ->where('id', $participant_section2->id)
            ->update([
                'updated_at' => $now - 4 * DAYSECS,
                'progress_updated_at' => $now - 4 * DAYSECS
            ]);
        $subject_instance_overview = new subject_instance_overview($subject_user_id, participant_source::INTERNAL);
        $subject_instance_overview->add_filters(['period' => 10]);
        $subject_instances_result = $subject_instance_overview->get_progressed_overview_items();
        self::assertCount(1, $subject_instances_result);
        /** @var subject_instance_overview_item $subject_instance_result */
        $subject_instance_result = $subject_instances_result->first();
        self::assertEquals($subject_instance->id, $subject_instance_result->id);
        self::assertEquals($now - 3 * DAYSECS, $subject_instance_result->last_update['date']);
    }

    /**
     * View-only participation can only ever appear under "not started" and "complete", because the subject
     * cannot make any progress.
     *
     * @return void
     */
    public function test_view_only_participation(): void {
        self::setAdminUser();

        $perform_generator = perform_generator::instance();

        $configuration = activity_generator_configuration::new()
            ->set_number_of_activities(1)
            ->set_number_of_users_per_user_group_type(1)
            ->set_number_of_tracks_per_activity(1)
            ->set_number_of_sections_per_activity(1)
            ->set_cohort_assignments_per_activity(1)
            ->set_number_of_elements_per_section(1)
            ->set_relationships_per_section(
                [
                    constants::RELATIONSHIP_SUBJECT,
                    constants::RELATIONSHIP_MANAGER,
                ]
            )
            ->set_view_only_relationships([constants::RELATIONSHIP_SUBJECT])
            ->enable_manager_for_each_subject_user();

        /** @var activity_model $activity */
        $activity = $perform_generator->create_full_activities($configuration)->first();

        /** @var activity_entity $activity_entity */
        $activity_entity = $activity->get_entity_copy();

        /** @var track $track */
        $track = $activity_entity->tracks->first();
        $subject_instances = $track->subject_instances;
        self::assertCount(1, $subject_instances);

        /** @var subject_instance_entity $subject_instance */
        $subject_instance = $subject_instances->first();
        $participant_instances = $subject_instance->participant_instances;
        self::assertCount(2, $participant_instances);

        $subject_user_id = $subject_instance->subject_user_id;
        self::assert_not_started_subject_instances([$subject_instance->id], 10, false, $subject_user_id);
        self::assert_not_progressed_subject_instances([], 10, false, $subject_user_id);
        self::assert_completed_subject_instances([], 10, false, $subject_user_id);
        self::assert_progressed_subject_instances([], 10, false, $subject_user_id);

        // Get the manager's participant instance
        $non_subject_participant_instances = $participant_instances->filter(
            fn (participant_instance $participant_instance) => (int)$participant_instance->participant_id !== (int)$subject_user_id
        );
        self::assertCount(1, $non_subject_participant_instances);
        /** @var participant_instance $manager_participant_instance */
        $manager_participant_instance = $non_subject_participant_instances->first();

        // Make progress as the manager.
        $participant_sections = $manager_participant_instance->participant_sections->all();
        self::assertCount(1, $participant_sections);

        $participant_section1 = participant_section_model::load_by_entity($participant_sections[0]);
        $participant_section1->get_progress_state()->on_participant_access();

        // Should still be under "not started".
        self::assert_not_started_subject_instances([$subject_instance->id], 10, false, $subject_user_id);
        self::assert_not_progressed_subject_instances([], 10, false, $subject_user_id);
        self::assert_completed_subject_instances([], 10, false, $subject_user_id);
        self::assert_progressed_subject_instances([], 10, false, $subject_user_id);

        perform_generator::set_subject_instance_completed_at(subject_instance_model::load_by_entity($subject_instance), time());
        self::assert_not_started_subject_instances([], 10, false, $subject_user_id);
        self::assert_not_progressed_subject_instances([], 10, false, $subject_user_id);
        self::assert_completed_subject_instances([$subject_instance->id], 10, false, $subject_user_id);
        self::assert_progressed_subject_instances([], 10, false, $subject_user_id);
    }

    private static function assert_progressed_subject_instances(
        array $expected_ids, int $period, bool $check_order = false, int $user_id = null
    ): void {
        self::assert_subject_instances(
            $expected_ids,
            self::create_subject_instance_overview($period, $user_id)->get_progressed_subject_instances(),
            $check_order
        );
    }

    private static function assert_not_progressed_subject_instances(
        array $expected_ids, int $period, bool $check_order = false, int $user_id = null
    ): void {
        self::assert_subject_instances(
            $expected_ids,
            self::create_subject_instance_overview($period, $user_id)->get_not_progressed_subject_instances(),
            $check_order
        );
    }

    private static function assert_completed_subject_instances(
        array $expected_ids, int $period, bool $check_order = false, int $user_id = null
    ): void {
        self::assert_subject_instances(
            $expected_ids,
            self::create_subject_instance_overview($period, $user_id)->get_completed_subject_instances(),
            $check_order
        );
    }

    private static function assert_not_started_subject_instances(
        array $expected_ids, int $period, bool $check_order = false, int $user_id = null
    ): void {
        self::assert_subject_instances(
            $expected_ids,
            self::create_subject_instance_overview($period, $user_id)->get_not_started_subject_instances(),
            $check_order
        );
    }

    private static function create_subject_instance_overview(int $period, int $user_id = null): subject_instance_overview {
        $user_id = $user_id ?? self::$user->id;
        $subject_instance_overview = new subject_instance_overview($user_id, participant_source::INTERNAL);
        $subject_instance_overview->add_filters(['period' => $period]);
        return $subject_instance_overview;
    }

    private static function assert_subject_instances(array $expected_ids, collection $result, bool $check_order = false): void {
        if ($check_order) {
            self::assertEquals($expected_ids, $result->pluck('id'));
        } else {
            self::assertEqualsCanonicalizing($expected_ids, $result->pluck('id'));
        }
    }

}