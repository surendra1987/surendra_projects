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
 * @author Andrew Watkins <andrew.watkins@pixelfusion.co.nz>
 * @package core_ai
 */

namespace core_ai\remote_file\response;

use core_ai\feature\response_base;

/**
 * Base class for remote file responses, this is an extension of the feature response base in
 * order to be compatible with the interaction logging system.
 *
 */
abstract class remote_file_response_base extends response_base {
    /** @var mixed Raw data from API response */
    protected $raw_data;

    /**
     * Set raw data from API response
     *
     * @param mixed $data
     * @return self
     */
    public function set_raw_data($data): self {
        $this->raw_data = $data;
        return $this;
    }

    /**
     * Get raw data from API response
     *
     * @return mixed
     */
    public function get_raw_data() {
        return $this->raw_data;
    }
}
