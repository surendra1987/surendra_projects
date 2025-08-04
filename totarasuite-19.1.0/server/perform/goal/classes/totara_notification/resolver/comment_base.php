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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\totara_notification\resolver;

use coding_exception;
use lang_string;
use core_user\totara_notification\placeholder\user;
use core_user\totara_notification\placeholder\users;
use perform_goal\settings_helper;
use perform_goal\model\goal;
use perform_goal\totara_comment\comment_resolver;
use perform_goal\totara_notification\constants;
use perform_goal\totara_notification\recipient\commenters;
use perform_goal\totara_notification\recipient\goal_subject;
use perform_goal\totara_notification\placeholder\goal as goal_placeholder;
use totara_core\extended_context;
use totara_comment\totara_notification\placeholder\comment;
use totara_notification\placeholder\placeholder_option;
use totara_notification\resolver\abstraction\permission_resolver;
use totara_notification\resolver\notifiable_event_resolver;

abstract class comment_base extends notifiable_event_resolver implements permission_resolver {
    /**
     * @inheritDoc
     *
     * @see notifiable_event_resolver
     */
    public function get_extended_context(): extended_context {
        $goal_id = $this->event_data[constants::DATA_GOAL_ID];

        return extended_context::make_with_context(
            goal::load_by_id($goal_id)->get_context(),
            settings_helper::get_component(),
            comment_resolver::AREA,
            $goal_id
        );
    }

    /**
     * @inheritDoc
     *
     * @see notifiable_event_resolver
     */
    public static function get_notification_available_placeholder_options(): array {
        $extract = fn(string $key, array $data): int => key_exists($key, $data)
            ? $data[$key]
            : throw new coding_exception(
                "Incoming placeholder data has invalid '$key' value"
            );

        return [
            placeholder_option::create(
                'comment',
                comment::class,
                new lang_string('commenter_placeholder_group', 'totara_comment'),
                fn (array $data): comment => comment::from_id(
                    $extract(constants::DATA_COMMENT_ID, $data)
                )
            ),

            placeholder_option::create(
                'commenter',
                user::class,
                new lang_string('notification_placeholder_commenter', 'perform_goal'),
                fn (array $data): user => user::from_id(
                    $extract(constants::DATA_COMMENTER_UID, $data)
                )
            ),

            placeholder_option::create(
                'commenters',
                users::class,
                new lang_string('notification_placeholder_commenters', 'perform_goal'),
                fn (array $data): users => users::from_ids(
                    commenters::get_user_ids($data)
                )
            ),

            placeholder_option::create(
                'goal',
                goal_placeholder::class,
                new lang_string('notification_placeholder_goal', 'perform_goal'),
                fn (array $data): goal_placeholder => goal_placeholder::from_id(
                    $extract(constants::DATA_GOAL_ID, $data)
                )
            ),

            placeholder_option::create(
                'goal_subject',
                user::class,
                new lang_string('notification_placeholder_goal_subject', 'perform_goal'),
                fn (array $data): user => user::from_id(
                    $extract(constants::DATA_GOAL_SUBJECT_UID, $data)
                )
            )
        ];
    }

    /**
     * @inheritDoc
     *
     * @see notifiable_event_resolver
     */
    public static function get_notification_available_recipients(): array {
        return [
            goal_subject::class,
            commenters::class
        ];
    }

    /**
     * @inheritDoc
     *
     * @see notifiable_event_resolver
     */
    public static function get_notification_default_delivery_channels(): array {
        return ['email', 'popup'];
    }

    /**
     * @inheritDoc
     *
     * @see permission_resolver
     */
    public static function can_user_manage_notification_preferences(
        extended_context $context,
        int $user_id
    ): bool {
        return has_capability(
            'perform/goal:manage_comment_notifications',
            $context->get_context(),
            $user_id
        );
    }
}
