<?php
/*
 * This file is part of Totara Core
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @package core_ai
 */

use core_ai\entity\interaction_log;
use core_ai\userdata\interactions;
use core\collection;
use core_phpunit\testcase;
use totara_userdata\userdata\export;
use totara_userdata\userdata\target_user;

/**
 * Tests the interactions user data class.
 *
 * @group core_ai
 * @group core_ai_userdata
 */
class core_ai_userdata_interactions_test extends testcase {
    public function test_is_countable(): void {
        self::assertTrue(
            interactions::is_countable(),
            'ai interactions user data not countable'
        );
    }

    public function test_is_exportable(): void {
        self::assertTrue(
            interactions::is_exportable(),
            'ai interactions user data not exportable '
        );
    }

    public function test_is_purgeable(): void {
        self::assertTrue(
            interactions::is_purgeable(target_user::STATUS_ACTIVE),
            'ai interactions user data not purgeable for active users'
        );

        self::assertTrue(
            interactions::is_purgeable(target_user::STATUS_DELETED),
            'ai interactions user data not purgeable for deleted users'
        );

        self::assertTrue(
            interactions::is_purgeable(target_user::STATUS_SUSPENDED),
            'ai interactions user data not purgeable for suspended users'
        );
    }

    public function test_count(): void {
        $context = context_system::instance();
        [$user1, $user2, $user3] = $this->create_users(3)->all();

        self::assertEquals(
            $this->create_interactions($user1, 3)->count(),
            interactions::execute_count($user1, $context),
            'wrong ai interaction count for user1'
        );

        self::assertEquals(
            $this->create_interactions($user2, 5)->count(),
            interactions::execute_count($user2, $context),
            'wrong ai interaction count for user2'
        );

        self::assertEquals(
            $this->create_interactions($user3, 7)->count(),
            interactions::execute_count($user3, $context),
            'wrong ai interaction count for user3'
        );
    }

    public function test_export(): void {
        [$user1, $user2] = $this->create_users(2)->all();

        $user1_interactions = $this->create_interactions($user1, 3)
            ->map(interactions::as_key_value_pairs(...))
            ->all();

        $user2_interactions = $this->create_interactions($user2, 6)
            ->map(interactions::as_key_value_pairs(...))
            ->all();

        // Export user1's interactions.
        $context = context_system::instance();
        $result = interactions::execute_export($user1, $context);

        self::assertInstanceOf(
            export::class,
            $result,
            'wrong response for user1 interaction data export'
        );

        self::assertEqualsCanonicalizing(
            $user1_interactions,
            $result->data,
            'wrong exported data for user1 interaction data export'
        );

        // Export user2's interactions.
        $result = interactions::execute_export($user2, $context);

        self::assertInstanceOf(
            export::class,
            $result,
            'wrong response for user2 interaction data export'
        );

        self::assertEqualsCanonicalizing(
            $user2_interactions,
            $result->data,
            'wrong exported data for user2 interaction data export'
        );
    }

    public function test_purge(): void {
        [$user1, $user2] = $this->create_users(2)->all();
        $user1_interaction_count = $this->create_interactions($user1, 3)->count();
        $user2_interaction_count = $this->create_interactions($user2, 5)->count();

        // Remove only user1's interactions.
        self::assertEquals(
            $user1_interaction_count + $user2_interaction_count,
            interaction_log::repository()->count(),
            'wrong initial ai interactions count'
        );

        $context = context_system::instance();
        self::assertEquals(
            interactions::RESULT_STATUS_SUCCESS,
            interactions::execute_purge($user1, $context),
            'wrong response for user1 interactions purge'
        );

        self::assertEquals(
            $user2_interaction_count,
            interaction_log::repository()->count(),
            'user1 ai interactions still exist'
        );

        // Now remove user2's interactions.
        self::assertEquals(
            interactions::RESULT_STATUS_SUCCESS,
            interactions::execute_purge($user2, $context),
            'wrong response for user2 ai interactions purge'
        );

        self::assertEquals(
            0,
            interaction_log::repository()->count(),
            'user2 ai interactions still exist'
        );
    }

    /**
     * Generates ai interaction records for the given user.
     *
     * @param target_user $user user who 'interacted' with the ai.
     * @param int $count no of interaction records to create.
     *
     * @return collection<interaction_log> the interaction records.
     */
    private function create_interactions(
        target_user $user,
        int $count
    ): collection {
        $attributes = [
            'user_id' => $user->get_user_record()->id,
            'interaction' => 'test_interaction',
            'request' => 'test_request',
            'response' => 'test_response',
            'plugin' => 'test_plugin',
            'feature' => 'test_feature',
            'configuration' => 'test_config',
            'created_at' => time()
        ];

        $interaction_count = $count > 1 ? range(0, $count - 1) : [0];

        return collection::new($interaction_count)->map(
            fn(int $i): interaction_log => (new interaction_log($attributes))
                ->save()
        );
    }

    /**
     * Generates test users.
     *
     * @param int $count no of users to create.
     *
     * @return collection<target_user> created users.
     */
    private function create_users(int $count): collection {
        $generator = $this->getDataGenerator();
        $user_count = $count > 1 ? range(0, $count - 1) : [0];

        return collection::new($user_count)->map(
            fn(int $i): stdClass => new target_user($generator->create_user())
        );
    }
}