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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package mod_perform
 */

use core\collection;
use core_phpunit\testcase;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\helpers\participation_sync_settings_helper;
use mod_perform\models\activity\settings\controls\sync_participant_instance_closure_option;
use mod_perform\testing\generator as perform_generator;

/**
 * Class participant_instance_creation_service_test
 *
 * @group perform
 */
class mod_perform_participation_sync_settings_helper_test extends testcase {
    public function test_get_instance_closure_sync_type(): void {
        self::setAdminUser();

        $perform_generator = perform_generator::instance();

        $subject_instance = $perform_generator->create_subject_instance([
            'activity_name' => 'Weekly ketchup',
            'subject_user_id' => static::getDataGenerator()->create_user()->id,
        ]);

        $activity = $subject_instance->activity();

        // Default: not enabled.
        $helper = participation_sync_settings_helper::create_from_subject_instances(new collection([$subject_instance]));
        static::assertEquals(
            sync_participant_instance_closure_option::CLOSURE_DISABLED,
            $helper->get_instance_closure_sync_type($activity->id)
        );

        // Change to setting 1.
        $setting = sync_participant_instance_closure_option::CLOSE_NOT_STARTED_ONLY;
        set_config('perform_sync_participant_instance_closure', $setting->value);
        $helper = participation_sync_settings_helper::create_from_subject_instances(new collection([$subject_instance]));
        static::assertEquals($setting, $helper->get_instance_closure_sync_type($activity->id));

        // Change to setting 2.
        $setting = sync_participant_instance_closure_option::CLOSE_ALL;
        set_config('perform_sync_participant_instance_closure', $setting->value);
        $helper = participation_sync_settings_helper::create_from_subject_instances(new collection([$subject_instance]));
        static::assertEquals($setting, $helper->get_instance_closure_sync_type($activity->id));
    }

    public function test_should_instance_closure_be_synced(): void {
        self::setAdminUser();

        $perform_generator = perform_generator::instance();

        $subject_instance = $perform_generator->create_subject_instance([
            'activity_name' => 'Weekly ketchup',
            'subject_user_id' => static::getDataGenerator()->create_user()->id,
        ]);

        $activity = $subject_instance->activity();

        // Default: not enabled.
        $helper = participation_sync_settings_helper::create_from_subject_instances(new collection([$subject_instance]));
        static::assertFalse($helper->should_instance_closure_be_synced($activity->id));

        // Change to setting 1.
        set_config('perform_sync_participant_instance_closure', 1);
        $helper = participation_sync_settings_helper::create_from_subject_instances(new collection([$subject_instance]));
        static::assertTrue($helper->should_instance_closure_be_synced($activity->id));

        // Change to setting 2.
        set_config('perform_sync_participant_instance_closure', 2);
        $helper = participation_sync_settings_helper::create_from_subject_instances(new collection([$subject_instance]));
        static::assertTrue($helper->should_instance_closure_be_synced($activity->id));
    }

    public function test_get_instance_closure_sync_type_with_overrides(): void {
        self::setAdminUser();

        $perform_generator = perform_generator::instance();

        $subject_instance = $perform_generator->create_subject_instance([
            'activity_name' => 'Weekly ketchup',
            'subject_user_id' => static::getDataGenerator()->create_user()->id,
        ]);

        $activity = activity::load_by_entity($subject_instance->activity());

        // Enable override.
        $activity->settings->update([
            'override_global_participation_settings' => 1,
            'sync_participant_instance_closure' => 2,
        ]);
        $helper = participation_sync_settings_helper::create_from_subject_instances(new collection([$subject_instance]));
        static::assertEquals(
            sync_participant_instance_closure_option::CLOSE_ALL,
            $helper->get_instance_closure_sync_type($activity->id)
        );

        // Disable override.
        $activity->settings->update([
            'override_global_participation_settings' => 0,
        ]);

        $helper = participation_sync_settings_helper::create_from_subject_instances(new collection([$subject_instance]));
        static::assertEquals(
            sync_participant_instance_closure_option::CLOSURE_DISABLED,
            $helper->get_instance_closure_sync_type($activity->id)
        );
    }
}
