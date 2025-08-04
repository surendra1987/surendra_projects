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
 * @author Mark Metcalfe <mark.metcalfe@totaralearning.com>
 * @package mod_perform
 * @category test
 */

use mod_perform\constants;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use mod_perform\entity\activity\subject_instance as subject_instance_entity;
use mod_perform\event\participant_instance_progress_updated;
use mod_perform\event\participant_section_progress_updated;
use mod_perform\event\subject_instance_availability_closed;
use mod_perform\event\subject_instance_progress_updated;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\subject_instance;
use mod_perform\models\response\participant_section;
use mod_perform\observers\subject_instance_availability;
use mod_perform\state\participant_instance\closed as pi_closed;
use mod_perform\state\participant_instance\complete as pi_complete;
use mod_perform\state\participant_instance\not_started as pi_not_started;
use mod_perform\state\participant_instance\not_submitted as pi_not_submitted;
use mod_perform\state\participant_instance\open as pi_open;
use mod_perform\state\state;
use mod_perform\state\subject_instance\closed as si_closed;
use mod_perform\state\subject_instance\complete as si_complete;
use mod_perform\state\subject_instance\in_progress as si_in_progress;
use mod_perform\state\subject_instance\not_started as si_not_started;
use mod_perform\state\subject_instance\open as si_open;
use mod_perform\state\subject_instance\subject_instance_availability as subject_instance_availability_state;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\testing\generator as perform_generator;

require_once(__DIR__ . '/state_testcase.php');

/**
 * @group perform
 */
class mod_perform_subject_instance_availability_test extends state_testcase {

    protected static function get_object_type(): string {
        return subject_instance_availability_state::get_type();
    }

    public static function state_transitions_data_provider(): array {
        return [
            'Open to Closed' => [si_open::class, si_closed::class, true],
            'Closed to Open' => [si_closed::class, si_open::class, true],
            'Open to Open' => [si_open::class, si_open::class, false],
            'Closed to Closed' => [si_closed::class, si_closed::class, false],
        ];
    }

    /**
     * @dataProvider state_transitions_data_provider
     * @param string $initial_state_class
     * @param string $final_state_class
     * @param bool $can_switch
     */
    public function test_state_switching(string $initial_state_class, string $final_state_class, bool $can_switch): void {
        [$subject_instance] = $this->create_data();

        /** @var state $initial_state */
        $initial_state = new $initial_state_class($subject_instance);

        $this->assertEquals($can_switch, $initial_state->can_switch($final_state_class));
    }

    public function test_subject_instance_closed_upon_instance_completion(): void {
        /**
         * @var subject_instance $subject1
         * @var subject_instance_entity $subject1_entity
         * @var subject_instance_entity $subject2_entity
         */
        [$subject1, $subject1_entity, $subject2_entity] = $this->create_data();

        /** @var participant_instance $participant1_instance */
        $participant1_instance = $subject1->get_participant_instances()->first();
        /** @var participant_section $participant1_section */
        $participant1_section = $participant1_instance->get_participant_sections()->first();

        $participant1_section->complete(); // Auto-aggregates completion of subject instance.

        $subject1_entity->refresh();
        $subject2_entity->refresh();

        $subject1_model = subject_instance::load_by_entity($subject1_entity);
        $subject2_model = subject_instance::load_by_entity($subject2_entity);

        $this->assertEquals(si_complete::get_code(), $subject1_model->progress_state::get_code());
        $this->assertEquals(si_not_started::get_code(), $subject2_model->progress_state::get_code());

        $this->assertEquals(si_closed::get_code(), $subject1_model->availability_state::get_code());
        $this->assertEquals(si_open::get_code(), $subject2_model->availability_state::get_code());
    }

