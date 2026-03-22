<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

use core\format;
use core_phpunit\testcase;
use mod_perform\models\activity\participant_source;
use mod_perform\state\participant_section\closed as section_closed;
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\status\in_progress;
use perform_goal\model\status\not_started;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use performelement_perform_goal_creation\testing\perform_goal_creation_test_trait;
use mod_perform\data_providers\response\participant_section as participant_section_provider;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform
 * @group perform_element
 */
class mod_perform_webapi_resolver_type_section_element_response_test extends testcase {

    use perform_goal_creation_test_trait;
    use webapi_phpunit_helper;

    private const TYPE = 'mod_perform_section_element_response';

    public function test_response_data_formatted_lines_not_completed(): void {
        [$section_element_response, $subject_user, $goal1, $goal2] = $this->set_data();
        $subject_user_context = context_user::instance($subject_user->id);

        $participant_instance = $section_element_response->get_participant_instance();
        $section_provider = new participant_section_provider($subject_user->id, participant_source::INTERNAL);
        $participant_section = $section_provider->find_by_instance_id($participant_instance->get_id());

        static::assertFalse($participant_section->is_complete());
        static::assertNotEquals(section_closed::get_code(), (int)$participant_section->availability);

        $value = $this->resolve_graphql_type(
            self::TYPE,
            'response_data_formatted_lines',
            $section_element_response,
            ['format' => format::FORMAT_PLAIN],
            $subject_user_context
        );

        static::assertNotEmpty($value);
        static::assertCount(2, $value);

        // The performance instance is not closed and not completed lets delete the goal 2 and test again.
        $goal2->delete();
        $data = json_encode([$goal1->id], JSON_THROW_ON_ERROR);
        $section_element_response->set_response_data($data);
        $section_element_response->save();

        // Test participant section is still not completed/closed
        $participant_section->refresh();
        static::assertFalse($participant_section->is_complete());
        static::assertNotEquals(section_closed::get_code(), (int)$participant_section->availability);

        $value = $this->resolve_graphql_type(
            self::TYPE,
            'response_data_formatted_lines',
            $section_element_response,
            ['format' => format::FORMAT_PLAIN],
            $subject_user_context
        );

        static::assertNotEmpty($value);
        static::assertCount(1, $value);
    }

    public function test_response_data_formatted_lines_completed(): void {
        [$section_element_response, $subject_user, , $goal2] = $this->set_data();
        $subject_user_context = context_user::instance($subject_user->id);

        $participant_instance = $section_element_response->get_participant_instance();
        $section_provider = new participant_section_provider($subject_user->id, participant_source::INTERNAL);
        $participant_section = $section_provider->find_by_instance_id($participant_instance->get_id());

        static::assertFalse($participant_section->is_complete());
        static::assertNotEquals(section_closed::get_code(), (int)$participant_section->availability);

        $value = $this->resolve_graphql_type(
            self::TYPE,
            'response_data_formatted_lines',
            $section_element_response,
            ['format' => format::FORMAT_PLAIN],
            $subject_user_context
        );

        static::assertNotEmpty($value);
        static::assertCount(2, $value);

        // Now complete and close participant instance while we have goal1 and goal2 as our response data.
        $participant_section->complete();
        $participant_section->get_availability_state()->close();
        static::assertTrue($participant_section->is_complete());
        static::assertEquals(section_closed::get_code(), (int)$participant_section->availability);

        // After the performance instance is closed and completed lets delete the goal 2 and test again.
        $goal2->delete();

        $value = $this->resolve_graphql_type(
            self::TYPE,
            'response_data_formatted_lines',
            $section_element_response,
            ['format' => format::FORMAT_PLAIN],
            $subject_user_context
        );

        static::assertNotEmpty($value);
        static::assertCount(2, $value);
    }

    protected function set_data(): array {
        $subject_user = static::getDataGenerator()->create_user();
        static::setUser($subject_user);

        $now = time();

        $goal_generator = goal_generator::instance();
        $goal1 = $goal_generator->create_goal(goal_generator_config::new([
            'name' => 'Personal goal 1',
            'id_number' => 'goal_1_id_number',
            'user_id' => $subject_user->id,
            'context' => context_user::instance($subject_user->id),
            'description' => 'description goal 1',
            'start_date' => $now + DAYSECS,
            'target_type' => 'date',
            'target_date' => $now + (2 * DAYSECS),
            'target_value' => 111,
            'current_value' => 55,
            'status' => not_started::get_code(),
            'closed_at' => null,
        ]));

        // Adjust a few dates manually.
        goal_entity::repository()
            ->update_record([
                'id' => $goal1->id,
                'current_value_updated_at' => $now + (3 * DAYSECS),
                'created_at' => $now - DAYSECS,
                'updated_at' => $now - HOURSECS,
            ]);

        $goal2 = $goal_generator->create_goal(goal_generator_config::new([
            'name' => 'Personal goal 2',
            'id_number' => 'goal_2_id_number',
            'user_id' => $subject_user->id,
            'context' => context_user::instance($subject_user->id),
            'description' => 'description goal 2',
            'start_date' => $now + WEEKSECS,
            'target_type' => 'date',
            'target_date' => $now + (2 * WEEKSECS),
            'target_value' => 222,
            'current_value' => 66,
            'status' => in_progress::get_code(),
            'closed_at' => null,
        ]));

        // Adjust a few dates manually.
        goal_entity::repository()
            ->update_record([
                'id' => $goal2->id,
                'current_value_updated_at' => $now + (3 * WEEKSECS),
                'created_at' => $now - WEEKSECS,
                'updated_at' => $now - 60,
            ]);

        $section_element_response = $this->create_perform_goal_creation_response($subject_user->id);
        $data = json_encode([$goal1->id, $goal2->id], JSON_THROW_ON_ERROR);
        $section_element_response->set_response_data($data);
        // When save() is called, the hook should be executed.
        $section_element_response->save();

        return [$section_element_response, $subject_user, $goal1, $goal2];
    }
}
