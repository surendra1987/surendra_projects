<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Marco Song <marco.song@totaralearning.com>
 * @author Mark Metcalfe <mark.metcalfe@totaralearning.com>
 * @package performelement_linked_review
 */

use core\collection;
use mod_perform\constants;
use mod_perform\models\activity\element;
use mod_perform\models\activity\section_element;
use performelement_linked_review\entity\linked_review_content as linked_review_content_entity;
use performelement_linked_review\models\linked_review_content as linked_review_content_model;
use performelement_linked_review\testing\generator as linked_review_generator;
use totara_core\advanced_feature;
use totara_core\feature_not_available_exception;
use totara_core\relationship\relationship;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section;
use mod_perform\state\participant_section\open;
use mod_perform\state\participant_section\complete as section_complete;
use totara_competency\performelement_linked_review\competency_assignment;
use totara_competency\testing\generator as competency_generator;
use mod_perform\testing\generator as perform_generator;
use mod_perform\state\participant_instance\complete;
use mod_perform\state\participant_instance\in_progress;
use mod_perform\state\participant_instance\not_started;
use mod_perform\state\participant_section\closed;
use mod_perform\state\participant_section\in_progress as section_in_progress;

/**
 * @group perform
 * @group perform_element
 * @group perform_linked_review
 * @group perform_linked_review_creation
 */
class performelement_linked_review_webapi_resolver_mutation_update_review_content_test extends \core_phpunit\testcase {

    private const MUTATION = 'performelement_linked_review_update_linked_review_content';

    use webapi_phpunit_helper;

