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

use performelement_linked_review\models\linked_review_content as linked_review_content_model;
use performelement_linked_review\testing\generator as linked_review_generator;
use mod_perform\testing\generator as perform_generator;
use mod_perform\constants;
use performelement_linked_review\formatter\content_item as content_item_formatter;
use totara_core\performelement_linked_review\learning;

/**
 * Unit tests for the performelement_linked_review\formatter\content_item GraphQL fields formatter.
 *
 * @group perform
 * @group perform_element
 * @group perform_linked_review
 * @group perform_linked_review_removal
 */
class performelement_linked_review_content_item_formatter_test extends \core_phpunit\testcase {
    public function test_format_for_can_remove_field(): void {
        // Set up.
        self::setAdminUser();

        [$activity, $section, , $section_element] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element([
                'content_type' => learning::get_identifier()
            ]);

        [$user, , $participant_instance, ] = linked_review_generator::instance()
            ->create_participant_in_section([
                'activity' => $activity, 'section' => $section
            ]);

        // Create a course to use for LRI.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'c1', 'summary' => 'summary c1']);
        $model = linked_review_content_model::create(
            $course->id,
            $section_element->id,
            $participant_instance->id,
            false,
            'course'
        );

        perform_generator::instance()->create_section_relationship($section, ['relationship' => constants::RELATIONSHIP_SUBJECT]);

        self::setUser($user);
        $formatter = new content_item_formatter($model, context_system::instance());
        self::assertEquals($model->can_remove, $formatter->format('can_remove'));
    }
}
