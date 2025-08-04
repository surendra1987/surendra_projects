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

namespace totara_competency\webapi\resolver\query;

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
use invalid_parameter_exception;
use stdClass;
use totara_competency\helpers\capability_helper;
use totara_competency\data_providers\perform_overview;

/**
 * Base class for competency perform overview resolvers.
 */
abstract class perform_overview_base extends query_resolver {
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

        if (!perform_overview_util::can_view_competencies_overview_for($user_id)) {
            throw new coding_exception('No permissions to get overview data for this user');
        }

        $context = context_user::instance($user_id);
        $ec->set_relevant_context($context);
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('competencies'),
            new require_advanced_feature('competency_assignment'),
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
     *         - ?state state the queried state if any
     *         - string sort_by field to sort results on.
     */
    protected static function parse(array $args): stdClass {
        $input = $args['input'] ?? null;
        if (is_null($input)) {
            throw new invalid_parameter_exception(
                'No input parameter for overview'
            );
        }

        $filters = $input['filters'];
        if (is_null($filters)) {
            throw new invalid_parameter_exception(
                'No filter parameter for overview'
            );
        }

        $uid = (int)$filters['id'] ?? null;
        if (is_null($uid)) {
            throw new invalid_parameter_exception('No user id for overview');
        }

        $days_ago = (int)$filters['period'] ?? self::DEF_DAYS_AGO;
        if ($days_ago < 1) {
            throw new invalid_parameter_exception('Invalid period for overview');
        }

        $state = empty($filters['status'])
            ? null
            : state::from_name($filters['status']);

        return (object) [
            'user' => new user($uid),
            'days_ago' => $days_ago,
            'state' => $state,
            'sort_by' => strtolower($input['sort_by'] ?? 'last_updated')
        ];
    }

    /**
     * Creates a totara_competency\data_providers\perform_overview data source
     * for the given overview state.
     *
     * @param stdClass $args the parsed values from self::parse().
     * @param state $state state to use when creating the data source.
     *
     * @return perform_overview the data source.
     */
    protected static function create_data_source(
        stdClass $args, state $state
    ): perform_overview {
        $sorting_columns = [
            'assignment' => perform_overview::SORT_ASSIGNMENT,
            'competency' => perform_overview::SORT_COMPETENCY,
            'id' => perform_overview::SORT_ID,
            'last_updated' => perform_overview::SORT_LAST_UPDATED,
            'user' => perform_overview::SORT_USER
        ];

        $sort_by = $args->sort_by;
        $sort_column = $sorting_columns[$sort_by] ?? null;
        if (!$sort_column) {
            throw new invalid_parameter_exception("unknown sort order: $sort_by");
        }

        // If the primary sorting is a field other than id, then it also has to
        // be sorted by secondary fields since there can be multiple achievements
        // for the same inputs eg user/competency or assignment/competency combos.
        if ($sort_column !== perform_overview::SORT_ID) {
            $secondary_sort_columns = [
                perform_overview::SORT_USER,
                perform_overview::SORT_COMPETENCY,
                perform_overview::SORT_ASSIGNMENT
            ];

            $sort_column = collection::new($secondary_sort_columns)->reduce(
                fn(string $cols, string $col): string =>
                    strpos($cols, $col) === false ? "$cols,$col" : $cols,
                $sort_column
            );
        }

        return perform_overview::create_by_state(
            $args->days_ago, self::DEF_FROM_YEARS_AGO, $state, $args->user
        )->set_order($sort_column, 'desc');
    }
}
