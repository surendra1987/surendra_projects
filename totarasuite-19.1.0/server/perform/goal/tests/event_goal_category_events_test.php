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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\event\goal_category_activated;
use perform_goal\event\goal_category_created;
use perform_goal\event\goal_category_deactivated;
use perform_goal\entity\goal_category as perform_goal_category_entity;
use perform_goal\testing\generator;

class perform_goal_event_goal_category_events_test extends testcase {

    public function test_event_goal_category_created() {
        $owner = self::getDataGenerator()->create_user();
        $this->setUser($owner);
        $goal_category = generator::instance()->create_goal_category();

        /** @var perform_goal_category_entity $perform_goal */
        $perform_goal_category = new perform_goal_category_entity($goal_category->id);

        $event = goal_category_created::create_from_instance($perform_goal_category);
        $event->trigger();

        $this->assertEquals(\context_system::instance()->id, $event->get_context()->id);
        $this->assertSame($perform_goal_category->id, $event->objectid);
        $this->assertSame('c', $event->crud);
        $this->assertSame($event::LEVEL_OTHER, $event->edulevel);
        $this->assertSame(perform_goal_category_entity::TABLE, $event->objecttable);
        $this->assertSame(
            "The user with id '{$owner->id}' has created a goal_category with id '{$perform_goal_category->id}'",
            $event->get_description()
        );
        $this->assertEventContextNotUsed($event);
    }

    public function test_event_goal_category_activated() {
        $owner = self::getDataGenerator()->create_user();
        $this->setUser($owner);
        $goal_category = generator::instance()->create_goal_category();

        /** @var perform_goal_category_entity $perform_goal */
        $perform_goal_category = new perform_goal_category_entity($goal_category->id);

        $event = goal_category_activated::create_from_instance($perform_goal_category);
        $event->trigger();

        $this->assertEquals(\context_system::instance()->id, $event->get_context()->id);
        $this->assertSame($perform_goal_category->id, $event->objectid);
        $this->assertSame('u', $event->crud);
        $this->assertSame($event::LEVEL_OTHER, $event->edulevel);
        $this->assertSame(perform_goal_category_entity::TABLE, $event->objecttable);
        $this->assertSame(
            "The user with id '{$owner->id}' has activated the goal_category with id '{$perform_goal_category->id}'",
            $event->get_description()
        );
        $this->assertEventContextNotUsed($event);
    }

    public function test_event_goal_category_deactivated() {
        $owner = self::getDataGenerator()->create_user();
        $this->setUser($owner);
        $goal_category = generator::instance()->create_goal_category();

        /** @var perform_goal_category_entity $perform_goal */
        $perform_goal_category = new perform_goal_category_entity($goal_category->id);

        $event = goal_category_deactivated::create_from_instance($perform_goal_category);
        $event->trigger();

        $this->assertEquals(\context_system::instance()->id, $event->get_context()->id);
        $this->assertSame($perform_goal_category->id, $event->objectid);
        $this->assertSame('u', $event->crud);
        $this->assertSame($event::LEVEL_OTHER, $event->edulevel);
        $this->assertSame(perform_goal_category_entity::TABLE, $event->objecttable);
        $this->assertSame(
            "The user with id '{$owner->id}' has de-activated the goal_category with id '{$perform_goal_category->id}'",
            $event->get_description()
        );
        $this->assertEventContextNotUsed($event);
    }
}
