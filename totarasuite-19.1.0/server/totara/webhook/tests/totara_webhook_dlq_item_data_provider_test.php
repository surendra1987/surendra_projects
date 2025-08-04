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

use core_phpunit\testcase;
use totara_webhook\testing\generator as totara_webhook_generator;

class totara_webhook_totara_webhook_dlq_item_data_provider_test extends testcase {
    public function test_sort_fields(): void {
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
            'event_name' => \core\event\user_profile_viewed::class,
        ]);

        $payload_2 = $generator->create_totara_webhook_payload([
            'webhook_id' => $webhook_2->id,
            'event_name' => \core\event\user_profile_viewed::class,
        ]);

        \totara_webhook\model\totara_webhook_dlq_item::create_from_totara_webhook_payload(
            $payload_1
        );

        \totara_webhook\model\totara_webhook_dlq_item::create_from_totara_webhook_payload(
            $payload_2
        );

        // test descending order
        $data_provider = \totara_webhook\data_provider\totara_webhook_dlq_item::create();
        $data_provider->set_order('webhook_id', 'desc');;
        $found = $data_provider->fetch();
        $this->assertCount(2, $found);
        /** @var \totara_webhook\entity\totara_webhook_dlq_item $first */
        $first = $found->first();
        $this->assertEquals($webhook_2->id, $first->webhook_id);

        // test ascending order
        $data_provider = \totara_webhook\data_provider\totara_webhook_dlq_item::create();
        $data_provider->set_order('webhook_id', 'asc');;
        $found = $data_provider->fetch();
        $this->assertCount(2, $found);
        /** @var \totara_webhook\entity\totara_webhook_dlq_item $first */
        $first = $found->first();
        $this->assertEquals($webhook_1->id, $first->webhook_id);

        // test default sort order
        $data_provider = \totara_webhook\data_provider\totara_webhook_dlq_item::create();
        $found = $data_provider->fetch();
        $this->assertCount(2, $found);
        /** @var \totara_webhook\entity\totara_webhook_dlq_item $first */
        $first = $found->first();
        $this->assertEquals($webhook_1->id, $first->webhook_id);
    }
}
