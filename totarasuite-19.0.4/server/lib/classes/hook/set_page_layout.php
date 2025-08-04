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

/**
 * Hook for plugins to override the page layout
 */
class set_page_layout extends \totara_core\hook\base {
    private string $page_layout;

    /**
     * @param string $page_layout - the page layout to use
     */
    public function __construct(string $page_layout) {
        $this->page_layout = $page_layout;
    }

    /**
     * @return string
     */
    public function get_page_layout(): string {
        return $this->page_layout;
    }

    /**
     * @param string $page_layout
     * @return void
     */
    public function set_page_layout(string $page_layout): void {
        $this->page_layout = $page_layout;
    }
}