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
 * Hook for allowing plugins to modify the page footer HTML
 */
class modify_footer extends base {
    private string $footer;

    /**
     * @param string $footer
     */
    public function __construct(string $footer) {
        $this->footer = $footer;
    }

    /**
     * @return string
     */
    public function get_footer(): string {
        return $this->footer;
    }

    /**
     * @param string $footer
     * @return void
     */
    public function set_footer(string $footer): void {
        $this->footer = $footer;
    }
}