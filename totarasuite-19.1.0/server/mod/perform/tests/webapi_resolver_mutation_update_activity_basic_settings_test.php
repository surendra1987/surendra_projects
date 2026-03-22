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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\activity_type as activity_type_model;
use mod_perform\state\activity\draft;
use mod_perform\testing\generator;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform
 * Tests the mutation to create/update activity basic settings
 */
class mod_perform_webapi_resolver_mutation_update_activity_basic_settings_test extends testcase {
    private const MUTATION = 'mod_perform_update_activity_basic_settings';

    use webapi_phpunit_helper;

    public function test_user_cannot_update_without_permission(): void {
        [, $args] = $this->create_activity();

        $user = static::getDataGenerator()->create_user();
        static::setUser($user);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Invalid activity');

        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_update_success(): void {
        [, $args] = $this->create_activity();

        /** @var activity $activity */
        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        // Return values should be updated
        $this->assert_base_update_result($args, $result);
    }

    public function test_activity_must_belong_to_user(): void {
        $data_generator = static::getDataGenerator();

        $user1 = $data_generator->create_user();
        $user2 = $data_generator->create_user();

        [, $args] = $this->create_activity($user1);

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        $this->assert_base_update_result($args, $result);

        static::setUser($user2);
        $this->expectException(moodle_exception::class);

        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_successful_ajax_call(): void {
        [, $args] = $this->create_activity();

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        $result = $this->get_webapi_operation_data($result);
        $this->assertNotNull($result, 'null result');

        $this->assert_base_update_result($args, $result);

        $actual_type_display_name = $result['type']['display_name'];
        $expected_type_display_name = activity_type_model::load_by_id($args['input']['type_id'])->get_display_name();
        $this->assertEquals($actual_type_display_name, $expected_type_display_name);
    }

    public function test_failed_ajax_query(): void {
        [, $args] = $this->create_activity();

        $feature = 'performance_activities';
        advanced_feature::disable($feature);
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed($result, 'Feature performance_activities is not available.');
        advanced_feature::enable($feature);
    }

    private function create_activity(?stdClass $as_user = null): array {
        if ($as_user) {
            static::setUser($as_user);
        } else {
            static::setAdminUser();
        }

        $perform_generator = generator::instance();
        $activity = $perform_generator->create_activity_in_container(['activity_status' => draft::get_code()]);
        $new_type_id = 3;

        $args['input'] = [
            'activity_id' => $activity->id,
            'name' => "Activity-1",
            'description' => "Description of Activity 1",
            'type_id' => $new_type_id,
        ];

        return [$activity, $args];
    }

    /**
     * @param array $args
     * @param mixed $returned_activity
     */
    private function assert_base_update_result(array $args, mixed $returned_activity): void {
        $args = $args['input'];
        if (is_array($returned_activity)) {
            $returned_activity = (object) $returned_activity;
            $returned_activity->type = (object) $returned_activity->type;
        }
        static::assertEquals($returned_activity->id, $args['activity_id']);
        static::assertEquals($returned_activity->name, $args['name']);
        static::assertEquals($returned_activity->description, $args['description']);
        static::assertEquals($returned_activity->type->id, $args['type_id']);
    }
}
