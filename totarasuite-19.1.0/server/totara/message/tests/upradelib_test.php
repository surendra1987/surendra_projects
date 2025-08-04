<?php
/**
 * This file is part of Totara LMS
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_message
 */

use core\orm\query\builder;
use core_phpunit\testcase;

global $CFG;

require_once("$CFG->dirroot/totara/message/db/upgradelib.php");

class totara_message_upradelib_test extends testcase {
    /**
     * @return void
     */
    public function test_execute(): void {
        $db = builder::get_db();

        $ids = $this->create_mock_notification_records(2);
        // Create two program extension message metadata records.
        foreach ($ids as $id) {
            $record = new \stdClass();
            $record->msgtype = 0;
            $record->msgstatus = 0;
            $record->icon = 'default';
            $record->notificationid = $id;
            $record->processorid = 7;
            $record->urgency = 0;
            $prog_extension = new \stdClass();
            $prog_extension->action = 'prog_extension';
            $record->onaccept = serialize($prog_extension);
            $record->onreject = serialize($prog_extension);
            $db->insert_record('message_metadata', $record);
        }

        // Create other message metadata records
        $ids = $this->create_mock_notification_records(1);
        $record = new stdClass();
        $record->msgtype = 3;
        $record->msgstatus = 0;
        $record->processorid = 7;
        $record->urgency = 0;
        $record->icon = 'default';
        $record->notificationid = reset($ids);
        $db->insert_record('message_metadata', $record);

        // Total records.
        self::assertEquals(3, $db->count_records('message_metadata'));
        self::assertEquals(0, $db->count_records('message_metadata', ['icon' => 'program-regular']));

        // Upgrade.
        totara_message_upgrade_message_type_for_program();

        // Must have 2 records.
        self::assertEquals(2, $db->count_records('message_metadata', ['icon' => 'program-regular']));

        // Must have 1 record.
        self::assertEquals(1, $db->count_records('message_metadata', ['icon' => 'default']));
        $ids = $this->create_mock_notification_records(3);

        // Create new two default message and one program message.
        foreach ($ids as $id) {
            $record = new \stdClass();
            $record->msgtype = 11;
            $record->msgstatus = 0;
            $record->icon = 'program-regular';
            $record->notificationid = $id;
            $record->processorid = 7;
            $record->urgency = 0;
            if (($id % 2) === 0) {
                $record->msgtype = 0;
                $record->icon = 'default';
            }
            $prog_extension = new \stdClass();
            $prog_extension->action = 'prog_extension';
            $record->onaccept = serialize($prog_extension);
            $record->onreject = serialize($prog_extension);
            $db->insert_record('message_metadata', $record);
        }

        self::assertEquals(3, $db->count_records('message_metadata', ['icon' => 'default']));
        self::assertEquals(3, $db->count_records('message_metadata', ['icon' => 'program-regular']));

        // Upgrade.
        totara_message_upgrade_message_type_for_program();

        self::assertEquals(1, $db->count_records('message_metadata', ['icon' => 'default']));
        self::assertEquals(5, $db->count_records('message_metadata', ['icon' => 'program-regular']));
    }

    /**
     * @param int $num
     * @param int $useridfrom
     * @param int $useridto
     * @return array
     */
    private function create_mock_notification_records(int $num = 5, int $useridfrom = 2, int $useridto = 3): array {
        global $DB;

        $ids = [];
        for ($i = 0; $i < $num; $i++) {
            $notification = new \stdClass();
            $notification->useridfrom = $useridfrom;
            $notification->useridto = $useridto;
            $notification->component = 'totara_message';
            $notification->timecreated = time();
            $ids[] = $DB->insert_record('notifications', $notification);
        }

        return $ids;
    }
}