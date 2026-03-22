<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

defined('MOODLE_INTERNAL') || die();

use core\event\user_profile_viewed;
use core_phpunit\testcase;
use totara_webhook\entity\totara_webhook_dlq_item as totara_webhook_dlq_item_entity;
use totara_webhook\model\totara_webhook_dlq_item;
use totara_webhook\task\totara_webhook_dlq_purger_task;
use totara_webhook\testing\generator as totara_webhook_generator;

class totara_webhook_totara_webhook_dlq_purger_test extends testcase {

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_purge_default_dlq_after_threshold(): void {
        global $DB;
        // setup some dlq tasks
        $generator = totara_webhook_generator::instance();
        $webhook_1 = $generator->create_totara_webhook([
            'name' => 'webhook_1',
            'endpoint' => 'https://example.com/webhook_1',
        ]);
        $webhook_2 = $generator->create_totara_webhook([
            'name' => 'webhook_2',
            'endpoint' => 'https://example.com/webhook_2',
        ]);

        $payload_1 = $generator->create_totara_webhook_payload([
            'webhook_id' => $webhook_1->id,
            'event_name' => user_profile_viewed::class,
        ]);

        $payload_2 = $generator->create_totara_webhook_payload([
            'webhook_id' => $webhook_2->id,
            'event_name' => user_profile_viewed::class,
        ]);
        $dlq_1 = totara_webhook_dlq_item::create_from_totara_webhook_payload(
            $payload_1
        );
        totara_webhook_dlq_item::create_from_totara_webhook_payload(
            $payload_2
        );

        // update one of the payloads to be in scope of the purge
        $dlq_to_updated = new stdClass();
        $dlq_to_updated->id = $dlq_1->id;
        $dlq_to_updated->created_at = time() - (61 * 24 * 60 * 60);
        $DB->update_record(totara_webhook_dlq_item_entity::TABLE, $dlq_to_updated);

        $count = totara_webhook_dlq_item_entity::repository()->count();
        $this->assertEquals(2, $count);

        // run the task
        $task = new totara_webhook_dlq_purger_task();
        $task->execute();

        // check queue to make sure only applicable tasks were deleted
        $count = totara_webhook_dlq_item_entity::repository()->count();
        $this->assertEquals(1, $count);

        $found = totara_webhook_dlq_item_entity::repository()->get();
        $this->assertNotSame($found->first()->id, $dlq_1->id);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_purge_default_dlq_before_threshold(): void {
        global $DB;
        // setup some dlq tasks
        $generator = totara_webhook_generator::instance();
        $webhook_1 = $generator->create_totara_webhook([
            'name' => 'webhook_1',
            'endpoint' => 'https://example.com/webhook_1',
        ]);
        $webhook_2 = $generator->create_totara_webhook([
            'name' => 'webhook_2',
            'endpoint' => 'https://example.com/webhook_2',
        ]);

        $payload_1 = $generator->create_totara_webhook_payload([
            'webhook_id' => $webhook_1->id,
            'event_name' => user_profile_viewed::class,
        ]);

        $payload_2 = $generator->create_totara_webhook_payload([
            'webhook_id' => $webhook_2->id,
            'event_name' => user_profile_viewed::class,
        ]);
        $dlq_1 = totara_webhook_dlq_item::create_from_totara_webhook_payload(
            $payload_1
        );
        totara_webhook_dlq_item::create_from_totara_webhook_payload(
            $payload_2
        );

        // update one of the payloads to be in scope of the purge
        $dlq_to_updated = new stdClass();
        $dlq_to_updated->id = $dlq_1->id;
        $dlq_to_updated->created_at = time() - (29 * 24 * 60 * 60);
        $DB->update_record(totara_webhook_dlq_item_entity::TABLE, $dlq_to_updated);

        $count = totara_webhook_dlq_item_entity::repository()->count();
        $this->assertEquals(2, $count);

        // run the task
        $task = new totara_webhook_dlq_purger_task();
        $task->execute();

        // check queue to make sure only applicable tasks were deleted
        $count = totara_webhook_dlq_item_entity::repository()->count();
        $this->assertEquals(1, $count);

        $found = totara_webhook_dlq_item_entity::repository()->get();
        $this->assertNotSame($found->first()->id, $dlq_1->id);
    }
}
