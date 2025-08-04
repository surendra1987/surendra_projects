<?php
/**
 * This file is part of Totara Engage
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package container_workspace
 */

namespace container_workspace\totara_notification\resolver;

use lang_string;
use container_workspace\totara_notification\placeholder\role as role_placeholder;
use core_user\totara_notification\placeholder\user as user_placeholder;
use totara_notification\placeholder\placeholder_option;
use totara_notification\recipient\manager;
use totara_notification\recipient\subject;

/**
 * This notification covers users whose role assignment changed in a workspace by someone else (by an owner)
 */
class role_changed extends resolver {

    /**
     * @inheritDoc
     */
    public static function get_notification_available_placeholder_options(): array {
        $options = parent::get_notification_available_placeholder_options();
        unset($options['subject']);
        $options['subject'] = placeholder_option::create(
            'subject',
            user_placeholder::class,
            new lang_string('placeholder_group_subject', 'totara_notification'),
            static function (array $event_data): user_placeholder {
                return user_placeholder::from_id($event_data['actor_id']);
            }
        );
        $options['role'] = placeholder_option::create(
            'role',
            role_placeholder::class,
            new lang_string('notification_workspace_role_placeholder_group', 'container_workspace'),
            static function (array $event_data): role_placeholder {
                return role_placeholder::from_id($event_data['role_id']);
            }
        );
        return $options;
    }

    /**
     * @inheritDoc
     */
    public static function get_notification_available_recipients(): array {
        return [
            subject::class,
            manager::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_notification_title(): string {
        return get_string('notification_role_changed_resolver_title', 'container_workspace');
    }

    /**
     * @inheritDoc
     */
    protected static function get_notification_log_string_key(): string {
        return 'notification_log_role_changed';
    }
}
