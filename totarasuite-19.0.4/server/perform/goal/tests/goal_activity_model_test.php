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
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package perform_goal
 */

use core\entity\user as user_entity;
use core\orm\query\builder;
use core_phpunit\testcase;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_activity as goal_activity_entity;
use perform_goal\model\goal;
use perform_goal\model\goal_activity;
use perform_goal\testing\generator;

class perform_goal_goal_activity_model_test extends testcase {

    public function test_create_type_must_not_be_empty(): void {
        self::setAdminUser();

        $goal = generator::instance()->create_goal();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Goal activity must have a type.');
        goal_activity::create(
            $goal->id,
            null,
            '',
            'info text'
        );
    }

    public function test_create_type_must_not_be_too_long(): void {
        self::setAdminUser();

        $goal = generator::instance()->create_goal();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Goal activity type must not be longer than 100 characters.');
        $too_long_type = str_repeat('X', 101);
        goal_activity::create(
            $goal->id,
            null,
            $too_long_type,
            'info text'
        );
    }


    public function test_create_info_must_not_be_empty(): void {
        self::setAdminUser();

        $goal = generator::instance()->create_goal();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Goal activity must have an info string.');
        goal_activity::create(
            $goal->id,
            null,
            'type1',
            ''
        );
    }

    public function test_create_invalid_user(): void {
        self::setAdminUser();

        $goal = generator::instance()->create_goal();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid user_id: -99');
        goal_activity::create($goal->id, - 99, 'type1', 'info1');
    }

    public function test_create_invalid_goal(): void {
        self::setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid goal_id: -33');
        goal_activity::create(- 33, null, 'type1', 'info2');
    }

    public function test_create_successful(): void {
        $owner = self::getDataGenerator()->create_user();
        $goal_subject_user = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal = generator::instance()->create_goal();

        // Create one activity with user_id = null.
        $goal_activity1 = goal_activity::create(
            $goal->id,
            null,
            'type1',
            'info1'
        );

        $this->assertEquals($goal->id, $goal_activity1->goal_id);
        $this->assertNull($goal_activity1->user_id);
        $this->assertGreaterThanOrEqual($now, $goal_activity1->timestamp);
        $this->assertEquals('type1', $goal_activity1->activity_type);
        $this->assertEquals('info1', $goal_activity1->activity_info);
        $this->assertInstanceOf(goal::class, $goal_activity1->goal);
        $this->assertNull($goal_activity1->user);

        // Create one activity with user_id.
        $goal_activity2 = goal_activity::create(
            $goal->id,
            $goal_subject_user->id,
            'type2',
            'info2'
        );

        $this->assertEquals($goal->id, $goal_activity2->goal_id);
        $this->assertEquals($goal_subject_user->id, $goal_activity2->user_id);
        $this->assertGreaterThanOrEqual($now, $goal_activity2->timestamp);
        $this->assertEquals('type2', $goal_activity2->activity_type);
        $this->assertEquals('info2', $goal_activity2->activity_info);
        $this->assertInstanceOf(goal::class, $goal_activity2->goal);
        $this->assertEquals(new user_entity($goal_subject_user->id), $goal_activity2->user);
    }

    public function test_refresh(): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $goal = generator::instance()->create_goal();

        $goal_activity = goal_activity::create(
            $goal->id,
            null,
            'type1',
            'info1'
        );

        $this->assertNull($goal_activity->user_id);
        $this->assertNull($goal_activity->goal->user_id);

        builder::table(goal_entity::TABLE)->update(['user_id' => $owner->id]);
        builder::table(goal_activity_entity::TABLE)->update(['user_id' => $owner->id]);

        $goal_activity->refresh();
        $this->assertEquals($owner->id, $goal_activity->user_id);
        $this->assertEquals($owner->id, $goal_activity->goal->user_id);
    }
}