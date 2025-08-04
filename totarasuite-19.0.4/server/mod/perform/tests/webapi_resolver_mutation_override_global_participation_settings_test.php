<?php
/**
 * This file is part of Totara Perform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totaralearning.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\settings\controls\sync_participant_instance_closure_option;
use mod_perform\models\activity\settings\controls\sync_participant_instance_creation_option;
use mod_perform\testing\generator;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \mod_perform\webapi\resolver\mutation\override_global_participation_settings
 *
 * @group perform
 */
class mod_perform_webapi_resolver_mutation_override_global_participation_settings_test extends testcase {
    private const MUTATION = 'mod_perform_override_global_participation_settings';

    use webapi_phpunit_helper;

    /**
     * Test the mutation through the GraphQL stack.
     */
    public function test_execute_query_successful(): void {
        /** @var mod_perform\models\activity\activity $activity */
        [$activity, $args] = $this->create_activity();

        $settings = $activity->settings;
        self::assertFalse((bool)$settings->lookup(activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS));
        self::assertFalse((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION));
        self::assertFalse((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE));

        $args['input'][activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE] = true;

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertEquals([
            'override_global_participation_settings' => true,
            'sync_participant_instance_creation_type' => 1,
            'sync_participant_instance_closure_type' =>
                sync_participant_instance_closure_option::CLOSE_NOT_STARTED_ONLY->name,
            'sync_participant_instance_creation' => true,
            'sync_participant_instance_closure' => true,
        ], $result);

        $activity->refresh();
        $settings = $activity->settings;

        self::assertTrue((bool)$settings->lookup(activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS));
        self::assertTrue((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION));
        self::assertTrue((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE));

        // Two debugging calls expected for deprecated input keys.
        self::assertDebuggingCalledCount(2);
    }

    public function test_override_global_participation_settings(): void {
        /** @var mod_perform\models\activity\activity $activity */
        [$activity, $args] = $this->create_activity();

        set_config('perform_sync_participant_instance_creation', 0);
        set_config('perform_sync_participant_instance_closure', 0);

        $args['input'][activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS] = false;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE] = true;

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        // When override is turned off, the globals must be adopted.
        self::assertEquals($result, [
            'override_global_participation_settings' => false,
            'sync_participant_instance_creation_type' => 0,
            'sync_participant_instance_closure_type' =>
                sync_participant_instance_closure_option::CLOSURE_DISABLED->name,
            'sync_participant_instance_creation' => false,
            'sync_participant_instance_closure' => false,
        ]);
        $settings = $activity->settings;

        self::assertFalse((bool)$settings->lookup(activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS));
        self::assertFalse((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION));
        self::assertFalse((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE));

        set_config('perform_sync_participant_instance_creation', 1);
        set_config('perform_sync_participant_instance_closure', 0);

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        self::assertEquals($result, [
            'override_global_participation_settings' => false,
            'sync_participant_instance_creation_type' => 1,
            'sync_participant_instance_closure_type' =>
                sync_participant_instance_closure_option::CLOSURE_DISABLED->name,
            'sync_participant_instance_creation' => true,
            'sync_participant_instance_closure' => false,
        ]);
        $settings = $activity->settings;
        self::assertFalse((bool)$settings->lookup(activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS));
        self::assertTrue((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION));
        self::assertFalse((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE));

        // Two debugging calls per query expected for deprecated input keys.
        self::assertDebuggingCalledCount(4);
    }

    public function test_sync_participant_instance_creation(): void {
        /** @var mod_perform\models\activity\activity $activity */
        [$activity, $args] = $this->create_activity();

        $args['input'][activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE] = false;

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertEquals($result, [
            'override_global_participation_settings' => true,
            'sync_participant_instance_creation_type' => 1,
            'sync_participant_instance_closure_type' =>
                sync_participant_instance_closure_option::CLOSURE_DISABLED->name,
            'sync_participant_instance_creation' => true,
            'sync_participant_instance_closure' => false,
        ]);

        $activity->refresh();
        $settings = $activity->settings;

        self::assertTrue((bool)$settings->lookup(activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS));
        self::assertTrue((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION));
        self::assertFalse((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE));

        // Two debugging calls per query expected for deprecated input keys.
        self::assertDebuggingCalledCount(2);
    }

    public function test_sync_participant_instance_closure(): void {
        /** @var mod_perform\models\activity\activity $activity */
        [$activity, $args] = $this->create_activity();

        $args['input'][activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION] = false;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE] = true;

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertEquals($result, [
            'override_global_participation_settings' => true,
            'sync_participant_instance_creation_type' => 0,
            'sync_participant_instance_closure_type' =>
                sync_participant_instance_closure_option::CLOSE_NOT_STARTED_ONLY->name,
            'sync_participant_instance_creation' => false,
            'sync_participant_instance_closure' => true,
        ]);

        $activity->refresh();
        $settings = $activity->settings;

        self::assertTrue((bool)$settings->lookup(activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS));
        self::assertFalse((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION));
        self::assertTrue((bool)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE));

        // Two debugging calls per query expected for deprecated input keys.
        self::assertDebuggingCalledCount(2);
    }

    public function test_sync_participant_instance_creation_type_and_closure_type(): void {
        /** @var mod_perform\models\activity\activity $activity */
        [$activity, $args] = $this->create_activity();

        $args['input'][activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS] = true;
        $args['input']['sync_participant_instance_creation_type'] = sync_participant_instance_creation_option::CREATION_ENABLED->value;

        $pi_close_all = sync_participant_instance_closure_option::CLOSE_ALL->name;
        $args['input']['sync_participant_instance_closure_type'] = $pi_close_all;

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertEquals($result, [
            'override_global_participation_settings' => true,
            'sync_participant_instance_creation_type' => 1,
            'sync_participant_instance_closure_type' => $pi_close_all,
            'sync_participant_instance_creation' => true,
            'sync_participant_instance_closure' => true,
        ]);

        $activity->refresh();
        $settings = $activity->settings;

        self::assertTrue((bool)$settings->lookup(activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS));
        self::assertSame(1, (int)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION));
        self::assertSame(2, (int)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE));
    }

    public function test_failed_ajax_query_invalid_activity(): void {
        [$activity, $args] = $this->create_activity();
        $args['input']['activity_id'] = 999;
        $args['input'][activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE] = true;
        $this->expectException(moodle_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_failed_ajax_query_guestuser(): void {
        [$activity, $args] = $this->create_activity();
        $args['input'][activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION] = true;
        $args['input'][activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE] = true;

        self::setGuestUser();
        $this->expectException(require_login_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_failed_ajax_query_invalid_closure_type(): void {
        // Set up.
        [$activity, $args] = $this->create_activity();
        $args['input'][activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS] = true;
        $args['input']['sync_participant_instance_creation_type'] = 1;
        $args['input']['sync_participant_instance_closure_type'] = null; // not nullable

        // Assert.
        $this->expectException('TypeError');

        // Operate.
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    private function create_activity(): array {
        self::setAdminUser();

        /** @var \mod_perform\testing\generator $perform_generator */
        $perform_generator = generator::instance();
        $activity = $perform_generator->create_activity_in_container();

        $args = [
            'input' => [
                'activity_id' => $activity->id
            ]
        ];

        return [$activity, $args];
    }

}