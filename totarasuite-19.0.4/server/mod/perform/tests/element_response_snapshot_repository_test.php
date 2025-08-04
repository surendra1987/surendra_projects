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
 * @package mod_perform
 */

use core\collection;

use core_phpunit\testcase;
use mod_perform\entity\activity\element_response_snapshot as element_response_snapshot_entity;
use mod_perform\entity\activity\section as section_entity;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\section_element;
use mod_perform\models\response\participant_section;
use mod_perform\models\response\section_element_response;
use mod_perform\state\participant_section\closed;
use mod_perform\testing\generator as perform_generator;

/**
 * @group perform
 */
class mod_perform_element_response_snapshot_repository_test extends testcase {

    private $snapshot_count = 0;

    protected function tearDown(): void {
        $this->snapshot_count = 0;
        parent::tearDown();
    }

    protected function create_activity_with_response_with_snapshot(): section_element_response {
        $subject = self::getDataGenerator()->create_user();
        /** @var perform_generator $generator */
        $generator = perform_generator::instance();
        $activity = $generator->create_activity_in_container();

        $subject_instance = $generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_is_participating' => true,
            'subject_user_id' => $subject->id,
            'include_questions' => true,
        ]);
        $participant_instance = $subject_instance->participant_instances->first();

        /** @var section_entity $section*/
        $section = $subject_instance->activity()->sections->first();
        $section_element = $section->section_elements->first();

        $section_element_response = new section_element_response(
            participant_instance::load_by_entity($participant_instance),
            section_element::load_by_entity($section_element),
            null,
            new collection()
        );
        $section_element_response->set_response_data('"random response"')->save();

        $this->create_snapshot($section_element_response);

        return $section_element_response;
    }

    protected function create_snapshot(section_element_response $section_element_response): element_response_snapshot_entity {
        $snapshot_entity = new element_response_snapshot_entity();
        $snapshot_entity->response_id = $section_element_response->id;
        $snapshot_entity->item_type = 'test_item';
        $snapshot_entity->item_id = $this->snapshot_count++;
        return $snapshot_entity->save();
    }

    public function test_part_of_open_response_open() {
        self::setAdminUser();

        $section_element_response = $this->create_activity_with_response_with_snapshot();

        // Test the repository without filtering.
        $this->assertCount(1, element_response_snapshot_entity::repository()->get());

        // Test the repository with filtering.
        $this->assertCount(1, element_response_snapshot_entity::repository()->part_of_open_response()->get());
    }

    public function test_part_of_open_response_closed() {
        self::setAdminUser();

        $section_element_response = $this->create_activity_with_response_with_snapshot();
        /** @var participant_section $participant_section */
        $participant_section = $section_element_response->get_participant_instance()->participant_sections->first();
        $participant_section->switch_state(closed::class);


        // Test the repository without filtering.
        $this->assertCount(1, element_response_snapshot_entity::repository()->get());

        // Test the repository with filtering.
        $this->assertCount(0, element_response_snapshot_entity::repository()->part_of_open_response()->get());
    }

    public function test_part_of_open_response_instance_closed() {
        self::setAdminUser();

        $section_element_response = $this->create_activity_with_response_with_snapshot();
        /** @var participant_section $participant_section */
        $participant_instance = $section_element_response->get_participant_instance();
        $participant_instance->switch_state(\mod_perform\state\participant_instance\closed::class);


        // Test the repository without filtering.
        $this->assertCount(1, element_response_snapshot_entity::repository()->get());

        // Test the repository with filtering.
        $this->assertCount(0, element_response_snapshot_entity::repository()->part_of_open_response()->get());
    }

    public function test_part_of_open_response_open_and_closed() {
        self::setAdminUser();

        $section_element_response1 = $this->create_activity_with_response_with_snapshot();
        $section_element_response2 = $this->create_activity_with_response_with_snapshot();
        $section_element_response3 = $this->create_activity_with_response_with_snapshot();
        $section_element_response4 = $this->create_activity_with_response_with_snapshot();

        // Set 2 and 3 to closed.
        $participant_section2 = $section_element_response2->get_participant_instance()->participant_sections->first();
        $participant_section2->switch_state(closed::class);
        $participant_instance3 = $section_element_response3->get_participant_instance();
        $participant_instance3->switch_state(\mod_perform\state\participant_instance\closed::class);

        // Test the repository without filtering.
        $this->assertCount(4, element_response_snapshot_entity::repository()->get());

        // Test the repository with filtering.
        $this->assertCount(2, element_response_snapshot_entity::repository()->part_of_open_response()->get());
    }
}
