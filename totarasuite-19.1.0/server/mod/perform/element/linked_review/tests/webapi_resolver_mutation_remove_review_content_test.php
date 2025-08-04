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
 * @author Scott Davies <scott.davies@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section;
use mod_perform\state\participant_instance\in_progress;
use mod_perform\state\participant_instance\not_started;
use mod_perform\state\participant_section\open;
use performelement_linked_review\entity\linked_review_content as linked_review_content_entity;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\testing\generator;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\testing\generator as perform_generator;
use totara_competency\performelement_linked_review\competency_assignment;
use totara_competency\testing\generator as competency_generator;
use totara_core\relationship\relationship;

global $CFG;
require_once($CFG->dirroot . '/perform/goal/tests/perform_goal_perform_status_change_testcase.php');

/**
 * Tests the linked review content removal API.
 *
 * These tests do not verify the API against every single combination of removal
 * conditions or content types. The assumption is that such exhaustive testing
 * will be done by the abstractions that do the actual condition evaluation; all
 * that matters for the tests in this file is whether a linked review content's
 * can_remove() method says yes or no.
 *
 * @group perform
 * @group perform_element
 * @group perform_linked_review
 * @group perform_linked_review_removal
 */
class performelement_linked_review_webapi_resolver_mutation_remove_review_content_test extends testcase {
    private const MUTATION = 'performelement_linked_review_remove_linked_review_content';

    use webapi_phpunit_helper;

    public function test_remove(): void {
        [$linked_review, $subject_pi, ] = $this->setup_env();
        $args = ['input' => ['id' => $linked_review->id]];

        // Subject should be able to remove the linked review content.
        self::setUser($subject_pi->participant_user->to_record());
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        [
            'success' => $result,
            'error' => $error
        ] = $this->get_webapi_operation_data($result);
        self::assertTrue($result, 'remove failed');
        self::assertEmpty($error, "successful remove has error: $error'");

        self::assertFalse(
            linked_review_content_entity::repository()
                ->where('id', $linked_review->id)
                ->exists(),
            'linked review content not removed'
        );
    }

    /**
     * Data provider for test_error_message_for_cannot_remove
     */
    public static function td_error_message_for_cannot_remove(): array {
        return [
            'single section' => [false],
            'multiple section' => [true]
        ];
    }

    /**
     * @dataProvider td_error_message_for_cannot_remove
     */
    public function test_error_message_for_cannot_remove(bool $multisection): void {
        [$linked_review, , $mgr_pi] = $this->setup_env($multisection);
        $args = ['input' => ['id' => $linked_review->id]];

        // Manager cannot remove the linked review content.
        self::setUser($mgr_pi->participant_user->to_record());
        [
            'success' => $result,
            'error' => $error
        ] = $this->resolve_graphql_mutation(self::MUTATION, $args);

        self::assertFalse($result, 'remove passed');

        $text = 'The competency could not be removed because the content does not exist or is unavailable to you. Please refresh your browser.';
        self::assertStringContainsString($text, $error);

        self::assertTrue(
            linked_review_content_entity::repository()
                ->where('id', $linked_review->id)
                ->exists(),
            'linked review content was removed'
        );
    }

    public function test_failed_ajax(): void {
        [$linked_review, $subject_pi, ] = $this->setup_env();
        $args = ['input' => ['id' => $linked_review->id]];

        $try = function (array $parameters, string $err): void {
            try {
                $result = $this->resolve_graphql_mutation(self::MUTATION, $parameters);
                self::fail(
                    'managed to remove when it should have failed: ' . print_r($result, true)
                );
            } catch (moodle_exception $e) {
                self::assertStringContainsString($err, $e->getMessage());
            }
        };

        self::setUser($subject_pi->participant_user->to_record());
        advanced_feature::disable('performance_activities');
        $try($args, 'Feature performance_activities is not available.');

        advanced_feature::enable('performance_activities');

        self::setGuestUser();
        $try($args, 'Must be an authenticated user');

        // This simulates the case where an 'external user' sneakily tries to
        // remove the linked review content.
        self::setUser(null);
        $try($args, 'Course or activity not accessible. (You are not logged in)');

        self::setUser($subject_pi->participant_user->to_record());
        $args['input']['id'] = 101;
        [
            'success' => $result,
            'error' => $error
        ] = $this->resolve_graphql_mutation(self::MUTATION, $args);

        self::assertFalse($result, 'remove passed');
        self::assertStringContainsString(
            'could not be removed because the content does not exist or is unavailable to you. Please refresh your browser',
            $error
        );
    }

