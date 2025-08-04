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


namespace perform_goal\data_provider\filter;

use coding_exception;
use core\orm\entity\filter\filter;

/**
 * Sets out a filter field for the query to find a goal by the context id field.
 */
class goal_context_filter extends filter {
    /**
     * @var string
     */
    protected string $goal_table_alias;

    /**
     * Instantiate a new filter that knows the perform_goal table alias.
     *
     * @param string $goal_table_alias
     */
    public function __construct(string $goal_table_alias) {
        $this->goal_table_alias = $goal_table_alias;
        parent::__construct([]);
    }

    /**
     * @inheritDoc
     */
    public function apply(): void {
        if (!is_array($this->value)) {
            throw new coding_exception('Goal context filter must be an array.');
        }

        $this->builder->where($this->goal_table_alias . '.context_id', '=', $this->value);
    }
}
