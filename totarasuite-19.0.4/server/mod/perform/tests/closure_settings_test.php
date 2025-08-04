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
use mod_perform\models\activity\activity;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\response\participant_section as participant_section_model;
use mod_perform\state\participant_instance\closed as pi_closed;
use mod_perform\state\participant_instance\complete as pi_complete;
use mod_perform\state\participant_instance\in_progress as pi_in_progress;
use mod_perform\state\participant_instance\not_started as pi_not_started;
use mod_perform\state\participant_instance\open as pi_open;
use mod_perform\state\participant_section\closed as ps_closed;
use mod_perform\state\participant_section\complete as ps_complete;
use mod_perform\state\participant_section\not_started as ps_not_started;
use mod_perform\state\participant_section\open as ps_open;


/**
 * @group perform
 */
class mod_perform_closure_settings_test extends testcase {

    use mod_perform\testing\multisection_activity_trait;

    public function test_closure_settings_both_disabled(): void {
        [
            $activity,
            $participant_instance,
            $participant_section1,
            $participant_section2,
            $participant_section3,
        ] = $this->create_multi_section_activity();

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
        ]);

        // Assert initial state - everything open & not_started.
        static::assert_ps_progress(ps_not_started::class, $participant_section1);
        static::assert_ps_progress(ps_not_started::class, $participant_section2);
        static::assert_ps_progress(ps_not_started::class, $participant_section3);
        static::assert_ps_availability(ps_open::class, $participant_section1);
        static::assert_ps_availability(ps_open::class, $participant_section2);
        static::assert_ps_availability(ps_open::class, $participant_section3);

        static::assert_pi_progress(pi_not_started::class, $participant_instance);
        static::assert_pi_availability(pi_open::class, $participant_instance);

        // Complete first section.
        $this->complete_section($participant_section1);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_not_started::class, $participant_section2);
        static::assert_ps_progress(ps_not_started::class, $participant_section3);
        static::assert_ps_availability(ps_open::class, $participant_section1);
        static::assert_ps_availability(ps_open::class, $participant_section2);
        static::assert_ps_availability(ps_open::class, $participant_section3);

        static::assert_pi_progress(pi_in_progress::class, $participant_instance);
        static::assert_pi_availability(pi_open::class, $participant_instance);

        // Complete second section.
        $this->complete_section($participant_section2);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_complete::class, $participant_section2);
        static::assert_ps_progress(ps_not_started::class, $participant_section3);
        static::assert_ps_availability(ps_open::class, $participant_section1);
        static::assert_ps_availability(ps_open::class, $participant_section2);
        static::assert_ps_availability(ps_open::class, $participant_section3);

        static::assert_pi_progress(pi_in_progress::class, $participant_instance);
        static::assert_pi_availability(pi_open::class, $participant_instance);

        // Complete third section.
        $this->complete_section($participant_section3);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_complete::class, $participant_section2);
        static::assert_ps_progress(ps_complete::class, $participant_section3);
        static::assert_ps_availability(ps_open::class, $participant_section1);
        static::assert_ps_availability(ps_open::class, $participant_section2);
        static::assert_ps_availability(ps_open::class, $participant_section3);

        static::assert_pi_progress(pi_complete::class, $participant_instance);
        static::assert_pi_availability(pi_open::class, $participant_instance);
    }

    public function test_closure_settings_both_enabled(): void {
        [
            $activity,
            $participant_instance,
            $participant_section1,
            $participant_section2,
            $participant_section3,
        ] = $this->create_multi_section_activity();

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => true,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
        ]);

        // Complete first section.
        $this->complete_section($participant_section1);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_not_started::class, $participant_section2);
        static::assert_ps_progress(ps_not_started::class, $participant_section3);
        static::assert_ps_availability(ps_closed::class, $participant_section1);
        static::assert_ps_availability(ps_open::class, $participant_section2);
        static::assert_ps_availability(ps_open::class, $participant_section3);

        static::assert_pi_progress(pi_in_progress::class, $participant_instance);
        static::assert_pi_availability(pi_open::class, $participant_instance);

        // Complete second section.
        $this->complete_section($participant_section2);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_complete::class, $participant_section2);
        static::assert_ps_progress(ps_not_started::class, $participant_section3);
        static::assert_ps_availability(ps_closed::class, $participant_section1);
        static::assert_ps_availability(ps_closed::class, $participant_section2);
        static::assert_ps_availability(ps_open::class, $participant_section3);

        static::assert_pi_progress(pi_in_progress::class, $participant_instance);
        static::assert_pi_availability(pi_open::class, $participant_instance);

        // Complete third section.
        $this->complete_section($participant_section3);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_complete::class, $participant_section2);
        static::assert_ps_progress(ps_complete::class, $participant_section3);
        static::assert_ps_availability(ps_closed::class, $participant_section1);
        static::assert_ps_availability(ps_closed::class, $participant_section2);
        static::assert_ps_availability(ps_closed::class, $participant_section3);

        static::assert_pi_progress(pi_complete::class, $participant_instance);
        static::assert_pi_availability(pi_closed::class, $participant_instance);
    }


    public function test_only_section_closure_disabled(): void {
        [
            $activity,
            $participant_instance,
            $participant_section1,
            $participant_section2,
            $participant_section3,
        ] = $this->create_multi_section_activity();

        /** @var activity $activity */
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => true,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
        ]);

        // Complete first section.
        $this->complete_section($participant_section1);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_not_started::class, $participant_section2);
        static::assert_ps_progress(ps_not_started::class, $participant_section3);
        static::assert_ps_availability(ps_open::class, $participant_section1);
        static::assert_ps_availability(ps_open::class, $participant_section2);
        static::assert_ps_availability(ps_open::class, $participant_section3);

        static::assert_pi_progress(pi_in_progress::class, $participant_instance);
        static::assert_pi_availability(pi_open::class, $participant_instance);

        // Complete second section.
        $this->complete_section($participant_section2);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_complete::class, $participant_section2);
        static::assert_ps_progress(ps_not_started::class, $participant_section3);
        static::assert_ps_availability(ps_open::class, $participant_section1);
        static::assert_ps_availability(ps_open::class, $participant_section2);
        static::assert_ps_availability(ps_open::class, $participant_section3);

        static::assert_pi_progress(pi_in_progress::class, $participant_instance);
        static::assert_pi_availability(pi_open::class, $participant_instance);

        // Complete third section.
        $this->complete_section($participant_section3);

        static::assert_ps_progress(ps_complete::class, $participant_section1);
        static::assert_ps_progress(ps_complete::class, $participant_section2);
        static::assert_ps_progress(ps_complete::class, $participant_section3);
        static::assert_ps_availability(ps_closed::class, $participant_section1);
        static::assert_ps_availability(ps_closed::class, $participant_section2);
        static::assert_ps_availability(ps_closed::class, $participant_section3);

        static::assert_pi_progress(pi_complete::class, $participant_instance);
        static::assert_pi_availability(pi_closed::class, $participant_instance);
    }

    public function test_set_invalid_closure_settings_combination(): void {
        [$activity] = $this->create_multi_section_activity();

        // Assert initial settings - both are false.
        static::assertFalse((bool)$activity->settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION));
        static::assertFalse((bool)$activity->settings->lookup(activity_setting::CLOSE_ON_COMPLETION));

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Cannot disable CLOSE_ON_COMPLETION and enable CLOSE_ON_SECTION_SUBMISSION at the same time.');

        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
            activity_setting::CLOSE_ON_COMPLETION => false,
        ]);
    }

    public static function set_only_close_on_section_submission_provider(): array {
        return [
            [true, true, true, true, true],
            [true, true, false, false, true],
            [false, false, true, true, true],
            [false, false, false, false, false],
            [false, true, true, true, true],
            [false, true, false, false, true],
        ];
    }

    /**
     * Make sure we can't end up in an invalid state when switching CLOSE_ON_SECTION_SUBMISSION
     *
     * @dataProvider set_only_close_on_section_submission_provider
     * @param bool $initial_close_on_section_submission
     * @param bool $initial_close_on_completion
     * @param bool $new_value_close_on_section_submission
     * @param bool $expected_close_on_section_submission
     * @param bool $expected_close_on_completion
     * @return void
     */
    public function test_set_only_close_on_section_submission(
        bool $initial_close_on_section_submission,
        bool $initial_close_on_completion,
        bool $new_value_close_on_section_submission,
        bool $expected_close_on_section_submission,
        bool $expected_close_on_completion
    ): void {
        [$activity] = $this->create_multi_section_activity();

        // Set initial combination.
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => $initial_close_on_section_submission,
            activity_setting::CLOSE_ON_COMPLETION => $initial_close_on_completion,
        ]);

        // Assert initial setting
        static::assertSame($initial_close_on_section_submission, (bool)$activity->settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION));
        static::assertSame($initial_close_on_completion, (bool)$activity->settings->lookup(activity_setting::CLOSE_ON_COMPLETION));

        // Change only CLOSE_ON_SECTION_SUBMISSION.
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => $new_value_close_on_section_submission,
        ]);

        // Assert expected results.
        static::assertSame($expected_close_on_section_submission, (bool)$activity->settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION));
        static::assertSame($expected_close_on_completion, (bool)$activity->settings->lookup(activity_setting::CLOSE_ON_COMPLETION));
    }

    public static function set_only_close_on_completion_provider(): array {
        return [
            [true, true, true, true, true],
            [true, true, false, false, false],
            [false, false, true, false, true],
            [false, false, false, false, false],
            [false, true, true, false, true],
            [false, true, false, false, false],
        ];
    }

    /**
     * Make sure we can't end up in an invalid state when switching CLOSE_ON_COMPLETION
     *
     * @dataProvider set_only_close_on_completion_provider
     * @param bool $initial_close_on_section_submission
     * @param bool $initial_close_on_completion
     * @param bool $new_value_close_on_completion
     * @param bool $expected_close_on_section_submission
     * @param bool $expected_close_on_completion
     * @return void
     */
    public function test_set_only_close_on_completion(
        bool $initial_close_on_section_submission,
        bool $initial_close_on_completion,
        bool $new_value_close_on_completion,
        bool $expected_close_on_section_submission,
        bool $expected_close_on_completion
    ): void {
        [$activity] = $this->create_multi_section_activity();

        // Set initial combination.
        $activity->settings->update([
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => $initial_close_on_section_submission,
            activity_setting::CLOSE_ON_COMPLETION => $initial_close_on_completion,
        ]);

        // Assert initial setting
        static::assertSame($initial_close_on_section_submission, (bool)$activity->settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION));
        static::assertSame($initial_close_on_completion, (bool)$activity->settings->lookup(activity_setting::CLOSE_ON_COMPLETION));

        // Change only CLOSE_ON_COMPLETION.
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => $new_value_close_on_completion,
        ]);

        // Assert expected results.
        static::assertSame($expected_close_on_section_submission, (bool)$activity->settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION));
        static::assertSame($expected_close_on_completion, (bool)$activity->settings->lookup(activity_setting::CLOSE_ON_COMPLETION));
    }

    /**
     * @param string $state_class
     * @param participant_section_model $participant_section
     * @return void
     */
    private static function assert_ps_progress(string $state_class, participant_section_model $participant_section): void {
        $participant_section_refreshed = participant_section_model::load_by_id($participant_section->id);
        static::assertInstanceOf($state_class, $participant_section_refreshed->get_progress_state());
    }

    /**
     * @param string $state_class
     * @param participant_section_model $participant_section
     * @return void
     */
    private static function assert_ps_availability(string $state_class, participant_section_model $participant_section): void {
        $participant_section_refreshed = participant_section_model::load_by_id($participant_section->id);
        static::assertInstanceOf($state_class, $participant_section_refreshed->get_availability_state());
    }

    /**
     * @param string $state_class
     * @param participant_instance $participant_instance
     * @return void
     */
    private static function assert_pi_progress(string $state_class, participant_instance $participant_instance): void {
        $participant_instance_refreshed = participant_instance::load_by_id($participant_instance->id);
        static::assertInstanceOf($state_class, $participant_instance_refreshed->get_progress_state());
    }

    /**
     * @param string $state_class
     * @param participant_instance $participant_instance
     * @return void
     */
    private static function assert_pi_availability(string $state_class, participant_instance $participant_instance): void {
        $participant_instance_refreshed = participant_instance::load_by_id($participant_instance->id);
        static::assertInstanceOf($state_class, $participant_instance_refreshed->get_availability_state());
    }


}
