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
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use performelement_perform_goal_creation\model\goal_snapshot;
use performelement_perform_goal_creation\testing\perform_goal_creation_test_trait;

/**
 * @group perform
 * @group perform_element
 * @coversDefaultClass \performelement_perform_goal_creation\model\goal_snapshot
 */
class performelement_perform_goal_creation_goal_snapshot_test extends testcase {

    use perform_goal_creation_test_trait;

    /**
     * @covers ::create
     */
    public function test_create() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $config = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal = goal_generator::instance()->create_goal($config);

        $section_element_response = $this->create_perform_goal_creation_response($test_user->id);

        $now = time();
        $snapshot = goal_snapshot::create($section_element_response, $goal);

        // Assert attributes
        $this->assertIsInt($snapshot->id);
        $this->assertEquals($section_element_response->id, $snapshot->response_id);
        $this->assertEquals(goal_snapshot::ITEM_TYPE, $snapshot->item_type);
        $this->assertEquals($goal->id, $snapshot->item_id);
        $this->assertNotEmpty($snapshot->snapshot_raw);
        $this->assertGreaterThanOrEqual($now, $snapshot->created_at);
        $this->assertGreaterThanOrEqual($snapshot->created_at, $snapshot->updated_at);

        // Assert methods
        $resolved_response = $snapshot->section_element_response;
        $this->assertEquals($resolved_response->id, $section_element_response->id);
        $this->assertInstanceOf(section_element_response::class, $resolved_response);
        $resolved_goal = $snapshot->original;
        $this->assertEquals($resolved_goal->id, $goal->id);
        $this->assertInstanceOf(\perform_goal\model\goal::class, $resolved_goal);

        // Assert snapshot object
        $snapshot_goal = $snapshot->snapshot;
        $properties_expected = [
            'id',
            'category_id',
            'context_id',
            'owner_id',
            'user_id',
            'name',
            'id_number',
            'description',
            'start_date',
            'target_type',
            'target_date',
            'target_value',
            'current_value',
            'current_value_updated_at',
            'status',
            'status_updated_at',
            'closed_at',
            'created_at',
            'updated_at',
        ];
        foreach ($properties_expected as $key) {
            if (!property_exists($snapshot_goal, $key)) {
                $this->fail('Goal snapshot missing expected key: ' . $key);
            }
            $this->assertEquals($goal->{$key}, $snapshot_goal->{$key});
        }
        foreach (get_object_vars($snapshot_goal) as $key => $value) {
            if (!in_array($key, $properties_expected)) {
                $this->fail('Unexpected goal snapshot property in snapshot: ' . $key);
            }
        }
    }

    /**
     * @covers ::update_snapshot
     */
    public function test_update_snapshot() {
        self::setAdminUser();

        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);

        $config = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal = goal_generator::instance()->create_goal($config);

        $section_element_response = $this->create_perform_goal_creation_response($test_user->id);

        $snapshot = goal_snapshot::create($section_element_response, $goal);
        $this->assertEquals($goal->name, $snapshot->snapshot->name);

        // Use the entity to ensure the snapshot isn't updated by an event observer.
        $goal_entity = new \perform_goal\entity\goal($goal->id);
        $goal_entity->name = 'Flavour is goal!';
        $goal_entity->save();
        $reloaded_snapshot = $snapshot->refresh();
        $this->assertEquals($snapshot->snapshot->name, $reloaded_snapshot->snapshot->name);
        $this->assertNotEquals('Flavour is goal!', $reloaded_snapshot->snapshot->name);

        $updated_snapshot = $snapshot->update_snapshot();
        $this->assertEquals('Flavour is goal!', $updated_snapshot->snapshot->name);
    }
}