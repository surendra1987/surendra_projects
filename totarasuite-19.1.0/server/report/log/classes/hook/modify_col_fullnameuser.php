<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package report_log
 */

namespace report_log\hook;

use \core\event\base as event_base;
use totara_core\hook\base as hook_base;

class modify_col_fullnameuser extends hook_base {

    /**
     * Event
     * @var event_base
     */
    private event_base $event;

    /**
     * Original value of the column
     * @var string|null
     */
    private ?string $original_value;

    /**
     * Value used to override the original value
     * @var string|null
     */
    private ?string $override_value;

    public function __construct(event_base $event, ?string $value) {
        $this->event = $event;
        $this->original_value = $value;
    }

    /**
     * Get event
     * @return mixed
     */
    public function get_event() {
        return $this->event;
    }

    /**
     * Get the original value
     * @return string|null
     */
    public function get_original_value(): ?string {
        return $this->original_value;
    }

    /**
     * Retrieve the override value
     * @return string|null
     */
    public function get_override_value(): ?string {
        return $this->override_value ?? null;
    }

    /**
     * Sets the override value
     * @param string $value
     * @return void
     */
    public function set_override_value(string $value): void {
        $this->override_value = $value;
    }
}