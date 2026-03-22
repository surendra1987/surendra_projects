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

use core\webapi\middleware\reopen_session_for_writing;
use core\webapi\middleware\require_advanced_feature;
use core_phpunit\testcase;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\settings\controls\sync_participant_instance_closure_option;
use mod_perform\models\activity\settings\controls\sync_participant_instance_creation_option;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\webapi\middleware\require_activity;
use mod_perform\webapi\middleware\require_manage_capability;
use mod_perform\webapi\resolver\query\activity_controls;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/relationship_testcase.php');

/**
 * @coversDefaultClass \mod_perform\webapi\resolver\query\activity
 *
 * @group perform
 */
class mod_perform_webapi_resolver_query_activity_controls_test extends testcase {
    private const QUERY = 'mod_perform_activity_controls';

    use webapi_phpunit_helper;

    /**
     * Test the query through the GraphQL stack.
     */
    public function test_ajax_query_successful(): void {
        $activity = $this->create_activity();

        $args = [
            'activity_id' => $activity->id,
            'control_keys' => ['visibility']
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, ['input' => $args]);
        $this->assert_webapi_operation_successful($result);

        $result = $this->get_webapi_operation_data($result);
        $json = $result['controls'];
        $this->assertJson($json);

        self::assertTrue($result['success']);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(
            [
                'anonymous_responses' => [
                    'value' => false,
                    'mutable' => false,
                ],
                'valid_closure_state' => true,
                'visibility_condition' => [
                    'value' => 0,
                    'anonymous_value' => 2,
                    'options' => [
                        [
                            'name' => 'Show responses when the section is submitted',
                            'value' => 0,
                        ],
                        [
                            'name' => 'Show responses when the section is submitted and closed',
                            'value' => 1,
                        ],
                        [
                            'name' => 'Show responses when all participants have submitted and closed the activity',
                            'value' => 2,
                        ],
                    ]
                ],
            ],
            $decoded['visibility']
        );
    }

    /**
     * Test the query through the GraphQL stack.
     */
    public function test_ajax_query_sync_participation_successful(): void {
        $default_suffix = ' (global default)';
        $pi_sync_close_options = array_map(
            fn(sync_participant_instance_closure_option $it): array => [
                'id' => $it->name,
                'desc' => $it->description(),
                'label' => ($it->value === 0 ? $it->label() . $default_suffix : $it->label())
            ],
            sync_participant_instance_closure_option::cases()
        );

        $pi_sync_create_options = array_map(
            fn(sync_participant_instance_creation_option $it): array => [
                'id' => $it->value,
                'desc' => $it->description(),
                'label' => ($it->value === 0 ? $it->label() . $default_suffix : $it->label())
            ],
            sync_participant_instance_creation_option::cases()
        );

        $args = [
            'activity_id' => $this->create_activity()->id,
            'control_keys' => ['sync_participation']
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, ['input' => $args]);
        $this->assert_webapi_operation_successful($result);

        $result = $this->get_webapi_operation_data($result);
        $json = $result['controls'];
        $this->assertJson($json);

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(
            [
                'override_global_participation_settings' => false,
                'sync_participant_instance_creation_type' => 0,
                'sync_participant_instance_closure_type' =>
                    sync_participant_instance_closure_option::CLOSURE_DISABLED->name,
                'sync_participant_instance_closure_options' => $pi_sync_close_options,
                'sync_participant_instance_creation_options' => $pi_sync_create_options
            ],
            $decoded['sync_participation']
        );
    }

    public function test_get_middleware(): void {
        // Just check that the expected middleware is there. The middleware itself is tested elsewhere.
        $middlewares = activity_controls::get_middleware();

        $this->assertCount(4, $middlewares);
        $this->assertCount(1, array_filter(
            $middlewares,
            static fn ($middleware): bool => $middleware === reopen_session_for_writing::class
        ));
        $this->assertCount(1, array_filter(
            $middlewares,
            static fn ($middleware): bool => $middleware instanceof require_advanced_feature
        ));
        $this->assertCount(1, array_filter(
            $middlewares,
            static fn ($middleware): bool => $middleware instanceof require_activity
        ));
        $this->assertCount(1, array_filter(
            $middlewares,
            static fn ($middleware): bool => $middleware === require_manage_capability::class
        ));
    }

    private function create_activity(): activity {
        self::setAdminUser();
        $generator = self::getDataGenerator();
        /** @var \mod_perform\testing\generator $perform_generator */
        $perform_generator = $generator->get_plugin_generator('mod_perform');

        $configuration = activity_generator_configuration::new();

        $activities = $perform_generator->create_full_activities($configuration);
        /** @var activity $activity */
        return $activities->first();
    }

    public function test_ajax_query_check_closure_control(): void {
        $activity = $this->create_activity();
        $args = [
            'activity_id' => $activity->id,
            'control_keys' => ['closure']
        ];
        // Operate
        $result = $this->parsed_graphql_operation(self::QUERY, ['input' => $args]);
        $result = $this->get_webapi_operation_data($result);

        // Assert
        $json = $result['controls'];
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $closure_controls = $decoded['closure'];
        self::assertArrayHasKey('value', $closure_controls);
        // Check for close_on_section_submission setting.
        self::assertArrayHasKey('close_on_section_submission', $closure_controls['value']);
        self::assertFalse($closure_controls['value']['close_on_section_submission']);
        // Check for manual_close setting.
        self::assertArrayHasKey('checkbox_options', $closure_controls);
        $checkbox_manual_control_item_found_in_response = array_filter($closure_controls['checkbox_options'],
            fn($item) => ($item['id'] === 'manual_close' && $item['label'] === 'Manual close')
        );
        self::assertNotEmpty($checkbox_manual_control_item_found_in_response);
        self::assertArrayHasKey('manual_close', $closure_controls['value']);
        self::assertFalse($closure_controls['value']['manual_close']);
    }

    public function test_ajax_query_check_invalid_control_key_input(): void {
        $activity = $this->create_activity();
        $args = [
            'activity_id' => $activity->id,
            'control_keys' => ['random']
        ];
        // Operate
        $result = $this->parsed_graphql_operation(self::QUERY, ['input' => $args]);
        $result = $this->get_webapi_operation_data($result);

        self::assertFalse($result['success']);
        self::assertEquals("Invalid 'control_keys' value: 'random'.", $result['errors']['message']);
    }
}
