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
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\settings\visibility_conditions\all_responses;
use mod_perform\models\activity\settings\visibility_conditions\own_response;
use mod_perform\state\activity\draft;
use mod_perform\testing\generator;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform
 */
class mod_perform_webapi_resolver_mutation_update_activity_visibility_settings_test extends testcase {
    private const MUTATION = 'mod_perform_update_activity_visibility_settings';

    use webapi_phpunit_helper;

    public function test_user_cannot_update_without_permission(): void {
        [, $args] = $this->create_activity();

        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed($result, 'Invalid activity');
     }

    public function test_update_success(): void {
        [, $args] = $this->create_activity();

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);
        self::assert_base_update_result($args['input'], $result);
    }

    /**
     * Test update invalid visibility condition will set to own_response::VALUE
     */
    public function test_invalid_visibility_control_value() {
        [, $args] = $this->create_activity();
        $args['input']['anonymous_responses'] = false;
        $args['input']['visibility_condition'] = 5; // Invalid value

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed(
            $result,
            'Coding error detected, it must be fixed by a programmer: invalid visibility condition value: 5'
        );
    }

    /**
     * Test turn on anonymous responses will set visibility control to all responses closed
     */
    public function test_turn_on_anonymous_responses_should_set_visibility_control_to_all_responses_closed() {
        [$activity, $args] = $this->create_activity();
        $args['visibility_condition'] = own_response::VALUE;

        $returned_settings = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($returned_settings);

        $activity_setting = activity_setting::load_by_name($activity->get_id(), activity_setting::VISIBILITY_CONDITION);

        $this->assertEquals(all_responses::VALUE, $activity_setting->value);
    }

    public function test_failed_query(): void {
        [, $args] = $this->create_activity();

        $feature = 'performance_activities';
        advanced_feature::disable($feature);
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed($result, 'Feature performance_activities is not available.');
        advanced_feature::enable($feature);
        $args['input'] = [];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed($result, 'activity_id');
    }

    private function create_activity(?stdClass $as_user = null): array {
        if ($as_user) {
            self::setUser($as_user);
        } else {
            self::setAdminUser();
        }

        $perform_generator = generator::instance();
        $activity = $perform_generator->create_activity_in_container(['activity_status' => draft::get_code()]);

        $args['input'] = [
            'activity_id' => $activity->id,
            'anonymous_responses' => true,
            'visibility_condition' => all_responses::VALUE
        ];

        return [$activity, $args];
    }

    /**
     * @param array $args
     * @param array $returned_settings
     */
    private static function assert_base_update_result(array $args, array $returned_settings): void {
        $returned_settings = (object) $returned_settings[0];
        $activity_setting = activity_setting::load_by_name($args['activity_id'], activity_setting::VISIBILITY_CONDITION);
        self::assertEquals($args['visibility_condition'], $activity_setting->value);
        self::assertEquals($args['anonymous_responses'], $returned_settings->anonymous_responses);
        self::assertEquals($args['visibility_condition'], $returned_settings->visibility_condition['value']);
        self::assertTrue($returned_settings->anonymous_responses);
    }
}
