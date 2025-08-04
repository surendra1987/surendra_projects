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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

use core\collection;
use core\date_format;
use core\orm\query\builder;
use core\webapi\formatter\field\date_field_formatter;
use core_phpunit\testcase;
use mod_perform\constants as mod_perform_constants;
use mod_perform\entity\activity\element as element_entity;
use mod_perform\entity\activity\element_response;
use mod_perform\entity\activity\element_response_snapshot;
use mod_perform\entity\activity\participant_section;
use mod_perform\entity\activity\section_relationship;
use mod_perform\entity\activity\subject_instance as subject_instance_entity;
use mod_perform\models\activity\element;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\subject_instance;
use mod_perform\testing\generator as perform_generator;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\perform_status_change as perform_status_change_entity;
use perform_goal\model\goal as goal_model;
use perform_goal\model\perform_status_change;
use perform_goal\model\status\status_helper;
use perform_goal\performelement_linked_review\goal_snapshot;
use perform_goal\performelement_linked_review\perform_goal_content_type;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use performelement_linked_review\models\linked_review_content as linked_review_content_model;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\testing\generator as linked_review_generator;
use totara_core\advanced_feature;
use totara_core\relationship\relationship;
use totara_job\job_assignment;

/**
 * Unit tests for the perform_goal_content_type class.
 */
class perform_goal_perform_goal_content_type_test extends testcase {
    public function test_get_display_settings(): void {
        $display_settings = perform_goal_content_type::get_display_settings([]);
        $subject_relationship = relationship::load_by_idnumber('subject');

        self::assertEquals(
            ['Allow updating progress in activity' => 'No'],
            $display_settings
        );

        $display_settings = perform_goal_content_type::get_display_settings([
            'enable_status_change' => false
        ]);

        self::assertEquals(
            ['Allow updating progress in activity' => 'No'],
            $display_settings
        );

        $display_settings = perform_goal_content_type::get_display_settings([
            'enable_status_change' => true
        ]);

        self::assertEquals(
            ['Allow updating progress in activity' => 'Yes'],
            $display_settings
        );

        $display_settings = perform_goal_content_type::get_display_settings([
            'enable_status_change' => true,
            'status_change_relationship' => $subject_relationship->id,
        ]);

        self::assertEquals(
            [
                'Allow updating progress in activity' => 'Yes',
                'Who can update goal progress?' => $subject_relationship->get_name(),
            ],
            $display_settings
        );
    }

    public function test_is_enabled(): void {
        advanced_feature::enable('perform_goals');
        self::assertTrue(perform_goal_content_type::is_enabled());

        advanced_feature::disable('perform_goals');
        self::assertFalse(perform_goal_content_type::is_enabled());
    }

    public function test_saving_element(): void {
        $content_type_identifier = 'perform_goal';
        $content_type_display_name = 'Totara goals';
        $content_type_display_generic = 'goal';

        $subject_relationship = relationship::load_by_idnumber(mod_perform_constants::RELATIONSHIP_SUBJECT);
        $manager_relationship = relationship::load_by_idnumber(mod_perform_constants::RELATIONSHIP_MANAGER);

        $element1_input_data = [
            'content_type' => $content_type_identifier,
            'content_type_settings' => [
                'enable_status_change' => false,
                'status_change_relationship' => null,
            ],
            'selection_relationships' => [$subject_relationship->id],
        ];
        $element1 = linked_review_generator::instance()->create_linked_review_element($element1_input_data);
        $element1_output_data = json_decode($element1->data, true);
        unset($element1_output_data['components'], $element1_output_data['compatible_child_element_plugins']);
        self::assertEquals([
            'content_type' => $content_type_identifier,
            'content_type_settings' => [
                'enable_status_change' => false,
                'status_change_relationship' => null,
            ],
            'selection_relationships' => [$subject_relationship->id],
            'selection_relationships_display' => [
                [
                    'id' => $subject_relationship->id,
                    'name' => $subject_relationship->name
                ],
            ],
            'content_type_display' => $content_type_display_name,
            'content_type_settings_display' => [
                [
                    'title' => 'Allow updating progress in activity',
                    'value' => 'No',
                ],
            ],
            'content_type_display_generic' => $content_type_display_generic
        ], $element1_output_data);

        $element2_input_data = [
            'content_type' => $content_type_identifier,
            'content_type_settings' => [
                'enable_status_change' => true,
                'status_change_relationship' => $manager_relationship->id,
            ],
            'selection_relationships' => [$manager_relationship->id],
        ];
        $element2 = linked_review_generator::instance()->create_linked_review_element($element2_input_data);
        $element2_output_data = json_decode($element2->data, true);
        unset($element2_output_data['components'], $element2_output_data['compatible_child_element_plugins']);
        self::assertEquals([
            'content_type' => $content_type_identifier,
            'content_type_settings' => [
                'enable_status_change' => true,
                'status_change_relationship' => $manager_relationship->id,
                'status_change_relationship_name' => $manager_relationship->get_name(),
            ],
            'selection_relationships' => [$manager_relationship->id],
            'selection_relationships_display' => [
                [
                    'id' => $manager_relationship->id,
                    'name' => $manager_relationship->name
                ],
            ],
            'content_type_display' => $content_type_display_name,
            'content_type_settings_display' => [
                [
                    'title' => 'Allow updating progress in activity',
                    'value' => 'Yes',
                ],
                [
                    'title' => 'Who can update goal progress?',
                    'value' => $manager_relationship->name,
                ],
            ],
            'content_type_display_generic' => $content_type_display_generic
        ], $element2_output_data);
    }

