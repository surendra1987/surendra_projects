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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use mod_perform\models\activity\element;
use mod_perform\testing\generator as perform_generator;
use mod_perform\constants;
use totara_job\job_assignment;
use performelement_linked_review\models\linked_review_content;
use perform_goal\settings_helper;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\model\target_type\date as target_type_date;

require_once(__DIR__.'/external_api_phpunit_helper.php');
require_once(__DIR__.'/perform_goal_create_goal_testcase.php');

/**
 * Common methods for perform_status_change web API resolver tests.
 */
abstract class perform_goal_perform_status_change_testcase extends testcase {
    /**
     * Helper method to generate some test goals.
     * @param int $number_of_goals
     * @return array
     */
    final protected function create_test_goals(int $number_of_goals, bool $use_same_goal_names = false): array {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.
        $goal_subject_user1 = self::getDataGenerator()->create_user();
        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $goal_owner_user = self::getDataGenerator()->create_user();
        role_assign($role_staff_manager->id, $goal_owner_user->id, context_system::instance()->id);
        self::setUser($goal_owner_user);
        // Create some test goals.
        $test_goals = [];
        $data_config = ['user_id' => $goal_subject_user1->id, 'target_type' => target_type_date::get_type()];
        if ($use_same_goal_names) {
            $data_config['name'] = 'test goal_' . time() . '_' . rand();
        }
        for ($i = 0; $i < $number_of_goals; $i++) {
            $data = goal_generator_config::new($data_config);
            $test_goals[] = goal_generator::instance()->create_goal($data);
        }

        return $test_goals;
    }

    /**
     * Helper method to generate test data.
     * @param string $status_change_relationship
     * @return stdClass
     */
    final protected function create_activity_data(string $status_change_relationship = 'manager'): stdClass {
        self::setAdminUser();
        $another_user = self::getDataGenerator()->create_user(['firstname' => 'Another', 'lastname' => 'User']);
        $manager_user = self::getDataGenerator()->create_user(['firstname' => 'Manager', 'lastname' => 'User']);
        $manager_user->fullname = $manager_user->firstname . ' ' . $manager_user->lastname;
        $subject_user = self::getDataGenerator()->create_user(['firstname' => 'Subject', 'lastname' => 'User']);

        /** @var job_assignment $manager_ja */
        $manager_ja = job_assignment::create([
            'userid' => $manager_user->id,
            'idnumber' => 'ja02',
        ]);

        job_assignment::create([
            'userid' => $subject_user->id,
            'idnumber' => 'ja01',
            'managerjaid' => $manager_ja->id
        ]);

        [$goal1, $goal2] = $this->create_test_goals(2);

        $perform_generator = perform_generator::instance();
        $activity = $perform_generator->create_activity_in_container(['activity_name' => 'Test activity']);
        $section = $perform_generator->create_section($activity);
        $manager_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => constants::RELATIONSHIP_MANAGER]
        );
        $subject_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => constants::RELATIONSHIP_SUBJECT]
        );
        $appraiser_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => constants::RELATIONSHIP_APPRAISER]
        );
        $element = element::create($activity->get_context(), 'linked_review', 'title', '', json_encode([
            'content_type' => 'personal_goal',
            'content_type_settings' => [
                'enable_status_change' => true,
                'status_change_relationship' => $perform_generator->get_core_relationship($status_change_relationship)->id
            ],
            'selection_relationships' => [$subject_section_relationship->core_relationship_id],
        ]));

        $section_element = $perform_generator->create_section_element($section, $element);

        $subject_instance1 = $perform_generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_user_id' => $subject_user->id
        ]);

        $subject_instance2 = $perform_generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_user_id' => $subject_user->id
        ]);

        $manager_participant_section1 = $perform_generator->create_participant_instance_and_section(
            $activity,
            $manager_user,
            $subject_instance1->id,
            $section,
            $manager_section_relationship->core_relationship->id
        );

        $subject_participant_section1 = $perform_generator->create_participant_instance_and_section(
            $activity,
            $subject_user,
            $subject_instance1->id,
            $section,
            $subject_section_relationship->core_relationship->id
        );
        $perform_generator->create_participant_instance_and_section(
            $activity,
            $subject_user,
            $subject_instance1->id,
            $section,
            $appraiser_section_relationship->core_relationship->id
        );
        $subject_participant_section2 = $perform_generator->create_participant_instance_and_section(
            $activity,
            $subject_user,
            $subject_instance2->id,
            $section,
            $subject_section_relationship->core_relationship->id
        );

        $linked_assignment1 = linked_review_content::create(
            $goal1->id, $section_element->id, $subject_participant_section1->participant_instance_id, false
        );
        $linked_assignment2 = linked_review_content::create(
            $goal2->id, $section_element->id, $subject_participant_section1->participant_instance_id, false
        );

        $data = new stdClass();
        $data->another_user = $another_user;
        $data->manager_user = $manager_user;
        $data->subject_user = $subject_user;
        $data->activity = $activity;
        $data->subject_instance1 = $subject_instance1;
        $data->subject_participant_instance1 = $subject_participant_section1->participant_instance;
        $data->manager_participant_instance1 = $manager_participant_section1->participant_instance;
        $data->manager_participant_section1 = $manager_participant_section1;
        $data->section_element = $section_element;
        $data->section = $section;
        $data->linked_assignment1 = $linked_assignment1;
        $data->linked_assignment2 = $linked_assignment2;
        $data->goal1 = $goal1;
        $data->goal2 = $goal2;
        $data->goal1_assignment = $goal1;
        $data->goal2_assignment = $goal2;
        $data->status = 'not_started';
        $data->current_value = 25.0;

        return $data;
    }
}
