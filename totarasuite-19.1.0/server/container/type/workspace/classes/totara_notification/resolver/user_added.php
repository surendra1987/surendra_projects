<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totaralearning.com>
 * @package container_workspace
 * @category totara_notification
 */

namespace container_workspace\totara_notification\resolver;

use container_workspace\totara_notification\placeholder\enrolment as enrolment_placeholder;
use container_workspace\totara_notification\recipient\workspace_owner;
use lang_string;
use totara_notification\placeholder\placeholder_option;
use totara_notification\recipient\manager;
use totara_notification\recipient\subject;

/**
 * This notification covers users who were added to a workspace by someone else (such as an audience or by an owner)
 */
class user_added extends resolver {

    /**
     * @inheritDoc
     */
    public static function get_notification_available_placeholder_options(): array {
        $options = parent::get_notification_available_placeholder_options();
        $options['enrolment'] = placeholder_option::create(
            'enrolment',
            enrolment_placeholder::class,
            new lang_string('notification_workspace_enrolment_placeholder_group', 'container_workspace'),
            function (array $event_data): enrolment_placeholder {
                return enrolment_placeholder::from_workspace_id_and_user_id(
                    $event_data['workspace_id'],
                    $event_data['user_id']
                );
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
            workspace_owner::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_notification_title(): string {
        return get_string('notification_user_added_resolver_title', 'container_workspace');
    }

    /**
     * @inheritDoc
     */
    protected static function get_notification_log_string_key(): string {
        return 'notification_log_user_added';
    }
}
