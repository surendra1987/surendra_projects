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
use mod_perform\constants;
use mod_perform\testing\generator;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\participant_instance;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\entity\activity\external_participant;
use mod_perform\models\activity\helpers\external_participant_token_validator;
use mod_perform\state\participant_section\complete;

/**
 * @group perform
 */
class mod_perform_webapi_resolver_mutation_close_participant_instance_by_external_nosession_test extends testcase {
    private const MUTATION = 'mod_perform_close_participant_instance_by_external_nosession';

    use webapi_phpunit_helper;

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_closure_disallowed(): void {
        // Disable manual closing.
        /** @var external_participant $external_participant */
        /** @var participant_instance $pi */
        [$external_participant, $pi] = $this->setup_external_data(false);

        $validator = new external_participant_token_validator($external_participant->token);
        static::assertTrue($validator->is_valid());
        static::assertFalse($pi->is_closed, 'participant instance already closed');

        $args = ['input' => ['token' => $external_participant->token]];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        [
            'success' => $result,
            'error' => $error
        ] = $this->get_webapi_operation_data($result);

        static::assertFalse($result, 'manual closure went through');
        static::assertEquals(
            get_string('manual_closure_condition_result:not_applicable', 'mod_perform'),
            $error
        );
        static::assertFalse(
            participant_instance::load_by_id($pi->id)->is_closed,
            'participant instance closed'
        );
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_failed_ajax(): void {
        /** @var external_participant $external_participant */
        [$external_participant] = $this->setup_external_data();
        $args = ['input' => ['token' => $external_participant->token]];

        $try = function (array $parameters, string $err): void {
            try {
                $result = $this->resolve_graphql_mutation(static::MUTATION, $parameters);
                static::fail(
                    'managed to close when it should have failed: ' . print_r($result, true)
                );
            } catch (moodle_exception $e) {
                static::assertStringContainsString($err, $e->getMessage());
            }
        };

        advanced_feature::disable('performance_activities');
        $try($args, 'Feature performance_activities is not available.');

        advanced_feature::enable('performance_activities');
        $args['input']['token'] = '$2y$10$/unc09hGNue1CEGF25zVMOjknvxlSUhGnVddT/5.zXsWEsI5085cK';
        $try($args, "payload has invalid 'input.token' value");
    }

    /**
     * @return void
     */
    public function test_close_by_external(): void {
        /** @var external_participant $external_participant */
        /** @var participant_instance $pi */
        [$external_participant, $pi] = $this->setup_external_data();
        foreach ($pi->get_participant_sections() as $participant_section) {
            $participant_section->complete(); // closure_conditions verify_required_questions_completed requires this.
        }

        $validator = new external_participant_token_validator($external_participant->token);
        static::assertTrue($validator->is_valid());
        static::assertFalse($pi->is_closed, 'participant instance already closed');

        $args = ['input' => ['token' => $external_participant->token]];

        $result = $this->parsed_graphql_operation(static::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        [
            'success' => $result,
            'error' => $error
        ] = $this->get_webapi_operation_data($result);

        static::assertNotEmpty($result, 'manual closure failed');
        static::assertEmpty($error, "successful remove has error: $error'");
        $pi->refresh();
        static::assertTrue(
            $pi->is_closed,
            'participant instance not closed'
        );

        // Check that manually closing the participant instance sets each section's progress to 'complete'.
        foreach ($pi->participant_sections as $section) {
            self::assertEquals(complete::get_code(), $section->progress);
        }
    }

    /**
     * Generates test data.
     *
     * @param $manual_closure_allowed, indicates whether the returned participant
     *        instance's activity's manual closure setting is enabled.
     *
     * @return array
     */
    private function setup_external_data(
        bool $manual_closure_allowed = true
    ): array {
        static::setAdminUser();

        $generator = generator::instance();

        $configuration = activity_generator_configuration::new()
            ->enable_creation_of_manual_participants()
            ->set_relationships_per_section(
                [
                    constants::RELATIONSHIP_EXTERNAL,
                    constants::RELATIONSHIP_SUBJECT,
                    constants::RELATIONSHIP_MANAGER
                ]
            );

        $activities = $generator->create_full_activities($configuration);
        foreach ($activities as $activity) {
            $activity->settings->update([
                    activity_setting::MANUAL_CLOSE => $manual_closure_allowed
                ]
            );
        }

        /** @var external_participant $external_participant */
        $external_participant = external_participant::repository()->get()->first();

        $participant_instance = participant_instance::load_by_entity(
            $external_participant->participant_instance
        );

        return [$external_participant, $participant_instance];
    }
}
