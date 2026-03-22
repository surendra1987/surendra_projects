<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\webapi\schema_objects;

class generic_option {
    /** @var string */
    public $id;

    /** @var string */
    public $label;

    /** @var bool */
    public $active = false;

    /**
     * @return string
     */
    public function get_id() : string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function get_label() : string {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function get_active() : bool {
        return $this->active;
    }
}