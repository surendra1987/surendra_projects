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
 * @package mod_perform
 */

namespace mod_perform\testing;

use core\collection;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use mod_perform\entity\activity\participant_section;
use mod_perform\entity\activity\section as section_entity;
use mod_perform\entity\activity\subject_instance as subject_instance_entity;
use mod_perform\expand_task;
use mod_perform\models\activity\element;
use mod_perform\models\activity\helpers\section_element_manager;
use mod_perform\models\activity\section;
use mod_perform\models\activity\track;
use mod_perform\models\activity\track_assignment_type;
use mod_perform\models\response\section_element_response;
use mod_perform\state\activity\draft;
use mod_perform\task\service\subject_instance_creation;
use mod_perform\testing\generator as perform_generator;
use mod_perform\user_groups\grouping;
use totara_job\job_assignment;
use mod_perform\models\response\participant_section as participant_section_model;
use mod_perform\models\activity\participant_instance;

trait multisection_activity_trait {

    /**
     * @return array
     */
    public function create_multi_section_activity(): array {
        // Create a multi-section activity.
        static::setAdminUser();
        $generator = static::getDataGenerator();
        $perform_generator = perform_generator::instance();

        // Create subject & manager users and add them to an audience.
        $subject_user = $generator->create_user(['firstname' => 'Subject', 'lastname' => 'User']);
        $manager_user = $generator->create_user(['firstname' => 'Manager', 'lastname' => 'User']);
        job_assignment::create([
            'userid' => $subject_user->id,
            'idnumber' => 'subject_job_assignment',
            'managerjaid' => job_assignment::create_default($manager_user->id)->id,
        ]);
        $cohort = $generator->create_cohort();
        cohort_add_member($cohort->id, $subject_user->id);
        cohort_add_member($cohort->id, $manager_user->id);

        // Create an activity with three sections.
        $activity = $perform_generator->create_activity_in_container([
            'create_track' => true,
            'create_section' => false,
            'activity_name' => 'Test activity',
            'activity_status' => draft::get_code()
        ]);
        $section1 = section::create($activity, 'Section 1');
        $section2 = section::create($activity, 'Section 2');
        $section3 = section::create($activity, 'Section 3');
        foreach ([$section1, $section2, $section3] as $section) {
            $perform_generator->create_section_relationship($section, ['relationship' => constants::RELATIONSHIP_SUBJECT]);
            $perform_generator->create_section_relationship($section, ['relationship' => constants::RELATIONSHIP_MANAGER]);

            // Create a question element in each section.
            $element = element::create(
                $activity->get_context(),
                'numeric_rating_scale',
                'Numeric title 1',
                'identifier_numeric',
                '{"defaultValue": "3", "highValue": "5", "lowValue": "1"}',
                false
            );

            /** @var section_entity $section_entity */
            $section_entity = section_entity::repository()->find($section->get_id());
            $section_element_manager = new section_element_manager($section_entity);

            $section_element_manager->add_element_after($element);
        }
        /** @var track $track */
        $track = track::load_by_activity($activity)->first();
        $track->add_assignment(track_assignment_type::ADMIN, grouping::cohort($cohort->id));

        $activity->activate();
        expand_task::create()->expand_all();
        (new subject_instance_creation())->generate_instances();

        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = subject_instance_entity::repository()
            ->where('subject_user_id', $subject_user->id)
            ->one(true);

        /** @var participant_instance_entity $participant_instance */
        $participant_instance = $subject_instance_entity->participant_instances->find(
            fn (participant_instance_entity $participant_instance) =>
                (int)$participant_instance->participant_id === (int)$subject_user->id
        );
        $participant_instance_model = participant_instance::load_by_entity($participant_instance);

        $participant_sections = participant_section::repository()
            ->where('participant_instance_id', $participant_instance->id)
            ->get()
            ->map_to(participant_section_model::class)
            ->all();

        static::assertCount(3, $participant_sections);

        return [
            $activity,
            $participant_instance_model,
            ...$participant_sections
        ];
    }

    /**
     * @param participant_section_model $participant_section
     * @return void
     */
    private function complete_section(participant_section_model $participant_section): void {
        $this->mark_answers_complete($participant_section);
        $participant_section->complete();
    }

    /**
     * @param participant_section_model $participant_section
     * @return void
     */
    private function mark_answers_complete(participant_section_model $participant_section): void {
        $section_element_count = $participant_section->get_section()->get_section_elements()->count();

        $responses = new collection();
        for ($i = 0; $i < $section_element_count; $i++) {
            $responses->append($this->create_valid_element_response());
        }

        $participant_section->set_section_element_responses($responses);
    }

    /**
     * @return section_element_response
     */
    private function create_valid_element_response(): section_element_response {
        return new class extends section_element_response {

            // Override constructor.
            public function __construct() {
            }

            public function save(): section_element_response {
                return $this;
            }

            public function validate_response($is_draft_validation = false): bool {
                $this->validation_errors = new collection();
                return true;
            }
        };
    }
}
