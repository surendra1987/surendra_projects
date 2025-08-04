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
 * @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
 * @package performelement_linked_review
 */

use core\collection;
use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\entity\activity\element as element_entity;
use mod_perform\entity\activity\participant_section;
use mod_perform\entity\activity\section_element;
use mod_perform\models\activity\element;
use mod_perform\models\activity\element_plugin;
use mod_perform\models\activity\section_element as section_element_model;
use mod_perform\models\response\participant_section as participant_section_model;
use mod_perform\testing\generator as perform_generator;
use performelement_linked_review\entity\linked_review_content_response;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\testing\generator as linked_review_generator;
use performelement_linked_review\testing\linked_review_test_data_trait;
use totara_competency\testing\generator as competency_generator;
use totara_core\relationship\relationship;

/**
 * @group perform
 * @group perform_element
 */
class performelement_linked_review_linked_review_test extends testcase {

    use linked_review_test_data_trait;

    public function test_clean_and_validate() {
        self::setAdminUser();
        [$activity] = linked_review_generator::instance()->create_activity_with_section_and_review_element();

        $element2 = element::create($activity->get_context(), 'linked_review', 'title', '', json_encode([
            'content_type' => 'totara_competency',
            'content_type_settings' => [],
            'selection_relationships' => [relationship::load_by_idnumber(constants::RELATIONSHIP_SUBJECT)->id],
            'components' => 'ishouldnotbehere',
            'selection_relationships_display' => 'me_neither',
            'content_type_display' => 'me_neither2',
            'content_type_settings_display' => 'me_neither3',
        ]));

        $data = json_decode($element2->get_data(), true);

        $this->assertEqualsCanonicalizing(
            [
                'content_type',
                'content_type_display_generic',
                'content_type_settings',
                'selection_relationships',
                'components',
                'selection_relationships_display',
                'compatible_child_element_plugins',
                'content_type_display',
                'content_type_settings_display',
            ],
            array_keys($data)
        );
        $this->assertNotEquals('ishouldnotbehere', $data['components']);
        $this->assertNotEquals('me_neither', $data['selection_relationships_display']);
        $this->assertNotEquals('me_neither2', $data['content_type_display']);
        $this->assertNotEquals('me_neither3', $data['content_type_settings_display']);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('The saved data must contain and only contain these keys:');

        element::create($activity->get_context(), 'linked_review', 'title', '', json_encode([
            'content_type' => 'totara_competency',
            'content_type_settings' => [],
            'selection_relationships' => [relationship::load_by_idnumber(constants::RELATIONSHIP_SUBJECT)->id],
            'ishouldnotbehere' => 'invalidvalue',
        ]));
    }

