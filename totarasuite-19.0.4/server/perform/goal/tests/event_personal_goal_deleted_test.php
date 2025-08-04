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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\event\personal_goal_deleted;
use perform_goal\entity\goal as perform_goal_entity;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;

class perform_goal_event_personal_goal_deleted_test extends testcase {

    public function test_event_goal_deleted_no_subject_user() {

        $owner = self::getDataGenerator()->create_user();
        $this->setUser($owner);
        $goal = generator::instance()->create_goal();

        /** @var perform_goal_entity $perform_goal */
        $perform_goal = new perform_goal_entity($goal->id);

        $event = personal_goal_deleted::create_from_instance($perform_goal);
        $event->trigger();

        $this->assertEquals($perform_goal->context_id, $event->get_context()->id);
        $this->assertSame($perform_goal->id, $event->objectid);
        $this->assertSame('d', $event->crud);
        $this->assertSame($event::LEVEL_OTHER, $event->edulevel);
        $this->assertEquals($owner->id, $event->userid);
        $this->assertSame(perform_goal_entity::TABLE, $event->objecttable);
        $this->assertSame(
            "The user with id '{$perform_goal->owner_id}' has deleted a personal goal with id '{$perform_goal->id}'",
            $event->get_description()
        );
        $this->assertEventContextNotUsed($event);
    }

    public function test_event_goal_deleted_with_subject_user() {

        $owner = self::getDataGenerator()->create_user();
        $this->setUser($owner);

        $subject_user = self::getDataGenerator()->create_user();
        $goal_config = goal_generator_config::new(['user_id' => $subject_user->id]);
        $goal = generator::instance()->create_goal($goal_config);

        /** @var perform_goal_entity $perform_goal */
        $perform_goal = new perform_goal_entity($goal->id);

        $event = personal_goal_deleted::create_from_instance($perform_goal);
        $event->trigger();

        $this->assertEquals($perform_goal->context_id, $event->get_context()->id);
        $this->assertSame($perform_goal->id, $event->objectid);
        $this->assertSame('d', $event->crud);
        $this->assertSame($event::LEVEL_OTHER, $event->edulevel);
        $this->assertSame(perform_goal_entity::TABLE, $event->objecttable);
        $this->assertSame(
            "The user with id '{$perform_goal->owner_id}' has deleted a personal goal with id '{$perform_goal->id}' for the user with id '{$perform_goal->user_id}'",
            $event->get_description()
        );
        $this->assertEventContextNotUsed($event);
    }
}