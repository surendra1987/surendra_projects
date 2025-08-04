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

namespace totara_webhook\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use totara_webhook\helper\event_list;

class totara_webhook_events extends query_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec) {
        // get all the event classes that exist in the system
        $events = event_list::get_all_events();
        return [
            'events' => $events,
        ];
    }

    /**
     * @inheridDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
        ];
    }
}
