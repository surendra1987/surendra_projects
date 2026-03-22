<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package container_workspace
 */
namespace container_workspace\task;

use container_workspace\member\member_request;
use container_workspace\notification\workspace_notification;
use container_workspace\output\join_request_notification;
use core\entity\user;
use core\message\message;
use core\task\adhoc_task;
use container_workspace\workspace;
use core_user;

/**
 * An adhoc tasks to notify the workspace's owner that there are new request to join to the workspace.
 */
final class notify_join_request_task extends adhoc_task {
    /**
     * @param int $member_request_id
     * @return notify_join_request_task
     */
    public static function from_member_request(int $member_request_id): notify_join_request_task {
        $task = new static();
        $task->set_custom_data(['member_request_id' => $member_request_id]);

        return $task;
    }

    /**
     * @return void
     */
    public function execute(): void {
        global $OUTPUT;

        $data = $this->get_custom_data();

        if (null === $data || !property_exists($data, 'member_request_id')) {
            throw new \coding_exception("No member request's id was added");
        }

        $member_request = member_request::from_id($data->member_request_id);
        $workspace = $member_request->get_workspace();

        $workspace_id = $workspace->get_id();

        // There could be a same user origin.
        $requester = new user($member_request->get_user_id());
        if ($workspace->is_owner($requester)) {
            // This should never happen as the workflow will not allow it - but we do extra checks here
            // to keep it safe.
            debugging(
                "The user who made a request is the same as the workspace's owner",
                DEBUG_DEVELOPER
            );

            return;
        } else if ($member_request->is_cancelled()) {
            // User had cancelled the requests prior to the point of cron being triggered.
            // Hence we will do nothing about it.
            return;
        }

        $template = join_request_notification::create($member_request);
        $rendered_content = $OUTPUT->render($template);

        $workspace->owners()
            ->filter(
                fn(user $owner): bool => !workspace_notification::is_off(
                    $workspace_id,
                    $owner->id
                )
            )
            ->map(
                function (user $owner) use ($rendered_content, $workspace_id): void {
                    // Make sure we send the notification in the language of the
                    // recipient
                    $recipient = $owner->to_record();
                    cron_setup_user($recipient);

                    $message = new message();

                    $message->subject = get_string(
                        'member_request_title',
                        'container_workspace'
                    );
                    $message->userto = $recipient;
                    $message->userfrom = core_user::get_noreply_user();
                    $message->component = workspace::get_type();
                    $message->name = 'join_request';
                    $message->fullmessageformat = FORMAT_PLAIN;
                    $message->courseid = $workspace_id;
                    $message->fullmessage = html_to_text($rendered_content);
                    $message->fullmessagehtml = $rendered_content;

                    message_send($message);
                }
            );

        $user = core_user::get_user((int)$this->get_userid());
        cron_setup_user($user);
    }
}