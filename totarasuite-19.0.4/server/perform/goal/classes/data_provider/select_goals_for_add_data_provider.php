<?php
/**
 * This file is part of Totara Perform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

namespace perform_goal\data_provider;

use stdClass;
use core\orm\entity\filter\in as filter_values_in;
use core\orm\entity\repository;
use perform_goal\entity\goal as goal_entity;
use perform_goal\data_provider\filter\goal_name_filter;

/**
 * Provides data for querying perform_goals (introduced 2023).
 */
class select_goals_for_add_data_provider extends goal_data_provider_base {
    /**
     * Returns a result of validated items from opaque cursor as a stdClass paged result.
     *
     * @param string|null $opaque_cursor
     * @return stdClass
     */
    final public function get_page_results_with_permissions(string $opaque_cursor = null): stdClass {
        $this->add_permission_filter();
        return $this->get_page_results($opaque_cursor);
    }

    /**
     * Sets a filter on the perform_goal repository for the id field, i.e. where id in (goal_ids...).
     * @param array $goal_ids
     * @return void
     */
    protected function filter_query_by_goals(array $goal_ids, ?repository $repository = null): void {
        $filter = (new filter_values_in('id'))
            ->set_value($goal_ids)
            ->set_entity_class(goal_entity::class);
        $repository = $repository ?? $this->repository;
        $repository->set_filter($filter);
    }
}
