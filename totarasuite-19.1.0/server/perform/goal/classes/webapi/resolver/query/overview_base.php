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

namespace perform_goal\webapi\resolver\query;

use coding_exception;
use context_user;
use core\entity\user;
use core\collection;
use core_my\models\perform_overview\state;
use core\webapi\execution_context;
use core\webapi\query_resolver;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_login;
use core_my\perform_overview_util;
use perform_goal\data_provider\perform_overview;
use stdClass;
use core\orm\query\order;

/**
 * Base class for perform goal overview resolvers.
 */
abstract class overview_base extends query_resolver {
    // Indicates the starting period in the far past from which to search for
    // records. In other words records _before_ this date are ignored for the
    // overview.
    public const DEF_FROM_YEARS_AGO = 2;

    // Indicates the default start of the overview period.
    public const DEF_DAYS_AGO = 14;

    /**
     * Checks the user's authorization and sets the correct context for graphql
     * execution.
     *
     * @param stdClass $args the parsed values from self::parse().
     * @param execution_context $ec graphql execution context to update.
     */
    protected static function authorize(
        stdClass $args, execution_context $ec
    ): void {
        $user_id = $args->user->id;

        if (!perform_overview_util::can_view_perform_goals_overview_for($user_id)) {
            throw new coding_exception(
                'No permissions to get overview data for this user'
            );
        }

        $context = context_user::instance($user_id);
        $ec->set_relevant_context($context);
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'),
            new require_login(),
            new require_authenticated_user()
        ];
    }

    /**
     * Parses the incoming argument list.
     *
     * @param array<string,mixed> $args arguments.
     *
     * @return stdClass the parsed values in this format:
     *         - user user - user for whom to generate the overview
     *         - int days_ago - indicating the start of the overview period
     *         - ?state state - the queried state if any
     *         - string sort_by - field to sort results on.
     */
    protected static function parse(array $args): stdClass {
        $input = $args['input'] ?? null;
        if (is_null($input)) {
            throw new coding_exception('No input parameter for overview');
        }

        $filters = $input['filters'];
        if (is_null($filters)) {
            throw new coding_exception('No filter parameter for overview');
        }

        $uid = (int)$filters['id'];
        if ($uid < 1 || user::repository()->where('id', $uid)->does_not_exist()) {
            throw new coding_exception('Invalid user id for overview');
        }

        $days_ago = (int)$filters['period'];

        $allowed_max_days_ago = array_reduce(
            perform_overview_util::get_overview_period_options(),
            fn(int $max, array $tuple): int => (int)$tuple['id'] > $max
                ? (int)$tuple['id']
                : $max,
            0
        );

        if ($days_ago < 1 || $days_ago > $allowed_max_days_ago) {
            throw new coding_exception('Invalid period for overview');
        }

        $state = empty($filters['status'])
            ? null
            : state::from_name($filters['status']);

        // Currently we will only support parsing the first 'sort' item passed in via API.
        $sort = $input['sort'][0] ?? [
            'column' => 'last_updated',
            'direction' => order::DIRECTION_ASC
        ];

        // Enforce a structure on the sort_type array.
        if (empty($sort['column'])) {
            throw new \coding_exception("Sort parameter must have a 'column' key");
        }
        if (empty($sort['direction'])) {
            $sort['direction'] = order::DIRECTION_ASC;
        }
        if (!in_array($sort['direction'], [order::DIRECTION_ASC, order::DIRECTION_DESC])) {
            throw new \coding_exception("Invalid sort direction");
        }
        $sort['column'] = strtolower($sort['column']);

        return (object) [
            'user' => new user($uid),
            'days_ago' => $days_ago,
            'state' => $state,
            'sort' => [$sort]
        ];
    }

    /**
     * Creates a perform_goal\data_providers\perform_overview data source for
     * the given overview state.
     *
     * @param stdClass $args the parsed values from self::parse().
     * @param state $state state to use when creating the data source.
     *
     * @return perform_overview the data source.
     */
    protected static function create_data_source(
        stdClass $args,
        state $state
    ): perform_overview {
        $allowed_sorting_columns = [
            'id' => perform_overview::SORT_ID,
            'name' => perform_overview::SORT_NAME,
            'target_date' => perform_overview::SORT_TARGET_DATE,
            'last_updated' => perform_overview::SORT_UPDATED
        ];

        $sort = $args->sort;
        $sort_column = $sort[0]['column'];

        $sort_column = $allowed_sorting_columns[$sort_column] ?? null;
        if (!$sort_column) {
            throw new coding_exception("unknown sort order: $sort_column");
        }
        $sort[0]['column'] = $sort_column;

        // If the primary sorting is a field other than id, then it also has to
        // be sorted by secondary fields since there can be multiple goals for
        // the same inputs eg name/updated time combos.
        $secondary_sort = [
            ['column' => perform_overview::SORT_NAME, 'direction' => 'ASC'],
            ['column' => perform_overview::SORT_TARGET_DATE, 'direction' => 'DESC'],
            ['column' => perform_overview::SORT_UPDATED, 'direction' => 'DESC']
        ];

        $final_sort = $sort_column === perform_overview::SORT_ID
            ? $sort
            : collection::new($secondary_sort)->reduce(
                function (array $sort, array $tuple) use ($sort_column): array {
                    if ($tuple['column'] !== $sort_column) {
                        $sort[] = $tuple;
                    }

                    return $sort;
                },
                $sort
            );

        $provider = new perform_overview(
            $args->days_ago, self::DEF_FROM_YEARS_AGO, $state, $args->user
        );

        return $provider->add_sort_by($final_sort);
    }
}
