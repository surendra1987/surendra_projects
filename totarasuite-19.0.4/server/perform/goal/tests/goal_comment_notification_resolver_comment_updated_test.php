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

use perform_goal\model\goal;
use perform_goal\totara_notification\constants;
use perform_goal\totara_notification\recipient\goal_subject;
use perform_goal\totara_notification\recipient\commenters;
use perform_goal\totara_notification\resolver\comment_updated;
use totara_notification\task\process_event_queue_task;
use totara_comment\comment;

require_once(__DIR__.'/goal_comment_notification_testcase.php');

/**
 * @group perform_goal
 * @group perform_goal_comment
 */
class perform_goal_goal_comment_notification_resolver_comment_updated_test extends perform_goal_comment_notification_testcase {
    /**
     * @return void
     */
    public function test_update_comment(): void {
        [$goal, $goal_subject, $manager] = $this->create_personal_goal();
        $subject_comment = $this->create_comment($goal, $goal_subject, 'comment');
        $mgr_comment = $this->create_comment($goal, $manager, 'comment');

        $notification_subject = 'Comment updated notification';
        $this->create_notification_preference(
            $goal,
            comment_updated::class,
            $notification_subject,
            [
                ['goal:name', 'Goal name'],
                ['commenter:first_name', 'Commenter'],
                ['comment:content_text', 'Comment Text']
            ],
            [goal_subject::class, commenters::class]
        );

        $sink = self::redirectMessages();
        $task = new process_event_queue_task();

        // Subject updates comment. A single notification because
        // 1. subject recipient returns nothing because subject updated the comment
        // 2. commenters recipient returns manager because he previously created
        //    a comment and manager is not the subject or the current commenter.
        $updated = $this->modify_comment(
            $subject_comment, $goal_subject, 'updated comment'
        );
        $task->execute();

        $notifications = $sink->get_messages();
        self::assertCount(1, $notifications);

        $this->assert_notification(
            $notifications[0],
            $notification_subject,
            $manager,
            $goal_subject,
            $goal,
            $updated
        );

        // Manager updates comment. A single notification because
        // 1. subject recipient returns subject because subject is not the
        //    current commenter.
        // 2. commenters recipient returns nothing because manager is the current
        //    commenter.
        $sink->clear();
        $updated = $this->modify_comment(
            $mgr_comment, $manager, 'updated comment'
        );
        $task->execute();

        $notifications = $sink->get_messages();
        self::assertCount(1, $notifications);

        $this->assert_notification(
            $notifications[0],
            $notification_subject,
            $goal_subject,
            $manager,
            $goal,
            $updated
        );

        $sink->close();
    }

    /**
     * Data provider for test_update_comment_invalid_placeholder_data().
     */
    public static function td_update_comment_invalid_placeholder_data(): array {
        return [
            'comment' => [
                'comment',
                constants::DATA_COMMENT_ID,
                [self::class, 'create_missing_comment_data']
            ],
            'commenter' => [
                'commenter',
                constants::DATA_COMMENTER_UID,
                [self::class, 'create_missing_commenter_data']
            ],
            'goal' => [
                'goal',
                constants::DATA_GOAL_ID,
                [self::class, 'create_missing_goal_data']
            ],
            'goal_subject' => [
                'goal_subject',
                constants::DATA_GOAL_SUBJECT_UID,
                [self::class, 'create_missing_goal_subject_data']
            ]
        ];
    }

    /**
     * @param goal $goal
     * @param comment $comment
     * @return array
     */
    private static function create_missing_comment_data(goal $goal, comment $comment): array {
        return [
            constants::DATA_COMMENTER_UID => $goal->user_id,
            constants::DATA_GOAL_ID => $goal->id,
            constants::DATA_GOAL_SUBJECT_UID => $goal->user_id
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
    private static function create_missing_goal_subject_data(goal $goal, comment $comment): array {
        return [
            constants::DATA_COMMENT_ID => $comment->get_id(),
            constants::DATA_COMMENTER_UID => $goal->user_id,
            constants::DATA_GOAL_ID => $goal->id
        ];
    }

    /**
     * @dataProvider td_update_comment_invalid_placeholder_data
     */
    public function test_update_comment_invalid_placeholder_data(
        string $placeholder_key,
        string $missing_data_key,
        callable $create
    ): void {
        [$goal, $goal_subject, ] = $this->create_personal_goal();
        $comment = $this->create_comment($goal, $goal_subject, 'comment');
        $data = $create($goal, $comment);

        $placeholders = comment_updated::get_notification_available_placeholder_options();
        foreach ($placeholders as $placeholder) {
            if ($placeholder->get_group_key() === $placeholder_key) {
                self::expectException(coding_exception::class);
                self::expectExceptionMessage($missing_data_key);
                $placeholder->get_placeholder_instance($data, $goal_subject->id);
            }
        }

        assertFailed("Unknown placeholder key: $placeholder_key");
    }
}
