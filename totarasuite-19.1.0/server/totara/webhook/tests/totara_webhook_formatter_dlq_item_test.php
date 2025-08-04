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
use totara_webhook\formatter\totara_webhook_dlq_item;
use totara_webhook\testing\totara_webhook_dlq_item_generator;
use totara_webhook\testing\generator as totara_webhook_generator;
use totara_webhook\totara_webhook_payload;

class totara_webhook_totara_webhook_formatter_dlq_item_test extends testcase {

    public function test_totara_dlq_item_formatter(): void {
        [$webhook, $payload] = $this->create_webhook_and_payload();
        $generator = totara_webhook_dlq_item_generator::instance();
        $model = $generator->create_totara_webhook_dlq_item_from_payload($payload);
        $context = context_system::instance();
        $formatter = new totara_webhook_dlq_item($model, $context);
        $expected = userdate($model->created_at, get_string('strftimedate', 'langconfig'));
        $this->assertSame($expected, $formatter->format('time_created', \core\date_format::FORMAT_DATE));
    }

    /**
     * helper for setting up data for testing
     *
     * @return array
     */
    protected function create_webhook_and_payload(): array {
        $webhook_generator = totara_webhook_generator::instance();
        $webhook = $webhook_generator->create_totara_webhook(
            [
                'name' => 'abc',
                'endpoint' => 'https://example.com/webhook',
                'events' => [
                    user_profile_viewed::class,
                ]
            ]
        );
        $payload = new totara_webhook_payload(
            1,
            ['testing' => true],
            user_profile_viewed::class,
            $webhook->id,
            time()
        );
        return [$webhook, $payload];
    }
}
