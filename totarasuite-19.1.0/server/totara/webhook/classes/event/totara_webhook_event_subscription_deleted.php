<?php
/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package totara_webhook
 * @author ben fesili <ben.fesili@totara.com>
 */

namespace totara_webhook\event;

use core\event\base;
use totara_webhook\entity\totara_webhook_event_subscription as totara_webhook_event_subscription_entity;
use totara_webhook\model\totara_webhook_event_subscription;

class totara_webhook_event_subscription_deleted extends base {

    protected function init(): void {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = totara_webhook_event_subscription_entity::TABLE;
    }

    public static function create_from_totara_webhook_event_subscription(totara_webhook_event_subscription $totara_webhook_event_subscription) {
        $data = [
            'objectid' => $totara_webhook_event_subscription->get_id(),
            'other' => [
                'webhook' => $totara_webhook_event_subscription->get_webhook()->get_id(),
                'event' => $totara_webhook_event_subscription->get_event(),
            ],
            'context' => $totara_webhook_event_subscription->get_context(),
        ];

        $event = static::create($data);
        $event->add_record_snapshot(totara_webhook_event_subscription_entity::TABLE, $totara_webhook_event_subscription->to_stdClass());
        return $event;
    }

    /**
     * @inheritDoc
     */
    public static function get_name(): string {
        return get_string('event_totara_webhook_event_subscription_deleted', 'totara_webhook');
    }

    /**
     * @inheritDoc
     */
    public function get_description(): string {
        return get_string('event_totara_webhook_event_subscription_deleted_description', 'totara_webhook', [
            'id' => $this->objectid,
            'userid' => $this->userid,
        ]);
    }
}