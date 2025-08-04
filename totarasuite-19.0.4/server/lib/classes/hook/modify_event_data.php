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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package core
 */

namespace core\hook;

use totara_core\hook\base;
use core\event\base as event;

class modify_event_data extends base {
    private event $event;

    /**
     * @param event $event
     */
    public function __construct(event $event) {
        $this->event = $event;
    }

    /**
     * @return array
     */
    public function get_event_data(): array {
        return $this->event->get_data();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function set_event_data(array $data): bool {
        return $this->event->set_data($data);
    }
}