<?php
/**
 * This file is part of Totara TXP
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
 * @author  Kelsey Scheurich <kelsey.scheurich@totara.com>
 * @package totara_webhook
 */

namespace totara_webhook\task;

use core\task\scheduled_task;
use totara_webhook\exception\insufficient_webhook_handler_exception;
use totara_webhook\handler\totara_webhook_handler_factory;

class totara_webhook_default_webhook_queue_purger extends scheduled_task {

    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_name(): string {
        return get_string('webhook_default_queue_purger', 'totara_webhook');
    }

    /**
     * @throws insufficient_webhook_handler_exception
     */
    public function execute(): void {
        $webhook_handler = totara_webhook_handler_factory::create_instance();
        $webhook_handler->purge_queue();
    }
}
