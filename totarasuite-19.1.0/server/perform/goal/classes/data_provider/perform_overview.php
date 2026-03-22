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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\data_provider;

use core\entity\user;
use core_my\models\perform_overview\state;
use core\orm\entity\repository;
use perform_goal\entity\goal_repository;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal as goal_model;

/**
 * Returns the goal models that fulfill a specific perform overview state.
 */
final class perform_overview extends goal_data_provider_base {
    // Accepted ordering options.
    //
    // Note: you might need to apply multiple sort options because there can be
    // multiple goals with the same name but differing by target dates.
    public const SORT_ID = 'id';
    public const SORT_NAME = 'name';
    public const SORT_TARGET_DATE = 'target_date';
    public const SORT_UPDATED = 'updated_at';

    /**
     * @var callable ()->repository function to create an on demand repository.
     */
    private $query;

    /**
     * Default constructor.
     *
     * @param int $days_ago indicates start of the overview period. The overview
     *        period is from $days ago in the past to today.
     * @param int $from_years_ago indicates the starting period in the far past
     *        from which to search for records. In other words records _before_
     *        this date are ignored for the overview.
     * @param state $state target overview state.
     * @param null|user $user user for whom to generate the overview. Defaults
     *        to the currently logged on user if not provided.
     */
    public function __construct(
        int $days_ago,
        int $from_years_ago,
        state $state,
        ?user $user = null
    ) {
        parent::__construct();

        // If no pagination is needed, it is ok to assign a primed repository as
        // thus: $this->repository = goal_repository::personal_goal_overview_for(
        // ...).
        //
        // However, this creates problems with pagination because pagination has
        // to use a new repository every invocation; the Totara ORM repository
        // was designed to be strictly single use only unfortunately. Which is
        // why the base class pagination code always reassigns $this->repository
        // with a plain vanilla goal repository. That makes the query get the
        // wrong items for this provider. Hence the need for self::build_query()
        // to dynamically create a primed repository everytime.
        $this->query = fn(): repository => goal_repository::personal_goal_overview_for(
            $days_ago, $from_years_ago, $state, $user ?: user::logged_in()
        );

        $this->sort_by_fields = [
            self::SORT_ID,
            self::SORT_NAME,
            self::SORT_TARGET_DATE,
            self::SORT_UPDATED
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function build_query(): void {
        $repo = $this->query;
        $this->repository = $repo();
    }

    /**
     * {@inheritDoc}
     */
    protected function validate_items(): void {
        $this->items = $this->items
            ->map_to([goal_model::class, 'load_by_entity'])
            ->filter(
                fn (goal_model $goal): bool => goal_interactor::from_goal($goal)
                    ->can_view()
            );
    }
}