    public function test_resolve_mutation_successful(): void {
        self::setAdminUser();

        [$activity, $section, $element, $section_element] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user1, $subject_instance, $participant_instance1] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity, 'section' => $section
        ]);
        [$user2, $subject_instance, $participant_instance2] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity, 'section' => $section, 'subject_instance' => $subject_instance,
            'relationship' => constants::RELATIONSHIP_MANAGER,
        ]);
        $content_id1 = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;
        $content_id2 = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;
        $content_id3 = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;
        $content = [
            ['id' => $content_id1],
            ['id' => $content_id2],
            ['id' => $content_id3]
        ];

        $content_type = json_decode($element->data, true)['content_type'] ?? null;
        $this->assertNotEmpty(trim($content_type));
        self::setUser($user1);

        $args = [
            'input' => [
                'content' => json_encode($content),
                'section_element_id' => $section_element->id,
                'participant_instance_id' => $participant_instance1->id,
            ],
        ];

        $this->assertEquals(0, linked_review_content_entity::repository()->count());

        $this->resolve_graphql_mutation(self::MUTATION, $args);

        /** @var linked_review_content_entity[]|collection $linked_content */
        $linked_content = linked_review_content_entity::repository()->get();
        $this->assertEquals(3, $linked_content->count());
        $this->assertEquals(count($content), $linked_content->count());
        foreach ($linked_content as $actual_content) {
            $this->assertEquals($section_element->id, $actual_content->section_element_id);
            $this->assertEquals($participant_instance1->subject_instance_id, $actual_content->subject_instance_id);
            $this->assertContainsEquals($actual_content->content_id, array_column($content, 'id'));
            $this->assertEquals($content_type, $actual_content->content_type);
            $this->assertGreaterThan(0, $actual_content->created_at);
        }
    }

    public function test_element_is_not_a_linked_review_element(): void {
        self::setAdminUser();
        [$activity, $section, $element, $review_section_element] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user, $subject_instance, $participant_instance] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity, 'section' => $section,
        ]);
        $other_user = self::getDataGenerator()->create_user();
        $short_text_element = element::create($activity->get_context(), 'short_text', 'A');
        $short_text_section_element = section_element::create($section, $short_text_element, 4);
        self::setUser($other_user);

        $args = [
            'input' => [
                'content' => json_encode([]),
                'section_element_id' => $short_text_section_element->id,
                'participant_instance_id' => $participant_instance->id,
            ],
        ];

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        $this->assertFalse($result['validation_info']['can_update']);
        $this->assertEquals($result['validation_info']['description'],
            get_string('can_not_add_nothing', 'performelement_linked_review')
        );
    }

    public function test_element_is_not_in_participant_section(): void {
        self::setAdminUser();

        // Activity1
        [$activity, $section] = linked_review_generator::instance()->create_activity_with_section_and_review_element();

        // Activity2
        [,, $element, $section_element] = linked_review_generator::instance()->create_activity_with_section_and_review_element();

        // Subject is a participant in activity1
        [$user, $subject_instance, $participant_instance] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity, 'section' => $section
        ]);

        $content_id1 = linked_review_generator::instance()->create_competency_assignment(['user' => $user])->id;
        $other_user = self::getDataGenerator()->create_user($content_id1);
        self::setUser($other_user);

        $args = [
            'input' => [
                'content' => json_encode([
                    ['id' => $content_id1]
                ]),
                // This is from activity2
                'section_element_id' => $section_element->id,

                // But this is for the subject in activity1
                'participant_instance_id' => $participant_instance->id,
            ],
        ];

        // Add fails because the subject in activity1 is not participating in
        // activity2.
        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        $this->assertFalse($result['validation_info']['can_update']);
        $this->assertEquals($result['validation_info']['description'],
            get_string(
                'create_condition_failed:user_is_not_selector',
                'performelement_linked_review'
            )
        );
    }

    public function test_user_is_not_a_participant(): void {
        self::setAdminUser();
        [$activity, $section, $element, $section_element] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user, $subject_instance, $participant_instance] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity, 'section' => $section,
        ]);

        $content_id1 = linked_review_generator::instance()->create_competency_assignment(['user' => $user])->id;
        $other_user = self::getDataGenerator()->create_user();
        self::setUser($other_user);

        $args = [
            'input' => [
                'content' => json_encode([
                    ['id' => $content_id1]
                ]),
                'section_element_id' => $section_element->id,
                'participant_instance_id' => $participant_instance->id,
            ],
        ];

        // Add fails because other_user is not participating in the activity.
        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        $this->assertFalse($result['validation_info']['can_update']);
        $this->assertEquals($result['validation_info']['description'],
            get_string(
                'create_condition_failed:user_is_not_selector',
                'performelement_linked_review'
            )
        );
    }

    public function test_invalid_content_ids_specified(): void {
        self::setAdminUser();
        [$activity, $section, $element, $section_element] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user, $subject_instance, $participant_instance] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity, 'section' => $section,
        ]);
        self::setUser($user);

        $args = [
            'input' => [
                'content' => json_encode([
                    ['id' => -1],
                    ['id' => -2],
                    ['id' => -3],
                    ['id' => -4]
                ]),
                'section_element_id' => $section_element->id,
                'participant_instance_id' => $participant_instance->id,
            ],
        ];

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Not all the specified content IDs actually exist');
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_additional_content_can_be_added_successful(): void {
        self::setAdminUser();

        [$activity, $section, $element, $section_element] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user1, $subject_instance, $participant_instance1] = linked_review_generator::instance()->create_participant_in_section(
            [
                'activity' => $activity,
                'section' => $section,
            ]
        );

        // Believe it or not, this creates _another_ user as the subject of the
        // activity. So now the same activity has 2 subjects!
        [$user2, $subject_instance2, $participant_instance2] = linked_review_generator::instance()->create_participant_in_section(
            [
                'activity' => $activity,
                'section' => $section,
                'subject_instance' => $subject_instance,
            ]
        );
        $first_content_id = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;
        $last_content_id = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;

        // User1 selects contents
        self::setUser($user1);

        $args = [
            'input' => [
                'content' => json_encode([['id' => $first_content_id]]),
                'section_element_id' => $section_element->id,
                'participant_instance_id' => $participant_instance1->id,
            ],
        ];

        $this->assertEquals(0, linked_review_content_entity::repository()->count());

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        /** @var linked_review_content_entity[]|collection $linked_content */
        $linked_content = linked_review_content_entity::repository()->get();
        foreach ($linked_content as $content) {
            $this->assertEquals($section_element->id, $content->section_element_id);
            $this->assertEquals($participant_instance1->subject_instance_id, $content->subject_instance_id);
            $this->assertEquals($content->content_id, $first_content_id);
        }

        // User2 selects contents for the same section element and same subject instance
        self::setUser($user2);
        $args = [
            'input' => [
                'content' => json_encode([['id' => $last_content_id]]),
                'section_element_id' => $section_element->id,
                'participant_instance_id' => $participant_instance2->id,
            ],
        ];

        // This passes because user2 is also a subject in the same activity as
        // user1!
        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        $this->assertTrue($result['validation_info']['can_update']);
        $this->assertEquals('', $result['validation_info']['description']);

        foreach ($linked_content as $content) {
            $this->assertEquals($section_element->id, $content->section_element_id);
            $this->assertEquals($participant_instance1->subject_instance_id, $content->subject_instance_id);
            $this->assertEquals($content->content_id, $first_content_id);
        }
    }

    public function test_feature_disabled(): void {
        advanced_feature::disable('performance_activities');
        self::setAdminUser();

        $this->expectException(feature_not_available_exception::class);
        $this->expectExceptionMessage('Feature performance_activities is not available.');

        $this->resolve_graphql_mutation(self::MUTATION, []);
    }

    public function test_require_login(): void {
        $this->expectException(require_login_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION, []);
    }

    // Start of tests for changes in TL-40684, i.e. Backend changes to support adding sets of linked review items multiple times.
    public function test_additional_content_added_fails_when_section_closed(): void {
        [$linked_review, $subject_pi, $manager_pi, $section_element] = $this->setup_env();
        $subject_user = $subject_pi->participant_user->to_record();
        self::setUser($subject_user);

        $content_id2 = linked_review_generator::instance()->create_competency_assignment(['user' => $subject_user->id])->id;

        $this->set_participant_section_progress(
            $subject_pi,
            (int)$section_element->section_id,
            section_complete::get_code(),
            closed::get_code(),
            complete::get_code()
        );

        $args = [
            'input' => [
                'content' => json_encode([['id' => $content_id2]]),
                'section_element_id' => $section_element->id,
                'participant_instance_id' => $subject_pi->id,
            ],
        ];
        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        $this->assertFalse($result['validation_info']['can_update']);
        $this->assertEquals(
            get_string(
                'create_condition_failed:section_not_editable',
                'performelement_linked_review'
            ),
            $result['validation_info']['description']
        );
    }

    public function test_additional_content_added_fails_when_attempting_duplicate(): void {
        [$linked_review, $subject_pi, $manager_pi, $section_element] = $this->setup_env();
        $subject_user = $subject_pi->participant_user->to_record();
        self::setUser($subject_user);

        $args = [
            'input' => [
                'content' => json_encode([['id' => $linked_review->content_id]]), // duplicate lr content_id in same section_element
                'section_element_id' => $section_element->id,
                'participant_instance_id' => $subject_pi->id,
            ],
        ];
        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        $this->assertFalse($result['validation_info']['can_update']);
        $this->assertEquals(get_string('can_not_readd_review_item_to_section', 'performelement_linked_review'), $result['validation_info']['description']);
    }

    public function test_additional_content_added_fails_when_participant_progress(): void {
        [$linked_review, $subject_pi, $manager_pi, $section_element] = $this->setup_env();

        $this->set_participant_section_progress(
            $manager_pi,
            (int)$section_element->section_id,
            section_in_progress::get_code(),
            open::get_code(),
            in_progress::get_code()
        );

        $subject_user = $subject_pi->participant_user->to_record();
        self::setUser($subject_user);
        $content_id2 = linked_review_generator::instance()->create_competency_assignment(['user' => $subject_user->id])->id;

        $args = [
            'input' => [
                'content' => json_encode([['id' => $content_id2]]),
                'section_element_id' => $section_element->id,
                'participant_instance_id' => $subject_pi->id,
            ],
        ];
        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);

        $this->assertFalse($result['validation_info']['can_update']);
        $this->assertEquals(
            get_string(
                'create_condition_failed:others_already_progressed',
                'performelement_linked_review'
            ),
            $result['validation_info']['description']
        );
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

        $generator = linked_review_generator::instance();

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
        $content = linked_review_content_model::create(
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

        return [$content, $spi, $mpi, $section_element];
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

    /**
     * @param participant_instance $pi
     * @param int $section_id
     * @param int $section_progress_code
     * @param int $section_availability_code
     * @param int $participant_progress_code
     * @return void
     */
    private function set_participant_section_progress(
        participant_instance $pi,
        int $section_id,
        int $section_progress_code,
        int $section_availability_code,
        int $participant_progress_code
    ): void {
        foreach ($pi->participant_sections as $pps) {
            if ((int)$pps->section_id == $section_id) {
                $pps->set_attribute('progress', $section_progress_code)
                    ->save()
                    ->refresh();

                $pps->set_attribute('availability', $section_availability_code)
                    ->save()
                    ->refresh();
            }
        }

        $pi->set_attribute('progress', $participant_progress_code)->save();
    }
}
