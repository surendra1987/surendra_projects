<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\entity\user;
use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core_phpunit\testcase;
use perform_goal\model\goal;
use perform_goal\settings_helper;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\totara_comment\comment_resolver;
use perform_goal\totara_notification\constants;
use perform_goal\totara_notification\recipient\commenters;
use totara_core\extended_context;
use totara_job\job_assignment;
use totara_comment\comment;
use totara_comment\comment_helper;
use totara_notification\json_editor\node\placeholder;
use totara_notification\testing\generator as notification_generator;

abstract class perform_goal_comment_notification_testcase extends testcase {
    /**
     * Verifies the generated notification is correct.
     *
     * This assumes self::create_notification_preference() was used to create
     * the notification preferences.
     *
     * @param stdClass $actual the notification as retrieved from the test sink.
     * @param string $notification_subject_text notification subject.
     * @param user $notification_target the user to whom the notification was
     *        sent
     * @param commenter $commenter the user who made the comment.
     * @param goal $goal parent goal to comment.
     * @param comment $comment comment text.
     */
    final protected function assert_notification(
        stdClass $actual,
        string $notification_subject_text,
        user $notification_target,
        user $commenter,
        goal $goal,
        comment $comment
    ): void {
        self::assertEquals($notification_subject_text, $actual->subject);
        self::assertEquals($notification_target->id, $actual->userto->id);

        $body = $actual->fullmessage;
        self::assertStringContainsString($notification_subject_text, $body);
        self::assertStringContainsString($goal->name, $body);
        self::assertStringContainsString($commenter->firstname, $body);
        self::assertStringContainsString($comment->get_content(), $body);
    }

    /**
     * Creates a goal comment.
     *
     * @param goal $goal commented goal.
     * @param user $commenter commenter details.
     * @param string $comment actual comment.
     *
     * @return comment the created comment.
     */
    final protected function create_comment(
        goal $goal,
        user $commenter,
        string $comment
    ): comment {
        // Cannot use goal generator::create_goal_comment() here because that
        // writes the comment directly to the database; comment_helper not only
        // writes to the database but also fires the events that trigger the
        // notification process.
        return comment_helper::create_comment(
            settings_helper::get_component(),
            comment_resolver::AREA,
            $goal->id,
            $comment,
            FORMAT_PLAIN,
            null,
            $commenter->id
        );
    }

    /**
     * Replies to a goal comment.
     *
     * @param comment $parent comment to which to reply.
     * @param user $commenter commenter details.
     * @param string $comment actual reply.
     *
     * @return comment the reply.
     */
    final protected function create_comment_reply(
        comment $parent,
        user $commenter,
        string $comment
    ): comment {
        return comment_helper::create_reply(
            $parent->get_id(),
            $comment,
            null,
            FORMAT_PLAIN,
            $commenter->id
        );
    }

    /**
     * Updates a goal comment.
     *
     * @param comment $comment comment to modify.
     * @param user $commenter commenter details.
     * @param string $updated new comment.
     *
     * @return comment the updated comment.
     */
    final protected function modify_comment(
        comment $comment,
        user $commenter,
        string $updated
    ): comment {
        return comment_helper::update_content(
            $comment->get_id(),
            $updated,
            null,
            FORMAT_PLAIN,
            $commenter->id
        );
    }

    /**
     * Creates the notification event associated with creating it.
     *
     * @param user $commenter commenter details.
     * @param goal $goal commented goal goal.
     * @param null|comment $comment associated comment. If this is null, a
     *        comment is created.
     * @param string $updated new comment.
     *
     * @return mixed[] [notification data, comment] tuple.
     */
    final protected function create_notification_data(
        goal $goal,
        user $commenter,
        ?comment $comment = null
    ): array {
        $final_comment = $comment ?? $this->create_comment(
            $goal, $commenter, $commenter->username . ' comment'
        );

        $data = [
            constants::DATA_COMMENT_ID => $final_comment->get_id(),
            constants::DATA_COMMENTER_UID => $commenter->id,
            constants::DATA_GOAL_ID => $goal->id,
            constants::DATA_GOAL_SUBJECT_UID => $goal->user_id
        ];

        return [$data, $final_comment];
    }

    /**
     * Creates a notification preference.
     *
     * @param goal $goal associated goal.
     * @param string $resolver_class notification resolver class.
     * @param string $notification_subject notification subject text.
     * @param mixed[mixed[]] $placeholders placeholders in the notification body
     *        as a set of [placeholder key, label] tuples.
     * @param string[] $recipients notification recipients as a list of
     *        totara_notification\recipient\recipient class names.
     */
    final protected function create_notification_preference(
        goal $goal,
        string $resolver_class,
        string $notification_subject = 'Test notification subject',
        array $placeholders = [['comment:content_text', 'Comment Text']],
        array $recipients = [commenters::class]
    ): void {
        $this->setAdminUser();
        $body = [paragraph::create_json_node_from_text($notification_subject)];

        if ($placeholders) {
            $nodes = array_map(
                fn(array $tuple): array => placeholder::create_node_from_key_and_label(
                    ...$tuple
                ),
                $placeholders
            );

            $body[] = paragraph::create_json_node_with_content_nodes($nodes);
        }

        $preferences = [
            'body' => document_helper::json_encode_document(
                document_helper::create_document_from_content_nodes($body)
            ),
            'body_format' => FORMAT_JSON_EDITOR,
            'schedule_offset' => 0,
            'subject' => $notification_subject,
            'subject_format' => FORMAT_PLAIN,
            'recipients' => $recipients
        ];

        notification_generator::instance()->create_notification_preference(
            $resolver_class,
            extended_context::make_with_context($goal->context),
            $preferences
        );
    }

    /**
     * Creates test data.
     *
     * @return mixed[] a [goal, subject, manager] tuple.
     */
    final protected function create_personal_goal(): array {
        $this->setAdminUser();

        $generator = self::getDataGenerator();
        $subject = new user($generator->create_user());

        $manager = new user($generator->create_user());
        job_assignment::create_default(
            $subject->id,
            ['managerjaid' => job_assignment::create_default($manager->id)->id]
        );

        $goal_data = ['user_id' => $subject->id];
        $goal = goal_generator::instance()->create_goal(
            goal_generator_config::new($goal_data)
        );

        return [$goal, $subject, $manager];
    }
}
