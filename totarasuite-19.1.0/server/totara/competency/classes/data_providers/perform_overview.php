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

namespace totara_competency\data_providers;

use core\collection;
use core\entity\user;
use core_my\models\perform_overview\state;
use core\orm\entity\repository;
use core\orm\entity\filter\filter_factory;
use totara_competency\entity\competency_achievement_repository;
use totara_core\data_provider\provider;

/**
 * Provides data for the perform overview feature.
 *
 * Refer to totara_competency_perform_overview_repository_testcase class to see
 * how the overview states are computed at this time.
 */
class perform_overview extends provider {
    // Accepted ordering options.
    //
    // Note: you might need to apply multiple sort options because there can be
    // multiple achievements for the same user/competency combination, differing
    // by assignments or update times.
    public const SORT_ASSIGNMENT = 'assignment_id';
    public const SORT_COMPETENCY = 'competency_id';
    public const SORT_ID = 'id';
    public const SORT_LAST_UPDATED = 'last_aggregated';
    public const SORT_USER = 'user_id';

    /**
     * @inheritDoc
     *
     * This creates a provider targeting proficient achievements in the past
     * year.
     */
    public static function create(?filter_factory $filter_factory = null): self {
        return self::create_by_state(
            365, 2, state::achieved(), null, $filter_factory
        );
    }

    /**
     * Returns a provider that retrieves achievements for a specific overview
     * state.
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
    public static function create_by_state(
        int $days_ago,
        int $from_years_ago,
        state $state,
        ?user $user = null,
        ?filter_factory $filter_factory = null
    ): self {
        $final_user = $user ?: user::logged_in();
        $repository =  competency_achievement_repository::overview_for(
            $days_ago, $from_years_ago, $state, collection::new([$final_user])
        );

        return (new self($repository, $filter_factory))
            ->set_user_id($final_user->id);
    }

    /**
     * @inheritDoc
     */
    public static function get_type(): string {
        return 'competency';
    }

    /**
     * @inheritDoc
     */
    public static function get_summary_format_select() {
        // Not really relevant for this class but have to return something since
        // it is mandated in the provider base class.
        return 'id';
    }

    /**
     * Default constructor.
     *
     * DO NOT USE THIS DIRECTLY; use a virtual constructor instead. Sadly cannot
     * make this private because the constructor is public in the base class and
     * PHP7.x (unlike PHP8) cannot handle that.
     *
     * @param repository $query the query to use for the data provider.
     * @param null|filter_factory $filter_factory creates filters.
     */
    public function __construct(
        repository $query, ?filter_factory $filter_factory
    ) {
        $sorting = [
            self::SORT_ID,
            self::SORT_ASSIGNMENT,
            self::SORT_COMPETENCY,
            self::SORT_LAST_UPDATED,
            self::SORT_USER
        ];

        parent::__construct(
            $query, $sorting, $filter_factory, fn(): repository => $query
        );
    }
}