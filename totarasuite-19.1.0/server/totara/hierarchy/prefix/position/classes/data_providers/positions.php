<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package hierarchy_position
 */

namespace hierarchy_position\data_providers;

use hierarchy_position\entity\position;
use totara_hierarchy\data_providers\base;
use core\pagination\cursor;
use core\orm\pagination\cursor_paginator;

defined('MOODLE_INTERNAL') || die();

/**
 * Data provider for fetching positions.
 */
class positions extends base {

    /**
     * Get hierarchy entity position class
     *
     * @return string
     */
    protected function get_entity_class(): string {
        return position::class;
    }

    /**
     * Returns a list of positions meeting the previously set search criteria.
     *
     * @param cursor|null $cursor indicates which "page" of positions to retrieve.
     * @return position[] the retrieved position entities.
     * @deprecated since Totara 19
     */
    public function fetch_paginated(?cursor $cursor = null): array {
        debugging('Method fetch_paginated() is no longer used and has been deprecated, call fetch() instead');
        $repository = position::repository()
            ->set_filters($this->filters)
            ->order_by($this->order_by, $this->order_direction);

        $pages = $cursor ? $cursor : cursor::create()->set_limit($this->page_size);
        $paginator = new cursor_paginator($repository, $pages, true);

        return $paginator->get();
    }
}