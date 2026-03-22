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
 * @author Simon Chester <simon.chester@totara.com>
 * @package goaltype_basic
 */

namespace goaltype_basic\model;

/**
 * Represents a single goal creation option
 *
 * @package goaltype_basic\model
 */
class creation_option {
    /**
     * @param string $id Unique ID, e.g. "goaltype_basic/manual"
     * @param string $name Display name
     * @param string $description Display description
     * @param string $icon_component Path to frontend icon component
     * @param string $ui_component Path to frontend component for creation UI
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $icon_component,
        public string $ui_component
    ) {
    }
}
