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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\entity\filters\event;

use core\orm\entity\filter\filter;
use mod_facetoface\query\event\filter_factory;

defined('MOODLE_INTERNAL') || die();

/**
 *  Filters events which are either active (not cancelled), cancelled, or both.
 *  status::$value should be the type of filter to filter by which must be one of the following:
 *  - ACTIVE
 *  - CANCELLED
 *  - BOTH
 */
class status extends filter {

    /**
     * @inheritDoc
     */
    public function apply(): void {
        match($this->value) {
            'ACTIVE' => filter_factory::event_not_cancelled($this->builder),
            'CANCELLED' => filter_factory::event_cancelled($this->builder),
            'BOTH' => null,
        };
    }

    /**
     * @param $value string The status to filter by. strtoupper() is applied to any value.
     * @inheritDoc
     */
    public function set_value($value): filter {
        return parent::set_value(strtoupper($value));
    }
}
