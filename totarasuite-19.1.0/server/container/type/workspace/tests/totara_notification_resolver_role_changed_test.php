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
use totara_notification\entity\notification_queue;
use totara_notification\entity\notifiable_event_queue;
use totara_notification\task\process_event_queue_task as notifications_task;
use container_workspace\member\member;
use container_workspace\event\user_role_changed;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @group container_workspace
 */
class container_workspace_totara_notification_resolver_role_changed_test extends container_workspace_multi_owner_testcase {

    public function test_role_changed_resolver(): void {

        builder::table(notifiable_event_queue::TABLE)->delete();
        static::assertEquals(0, builder::table(notifiable_event_queue::TABLE)->count());
        static::assertEquals(0, builder::table(notification_queue::TABLE)->count());

        static::setAdminUser();
        $message_sink = static::redirectMessages();

        $testdata = $this->create_multi_owner_workspace();

        static::setUser($testdata->workspace->owners()->first());

        $owner_role = member::get_role_for_owners();

        $other = [
            'role_id' => $owner_role->id,
            'role_name' => role_get_name($owner_role)
        ];

        $event = user_role_changed::create_from_data(
            $testdata->workspace,
            $testdata->owners->last(),
            $testdata->owners->first(),
            $other
        );
        $event->trigger();

        static::assertEquals(1, builder::table(notifiable_event_queue::TABLE)->count());
        static::assertEquals(0, builder::table(notification_queue::TABLE)->count());

        $task = new notifications_task();
        $task->execute();

        static::assertEquals(0, builder::table(notifiable_event_queue::TABLE)->count());
        static::assertEquals(0, builder::table(notification_queue::TABLE)->count());

        $messages = $message_sink->get_messages();
        // Default notifications have already been removed.
        static::assertCount(1, $messages);

        foreach ($messages as $message) {
            static::assertEquals($message->useridto, $testdata->workspace->owners()->last()->id);
            static::assertSame($message->subject, 'New role assigned in ' . $testdata->workspace->get_name());
        }
    }

    public function test_do_not_send_yourself(): void {

        builder::table(notifiable_event_queue::TABLE)->delete();
        static::assertEquals(0, builder::table(notifiable_event_queue::TABLE)->count());
        static::assertEquals(0, builder::table(notification_queue::TABLE)->count());

        static::setAdminUser();
        $message_sink = static::redirectMessages();

        $testdata = $this->create_multi_owner_workspace();

        static::setUser($testdata->workspace->owners()->first());

        $owner_role = member::get_role_for_owners();

        $other = [
            'role_id' => $owner_role->id,
            'role_name' => role_get_name($owner_role)
        ];

        $event = user_role_changed::create_from_data(
            $testdata->workspace,
            $testdata->owners->first(),
            $testdata->owners->first(),
            $other
        );
        $event->trigger();

        static::assertEquals(0, builder::table(notifiable_event_queue::TABLE)->count());
        static::assertEquals(0, builder::table(notification_queue::TABLE)->count());

        $messages = $message_sink->get_messages();
        static::assertCount(0, $messages);
    }
}
