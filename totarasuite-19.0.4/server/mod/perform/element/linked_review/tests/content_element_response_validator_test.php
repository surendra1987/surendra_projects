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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package performelement_linked_review
 */

use core_phpunit\testcase;
use mod_perform\entity\activity\element;
use performelement_linked_review\entity\linked_review_content_response;
use performelement_linked_review\helper\content_element_response_validator;
use performelement_linked_review\testing\linked_review_test_data_trait;

/**
 * @group perform
 * @group perform_element
 */
class performelement_linked_review_content_element_response_validator_test extends testcase {

    use linked_review_test_data_trait;

    public function test_validate_existing_responses(): void {
        [
            $linked_review_element,
            $section_element,
            $participant_section,
        ] = $this->create_activity_with_review_elements_and_contents();

        $participant_instance = $participant_section->participant_instance;
        $subject_instance = $participant_instance->subject_instance;

        $linked_review_content_items = $this->refresh_content_items($section_element->id, $subject_instance->id);
        static::assertCount(2, $linked_review_content_items);
        static::assertEquals(0, linked_review_content_response::repository()->count());

        $validator = new content_element_response_validator();

        // No responses exist, so there should be no validation errors.
        $validation_errors = $validator->validate_existing_responses(
            $this->refresh_content_items($section_element->id, $subject_instance->id),
            $this->refresh_review_element($linked_review_element->id)
        );
        static::assertCount(0, $validation_errors);

        // Create responses for the child elements.
        $responses = $this->create_content_element_responses($linked_review_content_items, $participant_instance->id);
        // We now have responses for two content items with two sub-questions each.
        static::assertEquals(4, linked_review_content_response::repository()->count());

        // Make all the questions required.
        element::repository()->update(['is_required' => 1]);

        // Still no validation errors because the responses are valid.
        $validation_errors = $validator->validate_existing_responses(
            $this->refresh_content_items($section_element->id, $subject_instance->id),
            $this->refresh_review_element($linked_review_element->id)
        );
        static::assertCount(0, $validation_errors);

        // Make response data empty for one response by adding json-encoded null.
        /** @var linked_review_content_response $response1 */
        $response1 = $responses->first();
        linked_review_content_response::repository()
            ->where('id', $response1->id )
            ->update(['response_data' => json_encode(null)]);

        $validation_errors = $validator->validate_existing_responses(
            $this->refresh_content_items($section_element->id, $subject_instance->id),
            $this->refresh_review_element($linked_review_element->id)
        );
        static::assertCount(1, $validation_errors);

        // Make response data 'empty' for another response by actually setting to null.
        /** @var linked_review_content_response $response2 */
        $response2 = $responses->last();
        linked_review_content_response::repository()
            ->where('id', $response2->id )
            ->update(['response_data' => null]);

        $validation_errors = $validator->validate_existing_responses(
            $this->refresh_content_items($section_element->id, $subject_instance->id),
            $this->refresh_review_element($linked_review_element->id)
        );
        static::assertCount(2, $validation_errors);

        // Make responses with empty response data optional.
        element::repository()
            ->where_in('id', [$response1->child_element_id, $response2->child_element_id])
            ->update(['is_required' => 0]);

        $validation_errors = $validator->validate_existing_responses(
            $this->refresh_content_items($section_element->id, $subject_instance->id),
            $this->refresh_review_element($linked_review_element->id)
        );
        static::assertCount(0, $validation_errors);
    }
}