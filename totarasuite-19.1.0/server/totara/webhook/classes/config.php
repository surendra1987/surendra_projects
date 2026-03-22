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

namespace totara_webhook;

use core\base_plugin_config;

class config extends base_plugin_config {

    const COMPONENT = 'totara_webhook';

    const QUEUE_PURGE_THRESHOLD = 'queue_purge_threshold';
    const DEAD_LETTER_PURGE_THRESHOLD = 'dead_letter_purge_threshold';

    /**
     * Use: Purging successful webhook requires from the queue
     *
     * Looks for the threshold offset from the users config
     * If not found - we default to 14 days
     * This is then subtracted from the current time to find all completed webhooks
     * from the previous N days
     *
     * @return int
     */
    public static function get_queue_purge_threshold(): int {
        $config_threshold = get_config(static::COMPONENT, static::QUEUE_PURGE_THRESHOLD);
        if (empty($config_threshold)) {
            // 14 days default
            return 14 * 24 * 60 * 60;
        }
        return $config_threshold;
    }

    /**
     * Use: Purging records from the dead letter queue
     *
     * Looks for the threshold offset from the users config
     * If not found - we default to 28 days
     * This is then subtracted from the current time to find all dead letters
     * from the previous N days
     *
     * @return int
     */
    public static function get_dead_letter_purge_threshold(): int {
        $config_dlq_threshold = get_config(static::COMPONENT, static::DEAD_LETTER_PURGE_THRESHOLD);
        if (empty($config_dlq_threshold)) {
            // 28 days default
            return 28 * 24 * 60 * 60;
        }
        return $config_dlq_threshold;
    }

    /**
     * @inheritDoc
     */
    protected static function get_component(): string {
        return static::COMPONENT;
    }
}