    public function test_subject_instance_not_closed_when_instance_not_complete(): void {
        /**
         * @var subject_instance $subject1
         * @var subject_instance_entity $subject1_entity
         * @var subject_instance_entity $subject2_entity
         */
        [$subject1, $subject1_entity, $subject2_entity] = $this->create_data();

        $subject1_entity->progress = si_not_started::get_code();
        $subject1_entity->save();

        /** @var participant_instance $participant1_instance */
        $participant1_instance = $subject1->get_participant_instances()->first();
        /** @var participant_section $participant1_section */
        $participant1_section = $participant1_instance->get_participant_sections()->first();

        $participant1_section->draft(); // Auto-aggregates in-progress of subject instance.

        $subject1_entity->refresh();
        $subject2_entity->refresh();

        $subject1_model = subject_instance::load_by_entity($subject1_entity);
        $subject2_model = subject_instance::load_by_entity($subject2_entity);

        $this->assertEquals(si_open::get_code(), $subject1_model->availability_state::get_code());
        $this->assertEquals(si_open::get_code(), $subject2_model->availability_state::get_code());
    }

    public function test_subject_instance_closed_event(): void {
        /**
         * @var subject_instance $subject1
         * @var subject_instance_entity $subject1_entity
         * @var subject_instance_entity $subject2_entity
         * @var activity $activity
         */
        [$subject1, $subject1_entity, $subject2_entity, $activity] = $this->create_data();

        $subject1_entity->progress = si_complete::get_code();
        $subject1_entity->save();
        $event_sink = $this->redirectEvents();
        subject_instance_availability::close_completed_subject_instance(
            subject_instance_progress_updated::create_from_subject_instance($subject1)
        );
        $event_sink->close();
        $events = $event_sink->get_events();

        $this->assertCount(1, $events);

        $event = reset($events);
        $this->assertInstanceOf(subject_instance_availability_closed::class, $event);
        $this->assertEquals($subject1_entity->id, $event->objectid);
        $this->assertEquals($activity->get_context()->id, $event->contextid);
        $this->assertEquals(get_admin()->id, $event->userid);

        $subject1_entity->refresh();
        $subject2_entity->refresh();

        $subject1_model = subject_instance::load_by_entity($subject1_entity);
        $subject2_model = subject_instance::load_by_entity($subject2_entity);

        $this->assertEquals(si_closed::get_code(), $subject1_model->availability_state::get_code());
        $this->assertEquals(si_open::get_code(), $subject2_model->availability_state::get_code());
    }

    public function test_instance_is_not_closed_if_activity_close_on_completion_is_not_set(): void {
        /**
         * @var subject_instance $subject1
         * @var subject_instance_entity $subject1_entity
         * @var subject_instance_entity $subject2_entity
         * @var activity $activity
         */
        [$subject1, $subject1_entity, $subject2_entity, $activity] = $this->create_data();
        $previous_subject_progress = $subject1->progress_status;

        $activity->settings->update([activity_setting::CLOSE_ON_COMPLETION => false]);

        /** @var participant_instance $participant1_instance */
        $participant1_instance = $subject1->get_participant_instances()->first();
        /** @var participant_section $participant1_section */
        $participant1_section = $participant1_instance->get_participant_sections()->first();

        // Capture the participant section progress event.
        $event_sink = $this->redirectEvents();
        $participant1_section->complete();
        $event_sink->close();
        $events = $event_sink->get_events();

        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf(participant_section_progress_updated::class, $event);

        // Manually fire the update that should occur due to the first event, and capture the participant instance progress event.
        $event_sink = $this->redirectEvents();
        $participant1_instance->update_progress_status();
        $event_sink->close();
        $events = $event_sink->get_events();

        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf(participant_instance_progress_updated::class, $event);

        // Manually fire the update that should occur due to the second event, and capture the subject instance progress event.
        $event_sink = $this->redirectEvents();
        $subject1->update_progress_status();
        $event_sink->close();
        $events = $event_sink->get_events();
        $this->assertCount(1, $events);

        $event = reset($events);
        $this->assertInstanceOf(subject_instance_progress_updated::class, $event);
        $this->assertEquals($subject1->id, $event->objectid);
        $this->assertEquals($activity->get_context()->id, $event->contextid);
        $this->assertEquals(get_admin()->id, $event->userid);
        $this->assertEquals($subject1->subject_user_id, $event->relateduserid);
        $this->assertEquals($subject1->progress_status, $event->other['progress']);
        $this->assertEquals($previous_subject_progress, $event->other['previous_progress']);

        $subject1_entity->refresh();
        $subject2_entity->refresh();

        $subject1_model = subject_instance::load_by_entity($subject1_entity);
        $subject2_model = subject_instance::load_by_entity($subject2_entity);

        $this->assertEquals(si_open::get_code(), $subject1_model->availability_state::get_code());
        $this->assertEquals(si_open::get_code(), $subject2_model->availability_state::get_code());
    }