    public function test_get_content_settings(): void {
        $manager_relationship = relationship::load_by_idnumber(mod_perform_constants::RELATIONSHIP_MANAGER);

        // Set up for test #1.
        $test_content_type_settings_input = [
            'enable_status_change' => false,
            'status_change_relationship' => null,
        ];

        // Operate.
        $content_type_settings_result = perform_goal_content_type::get_content_type_settings($test_content_type_settings_input);
        // Assert.
        self::assertEquals($test_content_type_settings_input, $content_type_settings_result); // Expect to be unchanged.

        // Set up for test #2.
        $test_content_type_settings_input2 = [
            'enable_status_change' => true,
            'status_change_relationship' => $manager_relationship->id,
        ];
        // Operate.
        $content_type_settings_result = perform_goal_content_type::get_content_type_settings($test_content_type_settings_input2);
        // Assert.
        $expected_result = ['enable_status_change' => true,
            'status_change_relationship' => $manager_relationship->id,
            'status_change_relationship_name' => 'Manager'
        ];
        self::assertEquals($expected_result, $content_type_settings_result); // Expect to be changed.
    }

    public function test_load_with_empty_content_items_collection(): void {
        $user = self::getDataGenerator()->create_user();
        $dummy_subject_instance = subject_instance::load_by_entity(new subject_instance_entity([
            'id' => 123456,
            'subject_user_id' => $user->id,
        ]));

        self::setUser($user);

        $content_type = new perform_goal_content_type(context_system::instance());

        $result = $content_type->load_content_items(
            $dummy_subject_instance,
            collection::new([]),
            null,
            true,
            time()
        );

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function test_load_content_items(): void {
        $data = $this->create_activity_data();
        $user = $data->subject_user;
        $goal1 = $data->goal1;
        $goal2 = $data->goal2;

        self::setUser($user);

        $created_at = time();

        $content_items = collection::new([
            $data->linked_review_content1,
            $data->linked_review_content2,
        ]);

        $content_type = new perform_goal_content_type(context_system::instance());

        $subject_instance_model = subject_instance::load_by_entity($data->subject_instance1);
        $result = $content_type->load_content_items(
            $subject_instance_model,
            $content_items,
            null,
            true,
            $created_at
        );

        self::assertIsArray($result);
        self::assertCount(2, $result);

        $goal1_result_item = array_filter($result, static function (array $item) use ($goal1) {
            return (int)$item['id'] === (int)$goal1->id;
        });
        $goal1_result_item = array_shift($goal1_result_item);

        $expected_statuses = array_map(
            function (string $status_code) {
                $status = status_helper::status_from_code($status_code);
                return [
                    'id' => $status->id,
                    'label' => $status->label,
                ];
            },
            status_helper::all_status_codes('perform_goal')
        );

        $date_formatter = new date_field_formatter(date_format::FORMAT_DATE, context_system::instance());
        $formatted_target_date = $date_formatter->format($goal1->target_date);
        $formatted_updated_at = $date_formatter->format($goal1->updated_at);

        $completed_status = status_helper::status_from_code('completed');

        // We expect the snapshot data.
        $expected_content_goal1 = [
            'id' => $goal1->id,
            'goal' => [
                'id' => $goal1->id,
                'plugin_name' => 'basic',
                'name' => 'Snapshot goal 1',
                'description' => '<p>test description 1</p>',
                'status' => [
                    'id' => $completed_status->id,
                    'label' => 'Completed',
                ],
                'target_date' => $formatted_target_date,
                'target_value' => 9.99,
                'current_value' => 0.99,
                'updated_at' => $formatted_updated_at,
            ],
            'available_statuses' => $expected_statuses,
            'can_change_status' => false,
            'status_change' => null,
            'permissions' => [
                'can_view' => true
            ]
        ];

        self::assertEquals($expected_content_goal1, $goal1_result_item);

        $goal2_result_item = array_filter($result, static function (array $item) use ($goal2) {
            return (int)$item['id'] === (int)$goal2->id;
        });
        $goal2_result_item = array_shift($goal2_result_item);

        $not_started_status = status_helper::status_from_code('not_started');
        $formatted_target_date = $date_formatter->format($goal2->target_date);
        $formatted_updated_at = $date_formatter->format($goal2->updated_at);

        // We expect the snapshot data.
        $expected_content_goal2 = [
            'id' => $goal2->id,
            'goal' => [
                'id' => $goal2->id,
                'plugin_name' => 'basic',
                'name' => 'Snapshot goal 2',
                'description' => '<p>test description 2</p>',
                'status' => [
                    'id' => $not_started_status->id,
                    'label' => 'Not started',
                ],
                'target_date' => $formatted_target_date,
                'target_value' => 8.88,
                'current_value' => 0.88,
                'updated_at' => $formatted_updated_at,
            ],
            'available_statuses' => $expected_statuses,
            'can_change_status' => false,
            'status_change' => null,
            'permissions' => [
                'can_view' => true
            ]
        ];
        self::assertEquals($expected_content_goal2, $goal2_result_item);

        // Remove snapshot for goal2.
        element_response_snapshot::repository()
            ->where('item_id', $goal2->id)
            ->delete();

        $result = $content_type->load_content_items(
            $subject_instance_model,
            $content_items,
            null,
            true,
            $created_at
        );

        self::assertIsArray($result);
        self::assertCount(2, $result);

        // goal1 should still be returned from the snapshot.
        $goal1_result_item = array_filter($result, static function (array $item) use ($goal1) {
            return (int)$item['id'] === (int)$goal1->id;
        });
        $goal1_result_item = array_shift($goal1_result_item);
        self::assertEquals('Snapshot goal 1', $goal1_result_item['goal']['name']);

        // goal2 should be fetched from the goal table as a fallback. This is necessary when the goal
        // selection is first made on the front end. No snapshots exist at that point.
        $goal2_result_item = array_filter($result, static function (array $item) use ($goal2) {
            return (int)$item['id'] === (int)$goal2->id;
        });
        $goal2_result_item = array_shift($goal2_result_item);

        $in_progress_status = status_helper::status_from_code('in_progress');

        // Make sure the structure and all the values are fine when it's fetched from the goal table as well.
        $expected_content_goal2 = [
            'id' => $goal2->id,
            'goal' => [
                'id' => $goal2->id,
                'plugin_name' => 'basic',
                'name' => 'Test goal 2 name',
                'description' => '<p>test description 2</p>',
                'status' => [
                    'id' => $in_progress_status->id,
                    'label' => 'In progress',
                ],
                'target_date' => $formatted_target_date,
                'target_value' => 2.22,
                'current_value' => 0.22,
                'updated_at' => $formatted_updated_at,
            ],
            'available_statuses' => $expected_statuses,
            'can_change_status' => false,
            'status_change' => null,
            'permissions' => [
                'can_view' => true
            ]
        ];
        self::assertEquals($expected_content_goal2, $goal2_result_item);

        // Remove snapshot for goal1 as well.
        element_response_snapshot::repository()
            ->where('item_id', $goal1->id)
            ->delete();

        $result = $content_type->load_content_items(
            $subject_instance_model,
            $content_items,
            null,
            true,
            $created_at
        );

        // goal1 and goal2 must come from the goal table now. Just check names to verify this.
        $goal1_result_item = array_filter($result, static function (array $item) use ($goal1) {
            return (int)$item['id'] === (int)$goal1->id;
        });
        $goal1_result_item = array_shift($goal1_result_item);
        self::assertEquals('Test goal 1 name', $goal1_result_item['goal']['name']);

        $goal2_result_item = array_filter($result, static function (array $item) use ($goal2) {
            return (int)$item['id'] === (int)$goal2->id;
        });
        $goal2_result_item = array_shift($goal2_result_item);
        self::assertEquals('Test goal 2 name', $goal2_result_item['goal']['name']);
    }

    public function test_load_content_items_with_status_change(): void {
        $data = $this->create_activity_data();
        $user = $data->subject_user;
        $goal1 = $data->goal1;

        self::setUser($user);

        $created_at = time();

        $content_items = collection::new([
            $data->linked_review_content1,
            $data->linked_review_content2,
        ]);

        // Add a status change record.
        $perform_status_change = perform_status_change::create(
            $data->subject_participant_instance1->id,
            $data->section_element->id,
            'in_progress',
            4.44,
            $goal1->id
        );

        // Hard set the created_at time to be sure about the timestamp.
        $now = time();
        builder::table(perform_status_change_entity::TABLE)
            ->where('id', $perform_status_change->id)
            ->update(['created_at' => $now]);

        $content_type = new perform_goal_content_type(context_system::instance());

        $subject_instance_model = subject_instance::load_by_entity($data->subject_instance1);
        $result = $content_type->load_content_items(
            $subject_instance_model,
            $content_items,
            null,
            true,
            $created_at
        );

        self::assertIsArray($result);
        self::assertCount(2, $result);

        $goal1_result_item = array_filter($result, static function (array $item) use ($goal1) {
            return (int)$item['id'] === (int)$goal1->id;
        });
        $goal1_result_item = array_shift($goal1_result_item);

        $date_formatter = new date_field_formatter(date_format::FORMAT_DATE, context_system::instance());

        $in_progress_status = status_helper::status_from_code('in_progress');

        self::assertEquals([
            'created_at' => $date_formatter->format($now),
            'status_changer_user' => [
                'fullname' => 'Subject User',
            ],
            'status' => [
                'id' => $in_progress_status->id,
                'label' => 'In progress',
            ],
            'current_value' => '4.44',
        ], $goal1_result_item['status_change']);

        // Remove the status changer user id. This can happen on userdata purge.
        builder::table(perform_status_change_entity::TABLE)
            ->where('id', $perform_status_change->id)
            ->update(['status_changer_user_id' => null]);

        $result = $content_type->load_content_items(
            $subject_instance_model,
            $content_items,
            null,
            true,
            $created_at
        );

        self::assertIsArray($result);
        self::assertCount(2, $result);

        $goal1_result_item = array_filter($result, static function (array $item) use ($goal1) {
            return (int)$item['id'] === (int)$goal1->id;
        });
        $goal1_result_item = array_shift($goal1_result_item);

        self::assertEquals([
            'created_at' => $date_formatter->format($now),
            'status_changer_user' => null,
            'status' => [
                'id' => $in_progress_status->id,
                'label' => 'In progress',
            ],
            'current_value' => '4.44',
        ], $goal1_result_item['status_change']);
    }

    public function test_get_goal_status_permissions(): void {
        $data = $this->create_activity_data();
        $participant_instance = participant_instance::load_by_entity($data->manager_participant_instance1);
        $subject_relationship = relationship::load_by_idnumber('subject')->id;
        $manager_relationship = relationship::load_by_idnumber('manager')->id;
        self::setUser($data->manager_user);

        $element = new element_entity($data->section_element->element_id);
        $element_data = [
            'content_type' => 'perform_goal',
            'content_type_settings' => [
                'enable_status_change' => false,
                'status_change_relationship' => null,
            ],
            'selection_relationships' => [$subject_relationship],
        ];
        $element->data = json_encode($element_data);
        $element->save();

        $content_items = linked_review_content_model::get_existing_selected_content(
            $data->section_element->id,
            $data->subject_instance1->id
        );

        // Can't view status as false is passed in for 'view other responses'.
        // Can't change status as it is disabled on the element.
        [$can_view, $can_change] = perform_goal_content_type::get_goal_status_permissions(
            $content_items,
            $data->manager_participant_section1,
            false
        );
        self::assertFalse($can_view);
        self::assertFalse($can_change);

        // Can view status as true is passed in for 'view other responses'.
        [$can_view, $can_change] = perform_goal_content_type::get_goal_status_permissions(
            $content_items,
            $data->manager_participant_section1,
            true
        );
        self::assertTrue($can_view);
        self::assertFalse($can_change);

        $element_data['content_type_settings']['enable_status_change'] = true;
        $element_data['content_type_settings']['status_change_relationship'] = $subject_relationship;
        $element->data = json_encode($element_data);
        $element->save();
        // Refresh content items, so they include the updated element data.
        $content_items = linked_review_content_model::get_existing_selected_content(
            $data->section_element->id,
            $data->subject_instance1->id
        );

        // Can't change status as user is not of the status_change_relationship
        [$can_view, $can_change] = perform_goal_content_type::get_goal_status_permissions(
            $content_items,
            $data->manager_participant_section1,
            true
        );
        self::assertTrue($can_view);
        self::assertFalse($can_change);

        $element_data['content_type_settings']['enable_status_change'] = true;
        $element_data['content_type_settings']['status_change_relationship'] = $manager_relationship;
        $element->data = json_encode($element_data);
        $element->save();
        $content_items = linked_review_content_model::get_existing_selected_content(
            $data->section_element->id,
            $data->subject_instance1->id
        );

        // Can change status as it is the correct relationship
        [$can_view, $can_change] = perform_goal_content_type::get_goal_status_permissions(
            $content_items,
            $data->manager_participant_section1,
            true
        );
        self::assertTrue($can_view);
        self::assertTrue($can_change);

        // Can view status even when passing in false for viewing other responses.
        [$can_view, $can_change] = perform_goal_content_type::get_goal_status_permissions(
            $content_items,
            $data->manager_participant_section1,
            false
        );
        self::assertTrue($can_view);
        self::assertTrue($can_change);

        $section_relationship = section_relationship::repository()
            ->where('core_relationship_id', $participant_instance->core_relationship_id)
            ->where('section_id', $data->section_element->section_id)
            ->get()
            ->first();
        $section_relationship->delete();
        $content_items = linked_review_content_model::get_existing_selected_content(
            $data->section_element->id,
            $data->subject_instance1->id
        );

        // Can't change status as the relationship doesn't exist on the section.
        [$can_view, $can_change] = perform_goal_content_type::get_goal_status_permissions(
            $content_items,
            $data->manager_participant_section1,
            true
        );
        self::assertTrue($can_view);
        self::assertFalse($can_change);

        // Re-create relationship.
        $section_relationship = new section_relationship($section_relationship->to_array());
        $section_relationship->save();
        $content_items = linked_review_content_model::get_existing_selected_content(
            $data->section_element->id,
            $data->subject_instance1->id
        );
        [$can_view, $can_change] = perform_goal_content_type::get_goal_status_permissions(
            $content_items,
            $data->manager_participant_section1,
            true
        );
        self::assertTrue($can_view);
        self::assertTrue($can_change);

        // Can't change status as there is no participant section record.
        participant_section::repository()->delete();
        $content_items = linked_review_content_model::get_existing_selected_content(
            $data->section_element->id,
            $data->subject_instance1->id
        );
        [$can_view, $can_change] = perform_goal_content_type::get_goal_status_permissions(
            $content_items,
            $data->manager_participant_section1,
            true
        );
        self::assertTrue($can_view);
        self::assertFalse($can_change);
    }

    public function test_load_content_items_for_deleted_goals(): void {
        $data = $this->create_activity_data();
        $user = $data->subject_user;
        /** @var goal_model $goal1 */
        $goal1 = $data->goal1;
        /** @var goal_model $goal2 */
        $goal2 = $data->goal2;

        self::setUser($user);

        // Set status_change_relationship on the element, so we are allowed to change status.
        $subject_relationship = relationship::load_by_idnumber('subject')->id;
        $element = new element_entity($data->section_element->element_id);
        $element_data = [
            'content_type' => 'perform_goal',
            'content_type_settings' => [
                'enable_status_change' => true,
                'status_change_relationship' => $subject_relationship,
            ],
            'selection_relationships' => [$subject_relationship],
        ];
        $element->data = json_encode($element_data, JSON_THROW_ON_ERROR);
        $element->save();

        $created_at = time();

        $content_items = collection::new([
            $data->linked_review_content1,
            $data->linked_review_content2,
        ]);

        $content_type = new perform_goal_content_type(context_system::instance());

        $subject_instance_model = subject_instance::load_by_entity($data->subject_instance1);
        $result = $content_type->load_content_items(
            $subject_instance_model,
            $content_items,
            $data->subject_participant_section1,
            true,
            $created_at
        );

        self::assertIsArray($result);
        self::assertCount(2, $result);

        $goal1_result_item = array_filter($result, static fn (array $item) => (int)$item['id'] === (int)$goal1->id);
        $goal1_result_item = array_shift($goal1_result_item);

        $goal2_result_item = array_filter($result, static fn (array $item) => (int)$item['id'] === (int)$goal2->id);
        $goal2_result_item = array_shift($goal2_result_item);

        // Subject user can change status on both goals.
        self::assertTrue($goal1_result_item['can_change_status']);
        self::assertTrue($goal2_result_item['can_change_status']);

        $goal1_id = $goal1->id;

        // Delete goal1.
        $goal1->delete();

        $result = $content_type->load_content_items(
            $subject_instance_model,
            $content_items,
            $data->subject_participant_section1,
            true,
            $created_at
        );

        self::assertIsArray($result);
        self::assertCount(2, $result);

        $goal1_result_item = array_filter($result, static fn (array $item) => (int)$item['id'] === (int)$goal1_id);
        $goal1_result_item = array_shift($goal1_result_item);

        $goal2_result_item = array_filter($result, static fn (array $item) => (int)$item['id'] === (int)$goal2->id);
        $goal2_result_item = array_shift($goal2_result_item);

        // The data should still be returned from the snapshot, but can_change_status must be false for goal1 because it is deleted.
        self::assertFalse($goal1_result_item['can_change_status']);
        self::assertTrue($goal2_result_item['can_change_status']);
    }

    protected function create_activity_data(string $status_change_relationship = 'manager'): stdClass {
        self::setAdminUser();

        $another_user = self::getDataGenerator()->create_user(['firstname' => 'Another', 'lastname' => 'User']);
        $manager_user = self::getDataGenerator()->create_user(['firstname' => 'Manager', 'lastname' => 'User']);
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

        $perform_generator = perform_generator::instance();
        $activity = $perform_generator->create_activity_in_container(['activity_name' => 'Test activity']);
        $section = $perform_generator->create_section($activity);
        $manager_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => mod_perform_constants::RELATIONSHIP_MANAGER]
        );
        $subject_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => mod_perform_constants::RELATIONSHIP_SUBJECT]
        );
        $appraiser_section_relationship = $perform_generator->create_section_relationship(
            $section,
            ['relationship' => mod_perform_constants::RELATIONSHIP_APPRAISER]
        );
        $element = element::create($activity->get_context(), 'linked_review', 'title', '', json_encode([
            'content_type' => 'perform_goal',
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

        $data = new stdClass();
        $data->another_user = $another_user;
        $data->manager_user = $manager_user;
        $data->subject_user = $subject_user;
        $data->activity = $activity;
        $data->subject_instance1 = $subject_instance1;
        $data->subject_participant_instance1 = $subject_participant_section1->participant_instance;
        $data->subject_participant_section1 = $subject_participant_section1;
        $data->manager_participant_instance1 = $manager_participant_section1->participant_instance;
        $data->manager_participant_section1 = $manager_participant_section1;
        $data->section_element = $section_element;
        $data->section = $section;

        [$goal1, $goal2] = $this->create_goals_with_snapshots($data);

        $linked_review_content1 = linked_review_content::create(
            $goal1->id, $section_element->id, $subject_participant_section1->participant_instance_id, false
        );
        $linked_review_content2 = linked_review_content::create(
            $goal2->id, $section_element->id, $subject_participant_section1->participant_instance_id, false
        );

        $data->linked_review_content1 = $linked_review_content1;
        $data->linked_review_content2 = $linked_review_content2;
        $data->goal1 = $goal1;
        $data->goal2 = $goal2;

        return $data;
    }

    /**
     * @param stdClass $activity_data
     * @return array
     */
    protected function create_goals_with_snapshots(stdClass $activity_data): array {
        self::setAdminUser();

        $goal_generator = goal_generator::instance();

        $goal_subject_user = $activity_data->subject_user;
        $user_context = context_user::instance($goal_subject_user->id);

        $goal_category = $goal_generator->create_goal_category();

        $now = time();
        $goal1_test_config = goal_generator_config::with_category($goal_category, [
            'context' => $user_context,
            'name' => 'Test goal 1 name',
            'user_id' => $goal_subject_user->id,
            'description' => '{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[{"type":"text","text":"test description 1"}]}]}',
            'target_date' => $now + YEARSECS,
            'target_value' => 1.11,
            'current_value' => 0.11,
            'status' => 'in_progress',
        ]);
        $goal1 = goal_generator::instance()->create_goal($goal1_test_config);

        $goal2_test_config = goal_generator_config::with_category($goal_category, [
            'context' => $user_context,
            'name' => 'Test goal 2 name',
            'user_id' => $goal_subject_user->id,
            'description' => '{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[{"type":"text","text":"test description 2"}]}]}',
            'target_date' => $now + YEARSECS,
            'target_value' => 2.22,
            'current_value' => 0.22,
            'status' => 'in_progress',
        ]);
        $goal2 = goal_generator::instance()->create_goal($goal2_test_config);

        // Create another goal that is not expected to be returned.
        $goal3 = goal_generator::instance()->create_goal(goal_generator_config::new());

        // Create an element_response record.
        $element_response = new element_response();
        $element_response->section_element_id = $activity_data->section_element->id;
        $element_response->participant_instance_id = $activity_data->subject_participant_instance1->id;
        $element_response->save();

        // Manipulate goal1 data for the snapshot.
        $goal_entity = new goal_entity($goal1->id);
        $goal_entity->name = 'Snapshot goal 1';
        $goal_entity->status = 'completed';
        $goal_entity->current_value = 0.99;
        $goal_entity->target_value = 9.99;
        $goal_snapshot = goal_snapshot::json_encode($goal_entity);

        // Create a snapshot record for goal1.
        $snapshot = new element_response_snapshot();
        $snapshot->response_id = $element_response->id;
        $snapshot->item_type = goal_snapshot::ITEM_TYPE;
        $snapshot->item_id = $goal1->id;
        $snapshot->snapshot = $goal_snapshot;
        $snapshot->save();

        // Manipulate goal2 data for the snapshot.
        $goal_entity = new goal_entity($goal2->id);
        $goal_entity->name = 'Snapshot goal 2';
        $goal_entity->status = 'not_started';
        $goal_entity->current_value = 0.88;
        $goal_entity->target_value = 8.88;
        $goal_snapshot = goal_snapshot::json_encode($goal_entity);

        // Create a snapshot record for goal2.
        $snapshot = new element_response_snapshot();
        $snapshot->response_id = $element_response->id;
        $snapshot->item_type = goal_snapshot::ITEM_TYPE;
        $snapshot->item_id = $goal2->id;
        $snapshot->snapshot = $goal_snapshot;
        $snapshot->save();

        // Create a snapshot record for goal3 (additional goal to control for contamination).
        $snapshot = new element_response_snapshot();
        $snapshot->response_id = $element_response->id;
        $snapshot->item_type = goal_snapshot::ITEM_TYPE;
        $snapshot->item_id = $goal3->id;
        $snapshot->snapshot = goal_snapshot::json_encode(['name' => 'should not show up anywhere']);
        $snapshot->save();

        return [$goal1, $goal2];
    }
}
