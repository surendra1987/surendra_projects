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
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\models\activity\activity_setting;

/**
 * @see \mod_perform\webapi\resolver\mutation\update_activity_closure_settings::resolve()
 * @group perform
 */
class mod_perform_webapi_resolver_mutation_update_activity_closure_settings_test extends testcase {
    private const MUTATION = 'mod_perform_update_activity_closure_settings';

    private const WRONG_COUNT = 'wrong settings count';
    private const WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE = 'wrong close on section submission setting value';
    private const WRONG_CLOSE_ON_COMPLETION_VALUE = 'wrong close on completion setting value';
    private const WRONG_CLOSE_ON_DUE_DATE_VALUE = 'wrong close on due date setting value';
    private const WRONG_MANUAL_CLOSE_SETTING_VALUE = 'wrong manual close setting value';

    use webapi_phpunit_helper;

    public function test_change_settings(): void {
        static::setAdminUser();

        $perform_generator = \mod_perform\testing\generator::instance();
        $activity = $perform_generator->create_activity_in_container();

        $settings = $activity->settings;
        $this->assertEquals(0, $settings->get()->count(), self::WRONG_COUNT);
        $this->assertFalse(
            (bool)$settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertFalse(
            (bool)$settings->lookup(activity_setting::CLOSE_ON_COMPLETION),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertFalse(
            (bool)$settings->lookup(activity_setting::CLOSE_ON_DUE_DATE),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertFalse(
            (bool)$settings->lookup(activity_setting::MANUAL_CLOSE),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
                activity_setting::CLOSE_ON_COMPLETION => false,
                activity_setting::CLOSE_ON_DUE_DATE => false,
                activity_setting::MANUAL_CLOSE => false
            ],
        ]);

        $this->assertEquals(4, $result->get()->count(), self::WRONG_COUNT);
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION, true),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_COMPLETION, true),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_DUE_DATE, true),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::MANUAL_CLOSE, true),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
                activity_setting::CLOSE_ON_COMPLETION => false,
                activity_setting::CLOSE_ON_DUE_DATE => true,
                activity_setting::MANUAL_CLOSE => true
            ],
        ]);
        $this->assertEquals(4, $result->get()->count(), self::WRONG_COUNT);
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION, false),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_COMPLETION, false),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_DUE_DATE, false),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::MANUAL_CLOSE, false),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
                activity_setting::CLOSE_ON_COMPLETION => true,
                activity_setting::CLOSE_ON_DUE_DATE => false,
                activity_setting::MANUAL_CLOSE => true
            ],
        ]);
        $this->assertEquals(4, $result->get()->count(), self::WRONG_COUNT);
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION, true),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_COMPLETION, false),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_DUE_DATE, false),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::MANUAL_CLOSE, false),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );
    }

    public function test_close_on_due_date_only(): void {
        static::setAdminUser();

        $perform_generator = \mod_perform\testing\generator::instance();
        $activity = $perform_generator->create_activity_in_container();
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
                activity_setting::CLOSE_ON_COMPLETION => true,
                activity_setting::CLOSE_ON_DUE_DATE => false,
                activity_setting::MANUAL_CLOSE => true
            ],
        ]);
        $this->assertEquals(4, $result->get()->count(), self::WRONG_COUNT);
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION, true),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_COMPLETION, false),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_DUE_DATE, false),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::MANUAL_CLOSE, false),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );

        // Lest change just due date and test all other values are not changed.
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_DUE_DATE => true,
            ],
        ]);
        $this->assertEquals(4, $result->get()->count(), self::WRONG_COUNT);
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION, true),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_COMPLETION, false),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_DUE_DATE, false),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::MANUAL_CLOSE, false),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );
    }

    public function test_with_optional_inputs(): void {
        static::setAdminUser();

        $perform_generator = \mod_perform\testing\generator::instance();
        $activity = $perform_generator->create_activity_in_container();

        $settings = $activity->settings;
        $this->assertEquals(0, $settings->get()->count(), self::WRONG_COUNT);
        $this->assertFalse(
            (bool)$settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertFalse(
            (bool)$settings->lookup(activity_setting::CLOSE_ON_COMPLETION),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertFalse(
            (bool)$settings->lookup(activity_setting::CLOSE_ON_DUE_DATE),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertFalse(
            (bool)$settings->lookup(activity_setting::MANUAL_CLOSE),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
                activity_setting::CLOSE_ON_COMPLETION => false,
                activity_setting::CLOSE_ON_DUE_DATE => false,
                activity_setting::MANUAL_CLOSE => false
            ],
        ]);
        $this->assert_result($result);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_COMPLETION => false,
                activity_setting::CLOSE_ON_DUE_DATE => false,
                activity_setting::MANUAL_CLOSE => false
            ],
        ]);
        $this->assert_result($result);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
                activity_setting::CLOSE_ON_DUE_DATE => false,
                activity_setting::MANUAL_CLOSE => false
            ],
        ]);
        $this->assert_result($result);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
                activity_setting::CLOSE_ON_COMPLETION => false,
                activity_setting::MANUAL_CLOSE => false
            ],
        ]);
        $this->assert_result($result);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
                activity_setting::CLOSE_ON_COMPLETION => false,
                activity_setting::CLOSE_ON_DUE_DATE => false,
            ],
        ]);
        $this->assert_result($result);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_DUE_DATE => false,
                activity_setting::MANUAL_CLOSE => false
            ],
        ]);
        $this->assert_result($result);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
            ],
        ]);
        $this->assertEquals(4, $result->get()->count(), self::WRONG_COUNT);
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION, true),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertTrue(
            (bool)$result->lookup(activity_setting::CLOSE_ON_COMPLETION, true),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_DUE_DATE, true),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::MANUAL_CLOSE, true),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );
    }

    public function test_successful_ajax_call(): void {
        static::setAdminUser();

        $perform_generator = \mod_perform\testing\generator::instance();
        $activity = $perform_generator->create_activity_in_container();

        $this->assertEquals(0, $activity->settings->get()->count(), self::WRONG_COUNT);

        $result = $this->parsed_graphql_operation(self::MUTATION, [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
                activity_setting::CLOSE_ON_COMPLETION => true,
                activity_setting::CLOSE_ON_DUE_DATE => true,
                activity_setting::MANUAL_CLOSE => false
            ],
        ]);
        $this->assert_webapi_operation_successful($result);

        $settings = $this->get_webapi_operation_data($result);
        $this->assertEquals(
            '1', // true
            $settings[activity_setting::CLOSE_ON_SECTION_SUBMISSION],
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertEquals(
            '1', // true
            $settings[activity_setting::CLOSE_ON_COMPLETION],
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertEquals(
            '1', // true
            $settings[activity_setting::CLOSE_ON_DUE_DATE],
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertEquals(
            '0', // false
            $settings[activity_setting::MANUAL_CLOSE],
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );
    }

    public function test_failed_ajax_call(): void {
        static::setAdminUser();

        $perform_generator = \mod_perform\testing\generator::instance();
        $activity = $perform_generator->create_activity_in_container();

        $args = [
            'input' => [
                'activity_id' => $activity->id,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => true,
                activity_setting::CLOSE_ON_COMPLETION => true,
                activity_setting::CLOSE_ON_DUE_DATE => true,
                activity_setting::MANUAL_CLOSE => false
            ],
        ];

        $feature = 'performance_activities';
        advanced_feature::disable($feature);
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed($result, 'Feature performance_activities is not available.');
        advanced_feature::enable($feature);

        $result = $this->parsed_graphql_operation(self::MUTATION, []);
        $this->assert_webapi_operation_failed($result, 'Variable "$input" of required type "mod_perform_activity_closure_settings_input!" was not provided.');

        $activity_id = 999;
        $args['input']['activity_id'] = $activity_id;
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed($result, "Invalid activity");

        static::setGuestUser();
        $args['input']['activity_id'] = $activity->id;
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed($result, 'Invalid activity');
    }

    private function assert_result(mixed $result): void {
        $this->assertEquals(4, $result->get()->count(), self::WRONG_COUNT);
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION, true),
            self::WRONG_CLOSE_ON_SECTION_SUBMISSION_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_COMPLETION, true),
            self::WRONG_CLOSE_ON_COMPLETION_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::CLOSE_ON_DUE_DATE, true),
            self::WRONG_CLOSE_ON_DUE_DATE_VALUE
        );
        $this->assertFalse(
            (bool)$result->lookup(activity_setting::MANUAL_CLOSE, true),
            self::WRONG_MANUAL_CLOSE_SETTING_VALUE
        );
    }
}
