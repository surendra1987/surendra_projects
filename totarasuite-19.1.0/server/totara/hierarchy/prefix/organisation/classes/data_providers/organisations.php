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
 * @package hierarchy_organisation
 */

namespace hierarchy_organisation\data_providers;

use hierarchy_organisation\entity\organisation;
use totara_hierarchy\data_providers\base;
use core\pagination\cursor;
use core\orm\pagination\cursor_paginator;

defined('MOODLE_INTERNAL') || die();

/**
 * Data provider for fetching organisations.
 */
class organisations extends base {
    /**
     * Get hierarchy entity organisation class
     *
     * @return string
     */
    protected function get_entity_class(): string {
        return organisation::class;
    }

    /**
     * Returns a list of organisations meeting the previously set search criteria.
     *
     * @param cursor|null $cursor indicates which "page" of organisations to retrieve.
     *
     * @return organisation[] the retrieved organisation entities.
     * @deprecated since Totara 19
     */
    public function fetch_paginated(?cursor $cursor = null): array {
        debugging('Method fetch_paginated() is no longer used and has been deprecated, call fetch() instead');
        $repository = organisation::repository()
            ->set_filters($this->filters)
            ->order_by($this->order_by, $this->order_direction);

        $pages = $cursor ? $cursor : cursor::create()->set_limit($this->page_size);
        $paginator = new cursor_paginator($repository, $pages, true);

        return $paginator->get();
    }
}