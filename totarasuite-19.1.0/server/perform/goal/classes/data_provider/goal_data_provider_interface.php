<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General  License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General  License for more details.
 *
 * You should have received a copy of the GNU General  License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

namespace perform_goal\data_provider;

use core\pagination\base_paginator;
use core\collection;
use core\orm\pagination\cursor_paginator;

/*
 * Outlines common public methods for a data_provider for the perform_goal module.
 */
interface goal_data_provider_interface {

    /**
     * Add filters for this provider.
     * @param array $filters
     */
    function add_filters(array $filters);

    /**
     * Sort the results in a specific way.
     *
     * @param array $sort
     */
    function add_sort_by(array $sort);

    /**
     * Get the queried items.
     *
     * @return collection
     */
    function get_results(): collection;

    /**
     * Returns a specific page of items as a stdClass paged result.
     *
     * @param int $page_size
     * @param int $page_requested
     * @return \stdClass
     */
    function get_offset_page(int $page_size = base_paginator::DEFAULT_ITEMS_PER_PAGE, int $page_requested = 1): \stdClass;

    /**
     * Returns next page of items from opaque cursor as a stdClass paged result.
     *
     * @param string|null $opaque_cursor
     * @param int $page_size
     * @return \stdClass
     */
    function get_page_results(string $opaque_cursor = null, int $page_size = base_paginator::DEFAULT_ITEMS_PER_PAGE): \stdClass;
}
