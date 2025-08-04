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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\models\activity\activity;
use mod_perform\entity\activity\activity as activity_entity;
use mod_perform\models\activity\activity_setting;
use mod_perform\state\activity\draft;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\testing\generator as perform_generator;
use mod_perform\usagedata\count_of_activities_by_setting;

defined('MOODLE_INTERNAL') || die();

/**
 * @group perform
 */
class mod_perform_usagedata_count_of_activities_by_setting_test extends testcase {

    public function test_empty_result(): void {
        static::assertEquals([
            'manual_close' => 0,
            'close_on_completion' => 0,
            'close_on_due_date' => 0,
            'multiple_closure_methods' => 0,
            'sync_participant_instance_closure_disabled' => 0,
            'sync_participant_instance_closure_close_not_started_only' => 0,
            'sync_participant_instance_closure_close_all' => 0,
        ], (new count_of_activities_by_setting())->export());
    }

    public function test_closure_counts(): void {
        static::setAdminUser();

        $generator = perform_generator::instance();
        $config = activity_generator_configuration::new()
            ->set_number_of_activities(3);
        /** @var activity $activity1 */
        /** @var activity $activity2 */
        /** @var activity $activity3 */
        [$activity1, $activity2, $activity3] = $generator->create_full_activities($config)->all();

        $activity1->settings->update([activity_setting::MANUAL_CLOSE => 1]);

        static::assertEquals([
            'manual_close' => 1,
            'close_on_completion' => 0,
            'close_on_due_date' => 0,
            'multiple_closure_methods' => 0,
        ], $this->get_closure_export());

        $activity2->settings->update([activity_setting::MANUAL_CLOSE => 1]);

        static::assertEquals([
            'manual_close' => 2,
            'close_on_completion' => 0,
            'close_on_due_date' => 0,
            'multiple_closure_methods' => 0,
        ], $this->get_closure_export());

        $activity3->settings->update([activity_setting::CLOSE_ON_COMPLETION => 1]);

        static::assertEquals([
            'manual_close' => 2,
            'close_on_completion' => 1,
            'close_on_due_date' => 0,
            'multiple_closure_methods' => 0,
        ], $this->get_closure_export());

        $activity2->settings->update([activity_setting::CLOSE_ON_COMPLETION => 1]);

        static::assertEquals([
            'manual_close' => 2,
            'close_on_completion' => 2,
            'close_on_due_date' => 0,
            'multiple_closure_methods' => 1,
        ], $this->get_closure_export());

        $activity2->settings->update([activity_setting::CLOSE_ON_DUE_DATE => 1]);

        static::assertEquals([
            'manual_close' => 2,
            'close_on_completion' => 2,
            'close_on_due_date' => 1,
            'multiple_closure_methods' => 1,
        ], $this->get_closure_export());

        $activity3->settings->update([activity_setting::MANUAL_CLOSE => 1]);

        static::assertEquals([
            'manual_close' => 3,
            'close_on_completion' => 2,
            'close_on_due_date' => 1,
            'multiple_closure_methods' => 2,
        ], $this->get_closure_export());

        // Revert some to disabled.
        $activity1->settings->update([activity_setting::MANUAL_CLOSE => 0]);
        $activity3->settings->update([activity_setting::MANUAL_CLOSE => 0]);

        static::assertEquals([
            'manual_close' => 1,
            'close_on_completion' => 2,
            'close_on_due_date' => 1,
            'multiple_closure_methods' => 1,
        ], $this->get_closure_export());

        // Enable a setting that is not counted here.
        $activity1->settings->update([activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE => 1]);
        $activity2->settings->update([activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE => 1]);
        $activity3->settings->update([activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE => 1]);

        static::assertEquals([
            'manual_close' => 1,
            'close_on_completion' => 2,
            'close_on_due_date' => 1,
            'multiple_closure_methods' => 1,
        ], $this->get_closure_export());

        // Set one activity to draft status. Should be excluded from the count.
        activity_entity::repository()->where('id', $activity2->id)->update(['status' => draft::get_code()]);

        static::assertEquals([
            'manual_close' => 0,
            'close_on_completion' => 1,
            'close_on_due_date' => 0,
            'multiple_closure_methods' => 0,
        ], $this->get_closure_export());
    }

