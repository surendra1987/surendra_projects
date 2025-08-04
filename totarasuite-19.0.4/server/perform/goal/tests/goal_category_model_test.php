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
 * @author  Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use perform_goal\entity\goal_category as goal_category_entity;
use perform_goal\event\goal_category_activated;
use perform_goal\event\goal_category_created;
use perform_goal\event\goal_category_deactivated;
use perform_goal\model\goal_category;

class perform_goal_goal_category_model_test extends testcase {

    public function test_create_plugin_name_empty(): void {
        self::setAdminUser();
        try {
            $goal_category = goal_category::create('', 'Basic');
            $this->fail('Expected coding_exception');
        } catch (coding_exception $e) {
            $this->assertStringContainsString('Goal category must have a plugin name.', $e->getMessage());
        }
    }

    public function test_create_plugin_must_exist(): void {
        self::setAdminUser();
        try {
            $goal_category = goal_category::create('foo', 'Basic');
            $this->fail('Expected coding_exception');
        } catch (coding_exception $e) {
            $this->assertStringContainsString('Invalid goal_category plugin name, class foo does not exist.', $e->getMessage());
        }
    }

    public function test_create_name_must_not_be_empty(): void {
        self::setAdminUser();
        try {
            $goal_category = goal_category::create('basic', ' ');
            $this->fail('Expected coding_exception');
        } catch (coding_exception $e) {
            $this->assertStringContainsString('Goal category must have a name.', $e->getMessage());
        }
    }

    public function test_create_name_must_not_be_too_long(): void {
        self::setAdminUser();
        $goal_category = goal_category::create('basic', str_repeat('z', 1024));
        try {
            $goal_category = goal_category::create('basic', str_repeat('z', 1025));
            $this->fail('Expected coding_exception');
        } catch (coding_exception $e) {
            $this->assertStringContainsString('Goal category name must not be longer than 1024 characters.', $e->getMessage());
        }
    }

    public function test_create_duplicate_id_number(): void {
        self::setAdminUser();
        $goal_category1 = goal_category::create('basic', 'Basic 1', 'basic-goal-1');
        try {
            $goal_category2 = goal_category::create('basic', 'Basic 2', 'basic-goal-1');
            $this->fail('Expected coding_exception');
        } catch (coding_exception $e) {
            $this->assertStringContainsString('id_number already exists: basic-goal-1', $e->getMessage());
        }
    }

    public function test_create_successful(): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $sink = $this->redirectEvents();
        $sink->clear();

        $goal_category = goal_category::create('basic', 'Test Category', 'basic-goal-1');

        $this->assertEquals('basic', $goal_category->plugin_name);
        $this->assertEquals('Test Category', $goal_category->name);
        $this->assertEquals('basic-goal-1', $goal_category->id_number);
        $this->assertFalse($goal_category->active);

        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $event = array_shift($events);
        $this->assertInstanceOf(goal_category_created::class, $event);
        $this->assertEquals($goal_category->id, $event->objectid);
        $this->assertEquals(context_system::instance()->id, $event->contextid);
        $this->assertEquals($owner->id, $event->userid);
    }

    public function test_get_name_with_langstring() {
        $goal_category1 = goal_category::create('basic', 'multilang:basic', 'basic-goal-1');
        $this->assertEquals('Basic', $goal_category1->name);

        $goal_category2 = goal_category::create('basic', 'multilang:not a lang string', 'basic-goal-2');
        $this->assertEquals('multilang:not a lang string', $goal_category2->name);
    }

    public function test_get_component() {
        $goal_category = goal_category::create('basic', 'Test Category', 'basic-goal-1');
        $this->assertEquals('goaltype_basic', $goal_category->component);
    }

    public function test_activate(): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $goal_category = goal_category::create('basic', 'Test Category', 'basic-goal-1');
        $this->assertFalse($goal_category->active);

        $sink = $this->redirectEvents();
        $sink->clear();

        $goal_category->activate();
        $this->assertTrue($goal_category->active);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $event = array_shift($events);
        $this->assertInstanceOf(goal_category_activated::class, $event);
        $this->assertEquals($goal_category->id, $event->objectid);
        $this->assertEquals(context_system::instance()->id, $event->contextid);
        $this->assertEquals($owner->id, $event->userid);
    }

    public function test_deactivate(): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $goal_category = goal_category::create('basic', 'Test Category', 'basic-goal-1');
        $goal_category->activate();
        $this->assertEquals(true, $goal_category->active);

        $sink = $this->redirectEvents();
        $sink->clear();

        $goal_category->deactivate();
        $this->assertFalse($goal_category->active);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $event = array_shift($events);
        $this->assertInstanceOf(goal_category_deactivated::class, $event);
        $this->assertEquals($goal_category->id, $event->objectid);
        $this->assertEquals(context_system::instance()->id, $event->contextid);
        $this->assertEquals($owner->id, $event->userid);

        // Activate it again?
        $goal_category->activate();
        $this->assertTrue($goal_category->active);

    }

    public function test_refresh(): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $goal_category = goal_category::create('basic', 'Test Category');

        $this->assertEquals('Test Category', $goal_category->name);

        builder::table(goal_category_entity::TABLE)->update(['name' => 'Something else']);

        $this->assertEquals('Test Category', $goal_category->name);

        $goal_category->refresh();
        $this->assertEquals('Something else', $goal_category->name);
    }
}