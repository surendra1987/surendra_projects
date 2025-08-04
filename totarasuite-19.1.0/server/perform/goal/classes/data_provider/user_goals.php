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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal\data_provider;

use context;
use core\collection;
use core\entity\user;
use core\pagination\base_paginator;
use perform_goal\data_provider\filter\goal_context_filter;
use perform_goal\entity\goal as goal_entity;
use perform_goal\data_provider\filter\goal_user_filter;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal as goal_model;
use stdClass;

/**
 * Provides data for querying perform_goals.
 */
class user_goals extends goal_data_provider_base {

    /**
     * This data provider can only be instantiated for a given user_id.
     *
     * @param int $user_id
     */
    public function __construct(int $user_id) {
        parent::__construct();

        $this->add_filters(['user_id' => $user_id]);
    }

    protected function build_query(): void {
        parent::build_query();
        $this->repository
            ->with('comments')
            ->with('tasks');
    }

    /**
     * @inheritDoc
     */
    protected function process_fetched_items(): void {
        $this->validate_items();

        $this->items = $this->items->map(
            fn (goal_entity $goal_entity): goal_model => goal_model::load_by_entity($goal_entity)
        );
    }

    /**
     * @param string|null $opaque_cursor
     * @param int $page_size
     * @return stdClass
     */
    final public function get_page_results_with_permissions(
        string $opaque_cursor = null,
        int $page_size = base_paginator::DEFAULT_ITEMS_PER_PAGE
    ): stdClass {
        $this->add_permission_filter();

        return $this->get_page_results($opaque_cursor, $page_size);
    }
}
