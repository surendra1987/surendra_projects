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

use core_phpunit\testcase;
use mod_perform\entity\activity\element_response_snapshot;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\performelement_linked_review\goal_snapshot;
use perform_goal\testing\performelement_linked_review_test_trait;

/**
 * @group perform
 * @group perform_element
 * @coversDefaultClass \perform_goal\performelement_linked_review\post_response_submission
 */
class perform_goal_performelement_linked_review_post_response_submission_test extends testcase {

    use performelement_linked_review_test_trait;

    /**
     * @covers ::process_response
     */
    public function test_process_response() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $config = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal = goal_generator::instance()->create_goal($config);

        $section_element_response = $this->create_perform_goal_linked_review_response($test_user->id);
        $this->assertCount(0, element_response_snapshot::repository()->get());

        // When save() is called, the hook should be executed.
        $section_element_response = $this->save_perform_goal_linked_review_content($section_element_response, [$goal]);
        $this->assertCount(1, element_response_snapshot::repository()->get());

        $snapshot = element_response_snapshot::repository()->order_by('id')->first();
        $this->assertEquals($section_element_response->id, $snapshot->response_id);
        $this->assertEquals(goal_snapshot::ITEM_TYPE, $snapshot->item_type);
        $this->assertEquals($goal->id, $snapshot->item_id);
        $this->assertNotEmpty($snapshot->snapshot);
    }

    /**
     * @covers ::process_response
     */
    public function test_process_response_twice_same_goals() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $config1 = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal1 = goal_generator::instance()->create_goal($config1);
        $config2 = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal2 = goal_generator::instance()->create_goal($config2);
        $config3 = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal3 = goal_generator::instance()->create_goal($config3);

        $section_element_response = $this->create_perform_goal_linked_review_response($test_user->id);
        $this->assertCount(0, element_response_snapshot::repository()->get());

        // When save() is called, the hook should be executed.
        $section_element_response = $this->save_perform_goal_linked_review_content($section_element_response, [$goal2, $goal3]);
        $this->assertCount(2, element_response_snapshot::repository()->get());
        $this->assertCount(0, element_response_snapshot::repository()->where('item_id', '=', $goal1->id)->get());

        // Update $goal2 using entity rather than model to avoid event.
        $goal2_entity = new \perform_goal\entity\goal($goal2->id);
        $goal2_entity->name = 'Changed my name!';
        $goal2_entity->save();

        // Hit that hook again.
        $section_element_response->save();
        $this->assertCount(2, element_response_snapshot::repository()->get());

        // Assert that we have an updated snapshot.
        $snapshot2 = element_response_snapshot::repository()->where('item_id', '=', $goal2->id)->one(true);
        $this->assertStringContainsString($goal2_entity->name, $snapshot2->snapshot);
    }

    /**
     * @covers ::process_response
     */
    public function test_process_response_twice_different_goals() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $config1 = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal1 = goal_generator::instance()->create_goal($config1);
        $config2 = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal2 = goal_generator::instance()->create_goal($config2);
        $config3 = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal3 = goal_generator::instance()->create_goal($config3);

        $section_element_response = $this->create_perform_goal_linked_review_response($test_user->id);
        $this->assertCount(0, element_response_snapshot::repository()->get());

        // When save() is called, the hook should be executed.
        $section_element_response = $this->save_perform_goal_linked_review_content($section_element_response, [$goal2, $goal3]);

        $this->assertCount(2, element_response_snapshot::repository()->get());
        $this->assertCount(0, element_response_snapshot::repository()->where('item_id', '=', $goal1->id)->get());
        $this->assertCount(1, element_response_snapshot::repository()->where('item_id', '=', $goal2->id)->get());
        $this->assertCount(1, element_response_snapshot::repository()->where('item_id', '=', $goal3->id)->get());

        // Now change the response.
        $section_element_response = $this->save_perform_goal_linked_review_content($section_element_response, [$goal1, $goal3]);

        // Hit that hook one more time.
        $section_element_response->save();
        $this->assertCount(2, element_response_snapshot::repository()->get());
        $this->assertCount(1, element_response_snapshot::repository()->where('item_id', '=', $goal1->id)->get());
        $this->assertCount(0, element_response_snapshot::repository()->where('item_id', '=', $goal2->id)->get());
        $this->assertCount(1, element_response_snapshot::repository()->where('item_id', '=', $goal3->id)->get());
    }
}
