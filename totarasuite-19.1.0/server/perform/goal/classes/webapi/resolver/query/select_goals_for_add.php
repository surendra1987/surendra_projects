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

namespace perform_goal\webapi\resolver\query;

use core\entity\user;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use perform_goal\data_provider\select_goals_for_add_data_provider;
use perform_goal\model\goal;

/**
 * Handles the "perform_goal_select_goals_for_add" GraphQL query.
 */
class select_goals_for_add extends query_resolver {
    /** @var array[] */
    protected const SORT = [
        [
            'column' => 'target_date',
            'direction' => 'ASC'
        ],
        [
            'column' => 'name',
            'direction' => 'ASC'
        ],
        [
            'column' => 'id',
            'direction' => 'ASC'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $data_provider = new select_goals_for_add_data_provider();

        $input = $args['input'] ?? [];
        $cursor = $input['cursor'] ?? null;
        $filters = $input['filters'] ?? ['user_id'];
        $filters['user_id'] = ($filters['user_id'] ?? null) ?: user::logged_in()->id;

        $results = $data_provider
            ->add_sort_by(self::SORT)
            ->add_filters($filters)
            ->get_page_results_with_permissions($cursor);

        return [
            'items' => $results->items->transform_to([goal::class, 'load_by_entity']),
            'total' => $results->total,
            'next_cursor' => $results->next_cursor
        ];
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
}
