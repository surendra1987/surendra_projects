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

use perform_goal\totara_notification\constants;
use perform_goal\totara_notification\recipient\goal_subject;

require_once(__DIR__.'/goal_comment_notification_testcase.php');

/**
 * @group perform_goal
 * @group perform_goal_comment
 */
class perform_goal_goal_comment_notification_recipient_goal_subject_test extends perform_goal_comment_notification_testcase {
    public function test_get_user_ids(): void {
        [$goal, $goal_subject, $manager] = $this->create_personal_goal();

        // Subject creates a comment. No uids returned because the subject is
        // one that created the comment.
        $data = $this->create_notification_data($goal, $goal_subject);
        self::assertEquals([], goal_subject::get_user_ids($data[0]));

        // Manager creates a comment. Subject is returned because subject is not
        // the current commenter.
        $data = $this->create_notification_data($goal, $manager);
        self::assertEquals(
            [$goal_subject->id], goal_subject::get_user_ids($data[0])
        );

        // Subject creates another comment. No uids returned because the subject is
        // one that created the comment
        $data = $this->create_notification_data($goal, $goal_subject);
        self::assertEquals([], goal_subject::get_user_ids($data[0]));
    }

    public function test_get_user_ids_missing_subject(): void {
        [$goal, $goal_subject, $manager] = $this->create_personal_goal();
        $comment = $this->create_comment($goal, $goal_subject, 'initial comment');

        $data = [
            constants::DATA_COMMENT_ID => $comment->get_id(),
            constants::DATA_COMMENTER_UID => $manager->id,
            constants::DATA_GOAL_ID => $goal->id
        ];

        self::expectException(coding_exception::class);
        self::expectExceptionMessage(constants::DATA_GOAL_SUBJECT_UID);
        goal_subject::get_user_ids($data);
    }
}
