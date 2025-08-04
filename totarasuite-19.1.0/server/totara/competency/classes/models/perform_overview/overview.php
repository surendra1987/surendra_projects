<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTDvs
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package totara_competency
 */

namespace totara_competency\models\perform_overview;

use core\collection;
use core\entity\user;
use core_my\models\perform_overview\state;
use totara_competency\aggregation_users_table;

/**
 * Competency perform overview data.
 */
class overview {
    /**
     * @var collection<string,collection<item>> mapping of state names to the
     *      list of items having that state.
     */
    private collection $items;

    /**
     * @var user the user whose overview this is.
     */
    private user $user;

    /**
     * @var ?int $limit_per_state counts per state when returning the result
     *      from self::get_items().
     */
    private ?int $limit_per_state;

    /**
     * Default constructor.
     *
     * @param user $user the user whose overview this is.
     * @param ?int $limit_per_state if this is a number > 0, then returns only
     *        that first N items per state when calling self::get_items().
     */
    public function __construct(user $user, ?int $limit_per_state = null) {
        $this->user = $user;
        $this->items = collection::new([]);
        $this->limit_per_state = (int)$limit_per_state;
    }

    /**
     * Appends the given overview item under the specified state.
     *
     * @param state $state overview state to register items against.
     * @param item $item the overview item to append.
     *
     * @return self this object.
     */
    public function add_by_state(state $state, item $item): self {
        $key = $state->name;
        $items_by_state = $this->items[$key] ?? collection::new([]);

        $this->items->set($items_by_state->append($item), $key);

        return $this;
    }

    /**
     * Returns the items with given state.
     *
     * @param state $state the state to look up.
     *
     * @return collection<mixed> the items.
     */
    public function get_items_by_state(state $state): collection {
        $items_by_state = $this->items[$state->name];

        switch (true) {
            case is_null($items_by_state):
                return collection::new([]);

            case $items_by_state->count() <= $this->limit_per_state:
            case $this->limit_per_state < 1:
                return $items_by_state;

            default:
                $slice = array_slice(
                    $items_by_state->all(), 0, $this->limit_per_state
                );

                return collection::new($slice);
        }
    }

    /**
     * Returns the number of items with given state.
     *
     * @param state $state the state to look up.
     *
     * @return int the count.
     */
    public function get_count_by_state(state $state): int {
        $items_by_state = $this->items->item($state->name);
        return is_null($items_by_state) ? 0 : $items_by_state->count();
    }

    /**
     * Returns the total number of items across all overview states.
     *
     * @return int the total.
     */
    public function get_total(): int {
        return $this->items->reduce(
            fn(int $total, collection $items): int => $total + $items->count(),
            0
        );
    }

    /**
     * Returns the user whose overview this is.
     *
     * @return user the user.
     */
    public function get_user(): user {
        return $this->user;
    }

    /**
     * Indicates if this user has competencies with pending changes. "Pending"
     * means that there are aggregation operations that are going to run for the
     * user; this not only includes changes to competencies already assigned to
     * the user but also to competencies that are newly assigned to the user but
     * still haven't gotten the initial achievement records via the aggregation
     * task.
     *
     * @return bool true if this user has competencies that have pending changes.
     */
    public function has_pending_changes(): bool {
        return (new aggregation_users_table())
            ->has_pending_aggregation($this->user->id);
    }

    /**
     * Sets the given overview items under the specified state. This _replaces_
     * the existing list if any.
     *
     * @param state $state overview state to register items against.
     * @param collection<item> $items the overview items with this state.
     *
     * @return self this object.
     */
    public function set_by_state(state $state, collection $items): self {
        $this->items->set($items, $state->name);
        return $this;
    }
}
