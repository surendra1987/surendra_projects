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

use core\orm\query\builder;
use core_phpunit\testcase;

/**
 * @group container_workspace
 */
class container_workspace_upgradelib_test extends testcase {

    public function test_upgrade_message_update_providers(): void {
        global $CFG;
        require_once($CFG->dirroot . '/container/type/workspace/db/upgradelib.php');
        $message_providers = static::get_message_providers();

        container_workspace_upgrade_message_update_providers();

        $db_message_providers = builder::table('message_providers')
        ->where('component', 'container_workspace')
        ->select(['name'])
        ->get();

        // Let's make sure the db and file message are match after removing 'transfer_ownership' message
        foreach ($message_providers as $name => $info) {
            static::assertNotEmpty($db_message_providers->find('name', $name));
        }
        static::assertEmpty($db_message_providers->find('name', 'transfer_ownership'));

        $db_config_plugins = builder::table('config_plugins')
            ->where_like('name', 'email_provider_container_workspace')
            ->select(['name'])
            ->get();

        foreach ($message_providers as $name => $info) {
            static::assertNotEmpty($db_config_plugins->find('name', 'email_provider_container_workspace_' . $name . '_permitted'));
        }
        static::assertEmpty($db_message_providers->find('name', 'email_provider_container_workspace_transfer_ownership_permitted'));
    }

    private static function get_message_providers(): array {
        return [
            // When new engage resource was added to the library of a workspace.
            'notification' => [
                'defaults' => [
                    'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'msteams' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF
                ]
            ],
            // When a user admin accepted the request to join of any user.
            'accept_member_request' => [
                'defaults' => [
                    'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'msteams' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF
                ]
            ],
            // When a new comment added to a discussion.
            'comment_on_discussion' => [
                'defaults' => [
                    'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'msteams' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF
                ]
            ],
            // When a user requested to join the workspace.
            'join_request' => [
                'defaults' => [
                    'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'msteams' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF
                ]
            ],
            // When request join a workspace was declined.
            'decline_request_join' => [
                'defaults' => [
                    'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'msteams' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF
                ]
            ],
            // When a user was added to a workspace
            'added_to_workspace' => [
                'defaults' => [
                    'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'msteams' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF
                ]
            ],
            // When bulk members got added via an audience
            'bulk_members_via_audience_added' => [
                'defaults' => [
                    'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'msteams' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                ],
            ],
            // When a new discussion posted in the workspace
            'create_new_discussion' => [
                'defaults' => [
                    'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
                    'msteams' => MESSAGE_PERMITTED
                ]
            ],
        ];
    }
}