    public function test_closed_at_date(): void {
        /** @var subject_instance $subject_instance */
        [$subject_instance] = $this->create_data();
        $subject_instance_entity = $subject_instance->get_entity_copy();
        $this->assertNull($subject_instance_entity->closed_at);

        $subject_instance->manually_close();
        $subject_instance_entity->refresh();
        $this->assertLessThanOrEqual(time(), $subject_instance_entity->closed_at);

        $subject_instance->manually_open();
        $subject_instance_entity->refresh();
        $this->assertNull($subject_instance_entity->closed_at);
    }

    public static function manual_closure_data_provider(): array  {
        return [
            [true, true, 'manually_close_and_complete'],
            [false, true, 'manually_close_and_complete'],
            [true, true, 'manually_close'],
            [false, true, 'manually_close'],
            [true, false,  'manually_close_and_complete'],
            [false, false, 'manually_close_and_complete'],
            [true, false, 'manually_close'],
            [false, false, 'manually_close'],
        ];
    }

    /**
     * Check for all combinations of 'manual_close' and 'close_on_completion' settings that
     * state escalation from participant instance to subject instance works as expected for
     * both manually_close() and manually_close_and_complete() methods.
     *
     * @dataProvider manual_closure_data_provider
     * @param bool $manual_closure_enabled
     * @param bool $close_on_completion_enabled
     * @param string $close_participant_instance_method
     * @return void
     */
    public function test_escalate_manual_close_for_subject_instance(
        bool $manual_closure_enabled,
        bool $close_on_completion_enabled,
        string $close_participant_instance_method
    ): void {
        self::setAdminUser();

        $configuration = activity_generator_configuration::new()
            ->set_number_of_users_per_user_group_type(1)
            ->set_relationships_per_section(['subject', 'manager'])
            ->enable_manager_for_each_subject_user();

        $generator = perform_generator::instance();
        $activity = $generator->create_full_activities($configuration)->first();

        // Make sure the set-up is as expected.
        $participant_instances = participant_instance_entity::repository()->get()->all();
        $this->assertCount(2, $participant_instances);
        $participant_instance1 = participant_instance::load_by_entity($participant_instances[0]);
        $participant_instance2 = participant_instance::load_by_entity($participant_instances[1]);
        $this->assertEquals($participant_instance1->subject_instance_id, $participant_instance2->subject_instance_id);

        $this->assert_pi_availability($participant_instance1->id, pi_open::get_code());
        $this->assert_pi_progress($participant_instance1->id, pi_not_started::get_code());

        $this->assert_pi_availability($participant_instance2->id, pi_open::get_code());
        $this->assert_pi_progress($participant_instance2->id, pi_not_started::get_code());

        $subject_instance = $participant_instance1->subject_instance;
        $this->assert_si_availability($subject_instance->id, si_open::get_code());
        $this->assert_si_progress($subject_instance->id, si_not_started::get_code());

        $activity->settings->update([activity_setting::MANUAL_CLOSE => $manual_closure_enabled]);
        $activity->settings->update([activity_setting::CLOSE_ON_COMPLETION => $close_on_completion_enabled]);

        // Close the first participant instance.
        $participant_instance1->$close_participant_instance_method();

        $this->assert_pi_availability($participant_instance1->id, pi_closed::get_code());
        $expected_pi_progress = $close_participant_instance_method === 'manually_close'
            ? pi_not_submitted::get_code()
            : pi_complete::get_code();
        $this->assert_pi_progress($participant_instance1->id, $expected_pi_progress);

        $this->assert_pi_availability($participant_instance2->id, pi_open::get_code());
        $this->assert_pi_progress($participant_instance2->id, pi_not_started::get_code());

        $subject_instance = subject_instance::load_by_id($subject_instance->id);
        $this->assert_si_availability($subject_instance->id, si_open::get_code());
        $this->assert_si_progress($subject_instance->id, si_in_progress::get_code());

        // Close the second participant instance.
        $participant_instance2->$close_participant_instance_method();

        $this->assert_pi_availability($participant_instance1->id, pi_closed::get_code());
        $expected_pi_progress = $close_participant_instance_method === 'manually_close'
            ? pi_not_submitted::get_code()
            : pi_complete::get_code();
        $this->assert_pi_progress($participant_instance1->id, $expected_pi_progress);

        $this->assert_pi_availability($participant_instance2->id, pi_closed::get_code());
        $this->assert_pi_progress($participant_instance2->id, $expected_pi_progress);

        $subject_instance = subject_instance::load_by_id($subject_instance->id);
        $expected_si_availability = $close_on_completion_enabled
            ? si_closed::get_code()
            : ($manual_closure_enabled ? si_closed::get_code() : si_open::get_code());
        $this->assert_si_availability($subject_instance->id, $expected_si_availability);
        $this->assert_si_progress($subject_instance->id, si_complete::get_code());
    }