    private function get_closure_export(): array {
        return $this->get_filtered_export([
            'manual_close',
            'close_on_completion',
            'close_on_due_date',
            'multiple_closure_methods',
        ]);
    }

    private function get_sync_export(): array {
        return $this->get_filtered_export([
            'sync_participant_instance_closure_disabled',
            'sync_participant_instance_closure_close_not_started_only',
            'sync_participant_instance_closure_close_all',
        ]);
    }

    private function get_filtered_export(array $filter_by_keys): array {
        return array_filter(
            (new count_of_activities_by_setting())->export(),
            static fn ($key): bool => in_array($key, $filter_by_keys),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function test_sync_counts_no_overrides(): void {
        static::setAdminUser();

        $generator = perform_generator::instance();
        $config = activity_generator_configuration::new()
            ->set_number_of_activities(3);
        /** @var activity $activity1 */
        /** @var activity $activity2 */
        /** @var activity $activity3 */
        [$activity1, $activity2, $activity3] = $generator->create_full_activities($config)->all();

        // Default global setting is disabled.
        static::assertEquals([
            'sync_participant_instance_closure_disabled' => 3,
            'sync_participant_instance_closure_close_not_started_only' => 0,
            'sync_participant_instance_closure_close_all' => 0,
        ], $this->get_sync_export());

        set_config('perform_sync_participant_instance_closure', 1);

        static::assertEquals([
            'sync_participant_instance_closure_disabled' => 0,
            'sync_participant_instance_closure_close_not_started_only' => 3,
            'sync_participant_instance_closure_close_all' => 0,
        ], $this->get_sync_export());

        set_config('perform_sync_participant_instance_closure', 2);

        static::assertEquals([
            'sync_participant_instance_closure_disabled' => 0,
            'sync_participant_instance_closure_close_not_started_only' => 0,
            'sync_participant_instance_closure_close_all' => 3,
        ], $this->get_sync_export());
    }

    public function test_sync_counts_with_overrides(): void {
        static::setAdminUser();

        $generator = perform_generator::instance();
        $config = activity_generator_configuration::new()
            ->set_number_of_activities(3);
        /** @var activity $activity1 */
        /** @var activity $activity2 */
        /** @var activity $activity3 */
        [$activity1, $activity2, $activity3] = $generator->create_full_activities($config)->all();

        $activity1->settings->update([activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE => 1]);
        // No change because the override flag is not enabled.
        static::assertEquals([
            'sync_participant_instance_closure_disabled' => 3,
            'sync_participant_instance_closure_close_not_started_only' => 0,
            'sync_participant_instance_closure_close_all' => 0,
        ], $this->get_sync_export());

        // Enable the override flag.
        $activity1->settings->update([activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS => 1]);
        static::assertEquals([
            'sync_participant_instance_closure_disabled' => 2,
            'sync_participant_instance_closure_close_not_started_only' => 1,
            'sync_participant_instance_closure_close_all' => 0,
        ], $this->get_sync_export());

        // Override & set sync closure for activity 2
        $activity2->settings->update([activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS => 1]);
        $activity2->settings->update([activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE => 2]);
        static::assertEquals([
            'sync_participant_instance_closure_disabled' => 1,
            'sync_participant_instance_closure_close_not_started_only' => 1,
            'sync_participant_instance_closure_close_all' => 1,
        ], $this->get_sync_export());

        // Mix with changing global default
        set_config('perform_sync_participant_instance_closure', 2);
        static::assertEquals([
            'sync_participant_instance_closure_disabled' => 0,
            'sync_participant_instance_closure_close_not_started_only' => 1,
            'sync_participant_instance_closure_close_all' => 2,
        ], $this->get_sync_export());

        // Override but no sync closure setting -> no change
        $activity3->settings->update([activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS => 1]);
        static::assertEquals([
            'sync_participant_instance_closure_disabled' => 0,
            'sync_participant_instance_closure_close_not_started_only' => 1,
            'sync_participant_instance_closure_close_all' => 2,
        ], $this->get_sync_export());
    }
}