    /**
     * @dataProvider td_error_message_for_cannot_remove
     * @return void
     */
    public function test_cannot_remove_when_others_have_progress($multisection): void {
        [$linked_review, $subject_pi , $mgr_pi] = $this->setup_env($multisection);
        // Set an 'other participant' to have progress.
        $mgr_pi->set_attribute('progress', in_progress::get_code())->save()->refresh();

        // Operate
        $args = ['input' => ['id' => $linked_review->id]];
        self::setUser($subject_pi->participant_user->to_record());
        [
            'success' => $result,
            'error' => $error
        ] = $this->resolve_graphql_mutation(self::MUTATION, $args);

        // Assert.
        self::assertFalse($result, 'remove passed');
        $selected_object = ($multisection ? 'section' : 'activity');
        $text = 'could not be removed because this ' . $selected_object . ' has already received responses. Please refresh your browser';
        self::assertStringContainsString($text, $error);
    }

    /**
     * Creates test data.
     *
     * This generates a competency linked review question with these details:
     * - the content selector is the activity subject
     * - the subject's manager is a participant
     * - the linked review question is in its own section
     * - if $multisection value is true, another section with dummy elements is
     *   generated.
     * - All the removal conditions for allowing the content selector (ie the
     *   subject) have been fulfilled.
     *
     * @param bool $multisection indicates if the activity is a multisection
     *        one.
     *
     * @return mixed[] test data tuple comprising these:
     *         - [linked_review_content] generated linked review content
     *         - [participant_instance] participant instance of the subject in
     *           the activity
     *         - [participant_instance] participant instance of the manager in
     *           the activity
     */
    private function setup_env(bool $multisection = false): array {
        self::setAdminUser();

        $generator = generator::instance();

        // By default this creates a competency linked review item that sets the
        // subject as the content selector.
        $data = [
            'content_type' => competency_assignment::get_identifier(),
            'content_type_settings' => [
                'enable_rating' => true,
                'rating_relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_SUBJECT
                )->id
            ],
        ];

        [$activity, $section, , $section_element] = $generator
            ->create_activity_with_section_and_review_element($data);

        // Set a subject as a participant in the activity.
        [, $si, $spi, ] = $generator->create_participant_in_section(
            ['activity' => $activity, 'section' => $section]
        );

        // Set another user as a manager participant in the activity.
        [, , $mpi, ] = $generator->create_participant_in_section(
            [
                'activity' => $activity,
                'section' => $section,
                'subject_instance' => $si,
                'relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_MANAGER
                )
            ]
        );

        $competency_generator = competency_generator::instance();
        $competency = $competency_generator->create_competency();
        $competency_assignment = $competency_generator
            ->assignment_generator()
            ->create_user_assignment($competency->id, $spi->participant_id);

        // Note this test is for removing already selected content; it does not
        // matter who originally "selected" the competency. What matters is the
        // subject is "recorded" as being the selector. Hence the false as the
        // last parameter.
        $content = linked_review_content::create(
            $competency_assignment->id, $section_element->id, $spi->id, false
        );

        $section_id = $section_element->section_id;
        $spi = $this->set_type_independent_conditions(
            $spi, $section_id, in_progress::class
        );

        $this->set_type_independent_conditions(
            $mpi, $section_id, not_started::class
        );

        if ($multisection) {
            $perform_generator = perform_generator::instance();

            $element = $perform_generator->create_element();
            $perform_generator->create_section_element(
                $perform_generator->create_section($activity), $element
            );

            $perform_generator->create_element(
                [
                    'context' => $activity->get_context(),
                    'plugin_name' => 'short_text',
                    'parent' => $element->id
                ]
            );
        }

        return [$content, $spi, $mpi];
    }

    /**
     * Sets up the content type independent conditions so that they do not trip
     * for the tests in this file.
     *
     * See performelement_linked_review\helper\lifecycle\removal_conditions for
     * content type agnostic conditions that have to be fulfilled first.
     *
     * @param participant_instance $pi participant instance to update.
     * @param int $section_id section for which to set the availability.
     * @param string $progress_class new progress to set.
     *
     * @return participant_instance the updated participant instance.
     */
    private function set_type_independent_conditions(
        participant_instance $pi,
        int $section_id,
        string $progress_class
    ): participant_instance {
        $pi->participant_sections
            ->filter(
                fn (participant_section $ps): bool =>
                    (int)$ps->section_id === $section_id
            )
            ->map(
                fn (participant_section $ps): participant_section => $ps
                    ->set_attribute('availability', open::get_code())
                    ->save()
                    ->refresh()
            );

        return $pi
            ->set_attribute('progress', $progress_class::get_code())
            ->save()
            ->refresh();
    }
}
