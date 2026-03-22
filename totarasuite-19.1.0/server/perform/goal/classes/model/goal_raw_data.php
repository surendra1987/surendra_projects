<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

namespace perform_goal\model;

use context;
use perform_goal\model\status\status;

/**
 * Additional metadata associated with a goal.
 *
 * @property-read status[] $available_statuses allowable goal statuses.
 * @property-read context $context associated goal context.
 * @property-read ?string $description associated goal description.
 * @property-read int $id associated goal id.
 * @property-read ?int $start_date goal start date.
 * @property-read ?int $target_date goal target completion date.
 */
final class goal_raw_data {
    /**
     * @var goal associated goal model.
     */
    private goal $goal;

    /**
     * @var status[] available statuses.
     */
    private array $available_statuses;

    /**
     * Default constructor.
     *
     * @param goal $goal associated goal model.
     */
    public function __construct(goal $goal) {
        $this->goal = $goal;

        $this->available_statuses = array_map(
            fn(string $code): status => goal::status_from_code($code),
            array_keys(goal::get_status_choices())
        );
    }

    /**
     * Magic attribute getter
     *
     * @param string $field field whose value is to be returned.
     *
     * @return mixed the field value.
     */
    public function __get(string $field) {
        switch ($field) {
            case 'available_statuses':
                return $this->available_statuses;

            default:
                return $this->goal->$field;
        }
    }
}
