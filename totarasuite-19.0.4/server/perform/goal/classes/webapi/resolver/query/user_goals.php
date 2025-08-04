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

namespace perform_goal\webapi\resolver\query;

use core\entity\user;
use core\pagination\base_paginator;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use perform_goal\constants;
use perform_goal\data_provider\user_goals as user_goals_data_provider;
use perform_goal\model\status\status_helper;

/**
 * Handles the "perform_goal_user_goals" GraphQL query.
 */
class user_goals extends query_resolver {

    private const SEARCH_FILTER_MAX_LENGTH = 100;

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $input = $args['input'] ?? [];
        $cursor = $input['pagination']['cursor'] ?? null;
        $limit = $input['pagination']['limit'] ?? base_paginator::DEFAULT_ITEMS_PER_PAGE;

        if (isset($input['filters']['user'])) {
            $user_record_reference = new user_record_reference();
            $target_user = $user_record_reference->get_record($input['filters']['user']);
        } else {
            $target_user = user::logged_in();
        }

        // We have to find out if there are any goals at all, so check the data provider without filtering.
        $data_provider = new user_goals_data_provider($target_user->id);
        $has_goals = $data_provider->get_page_results_with_permissions()->items->count() > 0;

        // Now get the results with filters and sorting.
        $data_provider = new user_goals_data_provider($target_user->id);
        $filters = self::clean_filters($input);

        $sort = self::format_sort_options($input);
        if (is_string($sort)) {
            return [
                'items' => [],
                'total' => 0,
                'next_cursor' => '',
                'has_goals' => $has_goals,
                'success' => false,
                'errors' => [
                    'code' => '',
                    'message' => $sort
                ]
            ];
        }

        $results = $data_provider
            ->add_sort_by($sort)
            ->add_filters($filters)
            ->get_page_results_with_permissions($cursor, $limit);

        return [
            'items' => $results->items,
            'total' => $results->total,
            'next_cursor' => $results->next_cursor,
            'has_goals' => $has_goals,
            'success' => true,
            'errors' => []
        ];
    }

    /**
     * Get the desired sorting into a form that can be passed to the data provider.
     * This merges the selected sort option from the query input with our pre-determined defaults.
     *
     * @param array $input
     *
     * @return array[]|string the sorting parameters or an exception message.
     */
    private static function format_sort_options(array $input): array|string {
        $default_sort = [
            [
                'column' => 'created_at',
                'direction' => 'DESC'
            ],
            [
                'column' => 'target_date',
                'direction' => 'ASC'
            ],
            [
                'column' => 'name',
                'direction' => 'ASC'
            ],
        ];

        if (!isset($input['options']['sort_by'])) {
            return $default_sort;
        }

        $sort_key = (string)$input['options']['sort_by'];
        if (!array_key_exists($sort_key, constants::EXPOSED_SORT_OPTIONS)) {
            return get_string('goals_error_sort_option_invalid', 'perform_goal', $sort_key);
        }

        $cursor = $input['pagination']['cursor'] ?? null;
        if (isset($cursor) && (
                $sort_key === 'most_complete' || $sort_key === 'least_complete'
            )
        ) {
            return get_string('goals_error_sort_option_invalid_with_cursor', 'perform_goal', $sort_key);
        }

        [$column, $direction] = constants::EXPOSED_SORT_OPTIONS[$sort_key];

        $selected_sort = [
            'column' => $column,
            'direction' => $direction,
        ];

        // Add the remaining default sort options after the selected sort option.
        return array_merge(
            [$selected_sort],
            array_filter($default_sort, static fn ($item) => $item['column'] !== $column)
        );
    }

    /**
     * Make sure filter parameters are well-formed.
     *
     * @param $input
     * @return array
     */
    private static function clean_filters($input): array {
        if (!array_key_exists('filters', $input)) {
            return [];
        }

        $filters = [];

        // Shorten search term to max length.
        if (array_key_exists('search', $input['filters'])) {
            $filters['search'] = substr($input['filters']['search'], 0, self::SEARCH_FILTER_MAX_LENGTH);
        }

        // Remove invalid status codes.
        if (array_key_exists('status', $input['filters']) && is_array($input['filters']['status'])) {
            $all_status_codes = status_helper::all_status_codes();
            $filters['status'] = array_filter(
                $input['filters']['status'],
                static fn (string $status_code) => in_array($status_code, $all_status_codes)
            );
        }

        return $filters;
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_authenticated_user(),
            new require_advanced_feature('perform_goals'),
        ];
    }
}
