<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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

use core_phpunit\testcase;
use mod_perform\data_providers\activity\activity_settings;
use mod_perform\testing\generator as perform_generator;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\models\activity\settings\controls\sync_participant_instance_closure_option;

/**
 * @group perform
 */
class mod_perform_webapi_type_activity_test extends testcase {

    use webapi_phpunit_helper;

    private const TYPE = 'mod_perform_activity';

    public function test_sync_participants_settings() {
        self::setAdminUser();

        $perform_generator = perform_generator::instance();
        $activity = $perform_generator->create_activity_in_container();

        $settings = $this->resolve_graphql_type(self::TYPE, 'settings', $activity, []);
        $pi_close_disabled = sync_participant_instance_closure_option::CLOSURE_DISABLED->name;
        $pi_close_unstarted = sync_participant_instance_closure_option::CLOSE_NOT_STARTED_ONLY->name;

        // All settings should default to false.
        self::assertFalse($settings['override_global_participation_settings']);
        self::assertFalse($settings['sync_participant_instance_creation']);
        self::assertFalse($settings['sync_participant_instance_closure']);

        // Globals should be observed because override_global is false.
        set_config('perform_sync_participant_instance_creation', 1);
        set_config('perform_sync_participant_instance_closure', 1);
        $settings = $this->resolve_graphql_type(self::TYPE, 'settings', $activity, []);
        self::assertFalse($settings['override_global_participation_settings']);
        self::assertTrue($settings['sync_participant_instance_creation']);
        self::assertTrue($settings['sync_participant_instance_closure']);
        self::assertEquals($pi_close_unstarted, $settings['sync_participant_instance_closure_type']);

        // Set override to true and globals should be ignored.
        $activity_settings = new activity_settings($activity);
        $activity_settings->update(['override_global_participation_settings' => true]);
        $settings = $this->resolve_graphql_type(self::TYPE, 'settings', $activity, []);
        self::assertTrue($settings['override_global_participation_settings']);
        self::assertFalse($settings['sync_participant_instance_creation']);
        self::assertFalse($settings['sync_participant_instance_closure']);
        self::assertEquals($pi_close_disabled, $settings['sync_participant_instance_closure_type']);

        // Just switch one local to make sure there's no mix up.
        $activity_settings = new activity_settings($activity);
        $activity_settings->update(['sync_participant_instance_creation' => true]);
        $settings = $this->resolve_graphql_type(self::TYPE, 'settings', $activity, []);
        self::assertTrue($settings['override_global_participation_settings']);
        self::assertTrue($settings['sync_participant_instance_creation']);
        self::assertFalse($settings['sync_participant_instance_closure']);
        self::assertEquals($pi_close_disabled, $settings['sync_participant_instance_closure_type']);

        // Switch one global and turn override off again.
        set_config('perform_sync_participant_instance_creation', 0);
        $activity_settings = new activity_settings($activity);
        $activity_settings->update(['override_global_participation_settings' => false]);
        $settings = $this->resolve_graphql_type(self::TYPE, 'settings', $activity, []);
        self::assertFalse($settings['override_global_participation_settings']);
        self::assertFalse($settings['sync_participant_instance_creation']);
        self::assertTrue($settings['sync_participant_instance_closure']);
        self::assertEquals($pi_close_unstarted, $settings['sync_participant_instance_closure_type']);
    }
}
