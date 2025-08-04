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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Riana Rossouw <riana.rossouw@totaralearning.com>
 * @package performelement_linked_review
 */

namespace performelement_linked_review\testing;

use core\collection;
use mod_perform\models\activity\element as element_model;
use mod_perform\models\activity\element_plugin;
use mod_perform\testing\generator as perform_generator;
use mod_perform\entity\activity\participant_section;
use mod_perform\entity\activity\section_element;
use mod_perform\entity\activity\element;
use performelement_linked_review\entity\linked_review_content_response;
use performelement_linked_review\models\linked_review_content;
use totara_competency\testing\generator as competency_generator;

/**
 * @group perform
 */
trait linked_review_test_data_trait {
    /**
     * Create activity with elements
     *
     * @param array $users
     * @param string $subject_user
     * @param array $activity_params
     */
    private function create_activity_with_elements(array $users, string $subject_user, array $activity_params): void {
        $perform_generator = perform_generator::instance();

        $activity = $perform_generator->create_activity_in_container(['activity_name' => $activity_params['name'], 'create_section' => false]);
        $section = $perform_generator->create_section($activity);

        $section_relationships = [];

        $relationships = $activity_params['relationships'];
        $elements = $activity_params['elements'];

        foreach ($relationships as $relationship) {
            $section_relationships[$relationship] = $perform_generator->create_section_relationship(
                $section,
                ['relationship' => $relationship]
            );
        }

        foreach ($elements as $element_params) {
            $element_plugin = $element_params['plugin_type'];
            $element_data = null;

            if ($element_plugin == 'linked_review') {
                $content_type = $element_params['content_type'] ?? 'company_goal';
                $content_type_settings = [];

                switch ($content_type) {
                    case 'personal_goal':
                    case 'company_goal':
                        $content_type_settings = [
                            'enable_status_change' => false,
                        ];
                        break;

                    case 'totara_competency':
                        $content_type_settings = [
                            'enable_rating' => false,
                        ];
                        break;
                }
                $element_data = json_encode([
                    'content_type' => $content_type,
                    'content_type_settings' => $content_type_settings,
                    'selection_relationships' => [$perform_generator->get_core_relationship($element_params['selection_relationship'])->id],
                ]);
            }

            $element = element_model::create($activity->get_context(), $element_plugin, $element_plugin . '_title', '', $element_data);
            $section_element = $perform_generator->create_section_element($section, $element);

            $subject_instance = $perform_generator->create_subject_instance([
                'activity_id' => $activity->id,
                'subject_user_id' => $users[$subject_user]->id,
                'relationships_can_answer' => implode(',', $relationships),
                'include_questions' => false,
            ]);

            foreach ($relationships as $relationship) {
                $participant_section = $perform_generator->create_participant_instance_and_section(
                    $activity,
                    $users[$relationship],
                    $subject_instance->id,
                    $section,
                    $section_relationships[$relationship]->core_relationship->id
                );
            }
        }
    }

    private function create_activity_with_review_elements_and_contents(): array {
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

        $linked_review_element_entity = element::repository()
            ->where('plugin_name', 'linked_review')
            ->one(true);
        $linked_review_element = element_model::load_by_entity($linked_review_element_entity);

        /** @var section_element $section_element */
        $section_element = section_element::repository()
            ->where('section_id', $participant_section->section_id)
            ->where('element_id', $linked_review_element->id)
            ->one(true);

        // Add another child element.
        $perform_generator->create_child_element([
            'parent_element' => $linked_review_element,
            'element_plugin' => 'short_text'
        ]);

        // Create two competencies
        $competency_generator = competency_generator::instance();
        $competency1 = $competency_generator->create_competency();
        $competency_assignment1 = $competency_generator
            ->assignment_generator()
            ->create_user_assignment($competency1->id, $participant_instance->participant_id);
        $competency2 = $competency_generator->create_competency();
        $competency_assignment2 = $competency_generator
            ->assignment_generator()
            ->create_user_assignment($competency2->id, $participant_instance->participant_id);

        // Create two linked review contents.
        linked_review_content::create($competency_assignment1->id, $section_element->id, $participant_instance->id, false);
        linked_review_content::create($competency_assignment2->id, $section_element->id, $participant_instance->id, false);

        $linked_review_content_items = $this->refresh_content_items($section_element->id, $participant_instance->subject_instance->id);
        static::assertCount(2, $linked_review_content_items);
        static::assertEquals(0, linked_review_content_response::repository()->count());

        return [
            $linked_review_element,
            $section_element,
            $participant_section,
        ];
    }

    /**
     * @param collection $linked_review_content_items
     * @param int $participant_instance_id
     * @return collection
     */
    private function create_content_element_responses(
        collection $linked_review_content_items,
        int $participant_instance_id
    ): collection {
        $sub_element_plugin = element_plugin::load_by_plugin('short_text');
        $response = $sub_element_plugin->get_example_response_data();

        $responses = new collection();
        /** @var linked_review_content $linked_review_content_item */
        foreach ($linked_review_content_items as $linked_review_content_item) {
            $element = $linked_review_content_item->section_element->element;
            foreach ($element->children as $child_element) {
                $subject_child_response = new linked_review_content_response();
                $subject_child_response->linked_review_content_id = $linked_review_content_item->id;
                $subject_child_response->child_element_id = $child_element->id;
                $subject_child_response->participant_instance_id = $participant_instance_id;
                $subject_child_response->response_data = $response;
                $subject_child_response->save();

                $responses->append($subject_child_response);
            }
        }
        return $responses;
    }

    /**
     * @param int $section_element_id
     * @param int $subject_instance_id
     * @return collection
     */
    private function refresh_content_items(int $section_element_id, int $subject_instance_id): collection {
        return linked_review_content::get_existing_selected_content(
            $section_element_id,
            $subject_instance_id
        );
    }

    /**
     * @param int $element_id
     * @return element_model
     */
    private function refresh_review_element(int $element_id): element_model {
        // Refresh entity and model so it has all the current data.
        return element_model::load_by_entity(element::repository()->find_or_fail($element_id));
    }
}
