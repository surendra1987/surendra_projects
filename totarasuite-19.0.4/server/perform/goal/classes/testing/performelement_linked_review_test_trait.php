<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

namespace perform_goal\testing;

use core\collection;
use mod_perform\models\activity\element;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\response\section_element_response;
use mod_perform\testing\generator as perform_generator;
use performelement_linked_review\models\linked_review_content;
use totara_core\relationship\relationship;

trait performelement_linked_review_test_trait {

    /**
     * Create a fake section_element_response for testing.
     *
     * @param int $user_id
     * @return section_element_response
     */
    protected function create_perform_goal_linked_review_response(int $user_id): section_element_response {
        $activity = perform_generator::instance()->create_activity_in_container([
            'activity_name' => 'Test activity',
            'activity_status' => 'Active',
            'create_section' => false,
        ]);
        $section = perform_generator::instance()->create_section($activity, ['title' => 'Test section']);

        $subject_relationship = relationship::load_by_idnumber(\mod_perform\constants::RELATIONSHIP_SUBJECT);
        $element_data = [
            'content_type' => 'perform_goal',
            'content_type_settings' => [
                'enable_status_change' => false,
                'status_change_relationship' => null,
            ],
            'selection_relationships' => [$subject_relationship->id],
        ];
        $element_data_encoded = json_encode($element_data, JSON_THROW_ON_ERROR);
        $element = element::create(
            $activity->get_context(),
            'linked_review',
            'Perform goal linked review',
            '',
            $element_data_encoded
        );
        $section_element = perform_generator::instance()->create_section_element($section, $element);
        $child_element = perform_generator::instance()->create_child_element(['parent_element' => $element]);

        $subject_instance_1 = perform_generator::instance()->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_is_participating' => true,
            'subject_user_id' => $user_id,
            'include_questions' => false,
        ]);
        $participant_instance_1 = $subject_instance_1->participant_instances->first();

        // Create the response.
        $section_element_response = new section_element_response(
            participant_instance::load_by_entity($participant_instance_1),
            $section_element,
            null,
            new collection()
        );

        return $section_element_response;
    }

    /**
     * Adds goal information and some child responses to a section_element_response instance.
     *
     * @param section_element_response $section_element_response
     * @param array $goals
     * @return section_element_response
     */
    protected function save_perform_goal_linked_review_content(
        section_element_response $section_element_response,
        array $goals
    ): section_element_response {
        $section_element = $section_element_response->section_element;
        $element = $section_element->element;
        $participant_instance = $section_element_response->participant_instance;
        $child_element = $element->children->first();

        // Create linked element response with goals as content.
        $child_element_config = $element->element_plugin->get_child_element_config();
        $repeating_item_identifier = $child_element_config->repeating_item_identifier;
        $child_element_responses_identifier = $child_element_config->child_element_responses_identifier;
        $content_ids = [];
        // Delete any existing content records.
        \performelement_linked_review\entity\linked_review_content::repository()
            ->where('section_element_id', '=', $section_element->id)
            ->where('selector_id', '=', $participant_instance->participant_id)
            ->where('subject_instance_id', '=', $participant_instance->subject_instance_id)
            ->delete();
        foreach ($goals as $goal) {
            $content_ids[] = linked_review_content::create($goal->id, $section_element->id, $participant_instance->id, false)->id;
        }
        $contents = [];
        foreach ($content_ids as $content_id) {
            $contents[$content_id] = [
                $child_element_responses_identifier => [
                    $child_element->id => [
                        "response_data" => '"content ' . $content_id . ' response"',
                        "child_element_id" => $child_element->id,
                    ]
                ],
                "content_id" => $content_id,
            ];
        }
        $linked_review_response = [
            $repeating_item_identifier => $contents,
        ];

        $response_data = json_encode(['response' => $linked_review_response], JSON_THROW_ON_ERROR);

        // Add data, then save and return the response.
        $section_element_response
            ->set_response_data($response_data)
            ->save();

        return $section_element_response;
    }
}