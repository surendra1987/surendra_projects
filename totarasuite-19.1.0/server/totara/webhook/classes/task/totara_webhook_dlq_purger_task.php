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

namespace totara_webhook\task;

use core\task\scheduled_task;
use totara_webhook\handler\totara_webhook_handler_factory;

class totara_webhook_dlq_purger_task extends scheduled_task {

    /**
     * @inheritDoc
     */
    public function get_name() {
        return get_string('webhook_dlq_purger', 'totara_webhook');
    }

    /**
     * @inheritDoc
     */
    public function execute() {
        $handler = totara_webhook_handler_factory::create_instance();
        $handler->purge_dead_letters();
    }
}
