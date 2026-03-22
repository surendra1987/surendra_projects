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


use core_phpunit\testcase;
use mod_perform\models\response\section_element_response;
use mod_perform\state\participant_section\closed;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use performelement_perform_goal_creation\model\goal_snapshot;
use performelement_perform_goal_creation\testing\perform_goal_creation_test_trait;

/**
 * @group perform
 * @group perform_element
 * @coversDefaultClass \performelement_perform_goal_creation\observer\perform_goal_updated
 */
class performelement_perform_goal_creation_observer_perform_goal_updated_test extends testcase {

    use perform_goal_creation_test_trait;

    /**
     * @covers ::goal_updated
     */
    public function test_with_update_details() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $goal_details = [
            'context' => $test_user_context,
            'user_id' => $test_user->id,
            'name' => 'Flavor is gaol!',
        ];
        $config = goal_generator_config::new($goal_details);
        $goal = goal_generator::instance()->create_goal($config);

        $section_element_response = $this->create_perform_goal_creation_response($test_user->id);

        $snapshot = goal_snapshot::create($section_element_response, $goal);
        $this->assertEquals($goal_details['name'], $snapshot->snapshot->name);

        // Updating the goal should automatically update the snapshot.
        $goal->update('Flavour is goal!');
        $snapshot->refresh();
        $this->assertEquals('Flavour is goal!', $snapshot->snapshot->name);
    }

    /**
     * @covers ::goal_updated
     */
    public function test_with_update_progress() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $goal_details = [
            'context' => $test_user_context,
            'user_id' => $test_user->id,
        ];
        $config = goal_generator_config::new($goal_details);
        $goal = goal_generator::instance()->create_goal($config);

        $section_element_response = $this->create_perform_goal_creation_response($test_user->id);

        $snapshot = goal_snapshot::create($section_element_response, $goal);
        $this->assertEquals('not_started', $snapshot->snapshot->status);

        // Updating the goal should automatically update the snapshot.
        $goal->update_progress('in_progress', 10);
        $snapshot->refresh();
        $this->assertEquals('in_progress', $snapshot->snapshot->status);
    }

    /**
     * @covers ::goal_updated
     */
    public function test_with_update_progress_on_closed_participant_section() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $goal_details = [
            'context' => $test_user_context,
            'user_id' => $test_user->id,
        ];
        $config = goal_generator_config::new($goal_details);
        $goal = goal_generator::instance()->create_goal($config);

        $section_element_response = $this->create_perform_goal_creation_response($test_user->id);

        $snapshot = goal_snapshot::create($section_element_response, $goal);
        $this->assertEquals('not_started', $snapshot->snapshot->status);

        $participant_section = $section_element_response->get_participant_instance()->participant_sections->first();
        $participant_section->switch_state(closed::class);

        // Updating the goal should ignore the snapshot because the section is closed.
        $goal->update_progress('in_progress', 10);
        $snapshot->refresh();
        $this->assertNotEquals('in_progress', $snapshot->snapshot->status);
    }

    /**
     * @covers ::goal_updated
     */
    public function test_with_update_progress_on_closed_participant_instance() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $goal_details = [
            'context' => $test_user_context,
            'user_id' => $test_user->id,
        ];
        $config = goal_generator_config::new($goal_details);
        $goal = goal_generator::instance()->create_goal($config);

        $section_element_response = $this->create_perform_goal_creation_response($test_user->id);

        $snapshot = goal_snapshot::create($section_element_response, $goal);
        $this->assertEquals('not_started', $snapshot->snapshot->status);

        $participant_instance = $section_element_response->get_participant_instance();
        $participant_instance->switch_state(\mod_perform\state\participant_instance\closed::class);

        // Updating the goal should ignore the snapshot because the section is closed.
        $goal->update_progress('in_progress', 10);
        $snapshot->refresh();
        $this->assertNotEquals('in_progress', $snapshot->snapshot->status);
    }
}