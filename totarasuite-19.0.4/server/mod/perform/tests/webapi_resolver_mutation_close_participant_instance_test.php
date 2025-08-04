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
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\participant_instance;
use mod_perform\testing\generator;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\state\participant_section\complete;

/**
 * @group perform
 */
class mod_perform_webapi_resolver_mutation_close_participant_instance_test extends testcase {
    private const MUTATION = 'mod_perform_close_participant_instance';

    use webapi_phpunit_helper;

    public function test_closure_allowed(): void {
        $pi = self::setup_env();
        foreach ($pi->get_participant_sections() as $participant_section) {
            $participant_section->complete(); // closure_conditions verify_required_questions_completed requires this.
        }
        $this->assertFalse($pi->is_closed, 'participant instance already closed');

        $args = ['input' => ['participant_instance_id' => $pi->id]];

        self::setUser($pi->participant->user->to_record());
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        [
            'success' => $result,
            'error' => $error
        ] = $this->get_webapi_operation_data($result);
        self::assertTrue($result, 'manual closure failed');
        self::assertEmpty($error, "successful remove has error: $error'");

        $pi->refresh();
        $this->assertTrue(
            $pi->is_closed,
            'participant instance not closed'
        );

        // Check that manually closing the participant instance sets each section's progress to 'complete'.
        foreach ($pi->participant_sections as $section) {
            self::assertEquals(complete::get_code(), $section->progress);
        }
    }

    public function test_closure_disallowed(): void {
        // Disable manual closing.
        $pi = self::setup_env(false);
        $this->assertFalse($pi->is_closed, 'participant instance already closed');

        $args = ['input' => ['participant_instance_id' => $pi->id]];

        self::setUser($pi->participant->user->to_record());
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        [
            'success' => $result,
            'error' => $error
        ] = $this->get_webapi_operation_data($result);
        self::assertFalse($result, 'manual closure went through');

        self::assertEquals(
            get_string(
                'manual_closure_condition_result:not_applicable', 'mod_perform'
            ),
            $error
        );

        $this->assertFalse(
            participant_instance::load_by_id($pi->id)->is_closed,
            'participant instance closed'
        );
    }

    public function test_failed_ajax(): void {
        $pi = self::setup_env();
        $args = ['input' => ['participant_instance_id' => $pi->id]];

        $try = function (array $parameters, string $err): void {
            try {
                $result = $this->resolve_graphql_mutation(self::MUTATION, $parameters);
                self::fail(
                    'managed to close when it should have failed: ' . print_r($result, true)
                );
            } catch (moodle_exception $e) {
                self::assertStringContainsString($err, $e->getMessage());
            }
        };

        self::setUser($pi->participant->user->to_record());
        advanced_feature::disable('performance_activities');
        $try($args, 'Feature performance_activities is not available.');

        advanced_feature::enable('performance_activities');

        self::setGuestUser();
        $try($args, 'Must be an authenticated user');

        // This simulates the case where an 'external user' sneakily tries to
        // manually close a participant instance.
        self::setUser(null);
        $try($args, 'Course or activity not accessible. (You are not logged in)');

        self::setUser($pi->participant->user->to_record());
        $args['input']['participant_instance_id'] = 101;
        $try($args, "payload has invalid 'input.participant_instance_id' value");
    }

    /**
     * Generates test data.
     *
     * @param $manual_closure_allowed indicates whether the returned participant
     *        instance's activity's manual closure setting is enabled.
     *
     * @return participant_instance created participant_instance.
     */
    private static function setup_env(
        bool $manual_closure_allowed = true
    ): participant_instance {
        self::setAdminUser();

        $core_generator = self::getDataGenerator();
        $subject = $core_generator->create_user();

        $generator = generator::instance();
        $activity = $generator->create_activity_in_container();
        $activity->settings->update([
            activity_setting::MANUAL_CLOSE => $manual_closure_allowed]
        );

        $si = $generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_user_id' => $subject->id,
            'include_questions' => false,
        ]);

        $section = $activity->get_sections()->first();
        $generator->create_participant_instance_and_section(
            $activity,
            $subject,
            $si->id,
            $section,
            $generator->get_core_relationship(constants::RELATIONSHIP_SUBJECT)->id
        );

        $participant_section = $generator->create_participant_instance_and_section(
            $activity,
            $core_generator->create_user(),
            $si->id,
            $section,
            $generator->get_core_relationship(constants::RELATIONSHIP_PEER)->id
        );

        return participant_instance::load_by_entity(
            $participant_section->participant_instance
        );
    }
}
