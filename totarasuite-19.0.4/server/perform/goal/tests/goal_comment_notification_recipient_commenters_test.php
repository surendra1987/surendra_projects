<?php
/**
 * This file is part of Totara Perform
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\entity\user;
use perform_goal\model\goal;
use perform_goal\totara_notification\constants;
use perform_goal\totara_notification\recipient\commenters;
use totara_comment\comment;

require_once(__DIR__.'/goal_comment_notification_testcase.php');

/**
 * @group perform_goal
 * @group perform_goal_comment
 */
class perform_goal_goal_comment_notification_recipient_commenters_test extends perform_goal_comment_notification_testcase {
    /**
     * @return void
     * @throws coding_exception
     */
    public function test_get_user_ids(): void {
        [$goal, $goal_subject, $manager] = $this->create_personal_goal();

        // Subject creates a comment. No uids returned because the subject is
        // filtered out of the commenters list.
        $data = $this->create_notification_data($goal, $goal_subject);
        self::assertEquals([], commenters::get_user_ids($data[0]));

        // Manager creates a comment. Still no uids returned because both subject
        // and current commenter are filtered out of the commenters list.
        $data = $this->create_notification_data($goal, $manager);
        self::assertEquals([], commenters::get_user_ids($data[0]));

        // Admin creates a comment. Only manager is returned because:
        // 1. manager is not the current commenter
        // 2. manager is not the subject
        $admin = user::repository()->where('username', 'admin')->one();
        $data = $this->create_notification_data($goal, $admin);
        self::assertEquals([$manager->id], commenters::get_user_ids($data[0]));

        // Subject creates another comment. Only manager and admin are returned
        // because:
        // 1. they commented earlier
        // 2. they are not the current commenter
        // 3. they are not the subject.
        $data = $this->create_notification_data($goal, $goal_subject);
        self::assertEqualsCanonicalizing(
            [$manager->id, $admin->id], commenters::get_user_ids($data[0])
        );

        // Manager creates another comment. Only admin is returned because:
        // 1. admin commented earlier
        // 2. admin is not the current commenter
        // 3. admin is not the subject.
        $data = $this->create_notification_data($goal, $manager);
        self::assertEqualsCanonicalizing(
            [$admin->id], commenters::get_user_ids($data[0])
        );
    }

    /**
     * Data provider for test_get_user_ids_invalid_data().
     */
    public static function td_get_user_ids_invalid_data(): array {
        return [
            'missing commenter' => [
                constants::DATA_COMMENTER_UID,
                [self::class, 'create_missing_commenter_data']
            ],
            'missing goal' => [
                constants::DATA_GOAL_ID,
                [self::class, 'create_missing_goal_data']
            ],
            'missing subject' => [
                constants::DATA_GOAL_SUBJECT_UID,
                [self::class, 'create_missing_subject_data']
            ]
        ];
    }

    /**
     * @param goal $goal
     * @param comment $comment
     * @return array
     */
    private static function create_missing_commenter_data(goal $goal, comment $comment): array {
        return [
            constants::DATA_COMMENT_ID => $comment->get_id(),
            constants::DATA_GOAL_ID => $goal->id,
            constants::DATA_GOAL_SUBJECT_UID => $goal->user_id
        ];
    }

    /**
     * @param goal $goal
     * @param comment $comment
     * @return array
     */
    private static function create_missing_goal_data(goal $goal, comment $comment): array {
        return [
            constants::DATA_COMMENT_ID => $comment->get_id(),
            constants::DATA_COMMENTER_UID => $goal->user_id,
            constants::DATA_GOAL_SUBJECT_UID => $goal->user_id
        ];
    }

    /**
     * @param goal $goal
     * @param comment $comment
     * @return array
     */
    private static function create_missing_subject_data(goal $goal, comment $comment): array {
        return [
            constants::DATA_COMMENT_ID => $comment->get_id(),
            constants::DATA_COMMENTER_UID => $goal->user_id,
            constants::DATA_GOAL_ID => $goal->id
        ];
    }

    /**
     * @dataProvider td_get_user_ids_invalid_data
     */
    public function test_get_user_ids_invalid_data(
        string $missing_key,
        callable $create
    ): void {
        [$goal, $goal_subject, ] = $this->create_personal_goal();
        $comment = $this->create_comment($goal, $goal_subject, 'comment');
        $data = $create($goal, $comment);

        self::expectException(coding_exception::class);
        self::expectExceptionMessage($missing_key);
        commenters::get_user_ids($data);
    }
}