    /**
     * @param int $participant_instance_id
     * @param int $progress_code
     * @return void
     */
    private function assert_pi_progress(int $participant_instance_id, int $progress_code): void {
        $pi = participant_instance::load_by_id($participant_instance_id);
        $this->assertSame($progress_code, $pi->progress_state::get_code());
    }

    /**
     * @param int $participant_instance_id
     * @param int $availability_code
     * @return void
     */
    private function assert_pi_availability(int $participant_instance_id, int $availability_code): void {
        $pi = participant_instance::load_by_id($participant_instance_id);
        $this->assertSame($availability_code, $pi->availability_state::get_code());
    }

    /**
     * @param int $subject_instance_id
     * @param int $progress_code
     * @return void
     */
    private function assert_si_progress(int $subject_instance_id, int $progress_code): void {
        $si = subject_instance::load_by_id($subject_instance_id);
        $this->assertSame($progress_code, $si->progress_state::get_code());
    }

    /**
     * @param int $subject_instance_id
     * @param int $availability_code
     * @return void
     */
    private function assert_si_availability(int $subject_instance_id, int $availability_code): void {
        $si = subject_instance::load_by_id($subject_instance_id);
        $this->assertSame($availability_code, $si->availability_state::get_code());
    }

    /**
     * Create activity and subject instances required for testing.
     *
     * @return array
     */
    private function create_data(): array {
        self::setAdminUser();

        /** @var perform_generator $generator */
        $generator = perform_generator::instance();

        $activity = $generator->create_activity_in_container();
        $activity->settings->update([activity_setting::CLOSE_ON_COMPLETION => true]);
        $section = $activity->get_sections()->first();

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $subject1_entity = $generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_user_id' => $user1->id,
            'include_questions' => false,
        ]);

        $subject2_entity = $generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_user_id' => $user2->id,
            'include_questions' => false,
        ]);

        $subject_relationship_id = $generator->get_core_relationship(constants::RELATIONSHIP_SUBJECT)->id;

        $generator->create_participant_instance_and_section(
            $activity,
            $user1,
            $subject1_entity->id,
            $section,
            $subject_relationship_id
        );
        $generator->create_participant_instance_and_section(
            $activity,
            $user2,
            $subject2_entity->id,
            $section,
            $subject_relationship_id
        );

        $subject1_model = subject_instance::load_by_entity($subject1_entity);
        $subject2_model = subject_instance::load_by_entity($subject2_entity);

        $this->assertEquals(si_open::get_code(), $subject1_model->availability_state::get_code());
        $this->assertEquals(si_open::get_code(), $subject2_model->availability_state::get_code());

        return [$subject1_model, $subject1_entity, $subject2_entity, $activity];
    }

}
