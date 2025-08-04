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
 * @package performelement_perform_goal_creation
 */

namespace performelement_perform_goal_creation\testing;

use core\collection;
use mod_perform\models\activity\element;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\response\section_element_response;
use mod_perform\testing\generator as perform_generator;

/**
 * A testcase for use in testing performelement_perform_goal_creation element logic.
 */
trait perform_goal_creation_test_trait {

    /**
     * Create a fake section_element_response for testing.
     *
     * @param int $user_id
     * @return section_element_response
     */
    protected function create_perform_goal_creation_response(int $user_id): section_element_response {
        $activity = perform_generator::instance()->create_activity_in_container([
            'activity_name' => 'Test activity',
            'activity_status' => 'Active',
            'create_section' => false,
        ]);
        $section = perform_generator::instance()->create_section($activity, ['title' => 'Test section']);
        $element = element::create(
            $activity->get_context(),
            'perform_goal_creation',
            'Personal goal creation'
        );
        $section_element = perform_generator::instance()->create_section_element($section, $element);

        $subject_instance_1 = perform_generator::instance()->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_is_participating' => true,
            'subject_user_id' => $user_id,
            'include_questions' => false,
        ]);
        $participant_instance_1 = $subject_instance_1->participant_instances->first();
        $participant_section = perform_generator::instance()->create_participant_section(
            $activity,
            $participant_instance_1,
            false,
            $section
        );
        $section_element_response = new section_element_response(
            participant_instance::load_by_entity($participant_instance_1),
            $section_element,
            null,
            new collection()
        );
        $section_element_response->save();
        return $section_element_response;
    }
}