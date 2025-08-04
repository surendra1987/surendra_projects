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
 * @author David Curry <david.curry@totara.com>
 * @package mobile_completedlearning
 */

namespace mobile_completedlearning\webapi\resolver\query;

use totara_core\webapi\resolver\query\my_completed_learning;
use core\webapi\execution_context;

/**
 * Class completed_learning extends totara_core_my_completed_learning query
 *
 * @package mobile_completedlearning\webapi\resolver\query
 */
class my_items extends my_completed_learning {
    /**
     * Extend the completed learning query to wrap it in mobile
     * pagination data.
     *
     * @param array $args
     * @param execution_context $ec
     * @return \stdClass
     */
    public static function resolve(array $args, execution_context $ec) {
        $pointer = $args['pointer'] ?? 0;
        $items = array_values(parent::resolve($args, $ec));
        $total = count($items);
        $page_size = PHPUNIT_TEST ? 3 : 20; // Use smaller pages for testing.
        $page_end = min($total, ($pointer + $page_size)); // Page ends at next page or total.

        // Create the page object with pagination data.
        $page = new \stdClass();
        $page->total = $total;
        $page->pointer = $page_end;

        // Limit the items to a single page worth.
        $page->items = [];
        for ($i = $pointer; $i < $page_end; $i++) {
            $page->items[] = $items[$i];
        }

        return $page;
    }
}
