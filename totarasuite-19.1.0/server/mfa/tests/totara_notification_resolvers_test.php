<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_mfa
 */

use core_phpunit\testcase;
use totara_notification\entity\notifiable_event_queue;
use totara_notification\entity\notification_queue;
use totara_notification\task\process_event_queue_task;

/**
 * @group totara_notification
 * @group core_mfa
 */
class core_mfa_totara_notification_resolvers_test extends testcase {
    /**
     * @return void
     * @throws coding_exception
     */
    public function test_mfa_instance_created_deleted(): void {
        global $DB;

        require_once __DIR__ . '/fixtures/mock_mfa_fixture.php';

        self::setAdminUser();

        // Ensure all are empty
        $DB->delete_records('notifiable_event_queue');
        self::assertEquals(0, $DB->count_records(notifiable_event_queue::TABLE));
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        $generator = self::getDataGenerator();
        $user = $generator->create_user();

        // Add a factor to the user
        $instance = \core_mfa\model\instance::create($user->id, 'unittest', 'Testing', []);

        // Redirect messages.
        $sink = self::redirectMessages();

        // Run tasks.
        $task = new process_event_queue_task();
        $task->execute();

        self::assertEquals(0, $DB->count_records(notifiable_event_queue::TABLE));
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        $messages = $sink->get_messages();
        // Only one notification was processed, because the other built-in notifications were disabled.
        self::assertCount(1, $messages);
        $message = reset($messages);

        self::assertEquals('Multi-factor authentication added to your account', $message->subject);
        self::assertEquals($user->id, $message->userto->id);

        $sink = self::redirectMessages();

        // Delete the instance
        $instance->delete();

        // Run tasks.
        $task = new process_event_queue_task();
        $task->execute();

        self::assertEquals(0, $DB->count_records(notifiable_event_queue::TABLE));
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        $messages = $sink->get_messages();
        // Only one notification was processed, because the other built-in notifications were disabled.
        self::assertCount(1, $messages);
        $message = reset($messages);

        self::assertEquals('Multi-factor authentication removed from your account', $message->subject);
        self::assertEquals($user->id, $message->userto->id);
    }
}
