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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package core
 */

namespace core\hook;

use totara_core\hook\base;

/**
 * Hook for allowing plugins to modify the page header HTML
 */
class modify_header extends base {
    private string $header;

    /**
     * @param string $header
     */
    public function __construct(string $header) {
        $this->header = $header;
    }

    /**
     * @return string
     */
    public function get_header(): string {
        return $this->header;
    }

    /**
     * @param string $header
     * @return void
     */
    public function set_header(string $header): void {
        $this->header = $header;
    }
}