    public function test_are_child_elements_accessible(): void {
        static::setAdminUser();

        $subject = static::getDataGenerator()->create_user();
        $perform_generator = perform_generator::instance();
        $subject_instance = $perform_generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject->id,
            'include_questions' => true,
            'include_review_element' => true
        ]);

        $participant_instances = $subject_instance->participant_instances;
        static::assertCount(1, $participant_instances);
        $participant_instance = $participant_instances->first();
        $participant_sections = $participant_instance->participant_sections;
        static::assertCount(1, $participant_sections);
        /** @var participant_section $participant_section */
        $participant_section = $participant_sections->first();

        /** @var element_entity $linked_review_element_entity */
        $linked_review_element_entity = element_entity::repository()
            ->where('plugin_name', 'linked_review')
            ->one(true);

        /** @var section_element $section_element */
        $section_element = section_element::repository()
            ->where('section_id', $participant_section->section_id)
            ->where('element_id', $linked_review_element_entity->id)
            ->one(true);

        $linked_review = element_plugin::load_by_plugin('linked_review');

        // We expect false because no content has been selected yet.
        static::assertFalse($linked_review->are_child_elements_accessible(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section)
        ));

        // Select some content.
        $competency_generator = competency_generator::instance();
        $competency = $competency_generator->create_competency();
        $competency_assignment = $competency_generator
            ->assignment_generator()
            ->create_user_assignment($competency->id, $participant_instance->participant_id);

        linked_review_content::create($competency_assignment->id, $section_element->id, $participant_instance->id, false);

        static::assertTrue($linked_review->are_child_elements_accessible(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section)
        ));
    }

    public function test_are_child_elements_fully_answered_called_without_child_element_ids(): void {
        [
            ,
            $section_element,
            $participant_section
        ] = $this->create_activity_with_review_elements_and_contents();

        $linked_review = element_plugin::load_by_plugin('linked_review');
        static::assertTrue($linked_review->are_child_elements_fully_answered(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section),
            []
        ));
    }

    public function test_are_child_elements_fully_answered_without_answers(): void {
        [
            $linked_review_element,
            $section_element,
            $participant_section
        ] = $this->create_activity_with_review_elements_and_contents();

        /** @var element $linked_review_element */
        /** @var collection $child_elements */
        $child_elements = $linked_review_element->get_children();
        static::assertCount(2, $child_elements);

        // There are no responses at all, so should return false.
        $linked_review = element_plugin::load_by_plugin('linked_review');
        static::assertFalse($linked_review->are_child_elements_fully_answered(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section),
            $child_elements->pluck('id')
        ));

        // Also check for a single one.
        static::assertFalse($linked_review->are_child_elements_fully_answered(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section),
            [$child_elements->first()->id]
        ));
    }

    public function test_are_child_elements_fully_answered_partly_answered(): void {
        [
            $linked_review_element,
            $section_element,
            $participant_section
        ] = $this->create_activity_with_review_elements_and_contents();

        $participant_instance = $participant_section->participant_instance;
        $subject_instance = $participant_instance->subject_instance;

        /** @var element $linked_review_element */
        /** @var collection $child_elements */
        $child_elements = $linked_review_element->get_children();
        $linked_review_content_items = $this->refresh_content_items($section_element->id, $subject_instance->id);
        static::assertCount(2, $linked_review_content_items);
        static::assertEquals(0, linked_review_content_response::repository()->count());

        // Create responses for the child elements of ONE content item.
        $this->create_content_element_responses(new collection([$linked_review_content_items->first()]), $participant_instance->id);
        static::assertEquals(2, linked_review_content_response::repository()->count());

        // Responses are missing for second content item, so should return false.
        $linked_review = element_plugin::load_by_plugin('linked_review');
        static::assertFalse($linked_review->are_child_elements_fully_answered(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section),
            $child_elements->pluck('id')
        ));
    }

    public function test_are_child_elements_fully_answered_with_required_empty_response(): void {
        [
            $linked_review_element,
            $section_element,
            $participant_section
        ] = $this->create_activity_with_review_elements_and_contents();

        $participant_instance = $participant_section->participant_instance;
        $subject_instance = $participant_instance->subject_instance;

        /** @var element $linked_review_element */
        /** @var collection $child_elements */
        $child_elements = $linked_review_element->get_children();
        $linked_review_content_items = $this->refresh_content_items($section_element->id, $subject_instance->id);

        // Create responses for the child elements of all content items.
        $responses = $this->create_content_element_responses($linked_review_content_items, $participant_instance->id);
        static::assertEquals(4, linked_review_content_response::repository()->count());

        // Make all the questions required.
        element_entity::repository()->update(['is_required' => 1]);

        // Make response data 'empty' for one response by actually setting to null.
        /** @var linked_review_content_response $response2 */
        $response1 = $responses->first();
        linked_review_content_response::repository()
            ->where('id', $response1->id)
            ->update(['response_data' => null]);

        // Response record is there for all 4 responses, but validation fails for one response for the required element.
        $linked_review = element_plugin::load_by_plugin('linked_review');
        static::assertFalse($linked_review->are_child_elements_fully_answered(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section),
            $child_elements->pluck('id')
        ));
    }

    public function test_are_child_elements_fully_answered_with_non_required_empty_response(): void {
        [
            $linked_review_element,
            $section_element,
            $participant_section
        ] = $this->create_activity_with_review_elements_and_contents();

        $participant_instance = $participant_section->participant_instance;
        $subject_instance = $participant_instance->subject_instance;

        /** @var element $linked_review_element */
        /** @var collection $child_elements */
        $child_elements = $linked_review_element->get_children();
        $linked_review_content_items = $this->refresh_content_items($section_element->id, $subject_instance->id);

        // Create responses for the child elements of all content items.
        $responses = $this->create_content_element_responses($linked_review_content_items, $participant_instance->id);
        static::assertEquals(4, linked_review_content_response::repository()->count());

        // Make response data 'empty' for one response by setting to null.
        /** @var linked_review_content_response $response2 */
        $response1 = $responses->first();
        linked_review_content_response::repository()
            ->where('id', $response1->id)
            ->update(['response_data' => null]);

        // Response record is there for all 4 responses and validation succeeds because the empty response is not required.
        $linked_review = element_plugin::load_by_plugin('linked_review');
        static::assertTrue($linked_review->are_child_elements_fully_answered(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section),
            $child_elements->pluck('id')
        ));
    }

    public function test_are_child_elements_fully_answered_with_all_responses(): void {
        [
            $linked_review_element,
            $section_element,
            $participant_section
        ] = $this->create_activity_with_review_elements_and_contents();

        $participant_instance = $participant_section->participant_instance;
        $subject_instance = $participant_instance->subject_instance;

        /** @var element $linked_review_element */
        /** @var collection $child_elements */
        $child_elements = $linked_review_element->get_children();
        $linked_review_content_items = $this->refresh_content_items($section_element->id, $subject_instance->id);

        // Create responses for the child elements of all content items.
        $this->create_content_element_responses($linked_review_content_items, $participant_instance->id);
        static::assertEquals(4, linked_review_content_response::repository()->count());

        $linked_review = element_plugin::load_by_plugin('linked_review');
        static::assertTrue($linked_review->are_child_elements_fully_answered(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section),
            $child_elements->pluck('id')
        ));

        // Make all the questions required. Should still be true.
        element_entity::repository()->update(['is_required' => 1]);
        static::assertTrue($linked_review->are_child_elements_fully_answered(
            section_element_model::load_by_entity($section_element),
            participant_section_model::load_by_entity($participant_section),
            $child_elements->pluck('id')
        ));

    }
}
