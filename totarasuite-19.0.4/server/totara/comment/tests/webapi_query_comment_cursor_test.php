<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package totara_comment
 */

use core_phpunit\testcase;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use totara_comment\comment as totara_comment;
use totara_comment\pagination\cursor_paginator;
use totara_webapi\phpunit\webapi_phpunit_helper;

class totara_comment_webapi_query_comment_cursor_test extends testcase {

    private const QUERY = 'totara_comment_comment_cursor';

    use webapi_phpunit_helper;

    public function test_totals(): void {
        self::setAdminUser();

        $user = self::getDataGenerator()->create_user();

        $component = 'perform_goal';
        $area = 'goal_comment';

        $now = time();
        $target_date = $now + DAYSECS;
        $goal = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'user_id' => $user->id,
                'context' => context_user::instance($user->id),
                'current_value' => 123.45,
                'description' => 'Test goal description',
                'name' => 'Test goal',
                'target_date' => $target_date,
                'target_value' => '99',
            ])
        );

        $comment1 = $this->create_comment($goal->id, $component, $area);
        $comment1_reply1 = $this->create_comment($goal->id, $component, $area, $comment1->get_id());
        $comment1_reply2 = $this->create_comment($goal->id, $component, $area, $comment1->get_id());

        $comment2 = $this->create_comment($goal->id, $component, $area);
        $comment2_reply1 = $this->create_comment($goal->id, $component, $area, $comment1->get_id());

        $other_instance_id = $goal->id + 111;
        $comment_other_instance = $this->create_comment($other_instance_id, $component, $area);
        $reply_other_instance = $this->create_comment($other_instance_id, $component, $area, $comment_other_instance->get_id());

        $args = [
            'component' => $component,
            'area' => $area,
            'instance_id' => $goal->id
        ];

        /** @var cursor_paginator $result */
        $result = $this->resolve_graphql_query(self::QUERY, $args);

        self::assertEquals(2, $result->get_total());
        self::assertEquals(5, $result->get_total_with_replies());
    }

    private function create_comment(int $instanceid, string $component, string $area, $parent_id = null): totara_comment {
        $commenting_user = self::getDataGenerator()->create_user();

        return totara_comment::create(
            $instanceid,
            uniqid('random_', true),
            $area,
            $component,
            FORMAT_MOODLE,
            $commenting_user->id,
            $parent_id
        );
    }

}