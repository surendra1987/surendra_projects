<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\reopen_session_for_writing;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use totara_catalog\catalog_retrieval;
use totara_catalog\exception\filters_query_exception;
use totara_catalog\local\filter_handler;
use totara_catalog\local\query_helper;
use totara_catalog\merge_select\filter_option;
use totara_catalog\webapi\schema_objects\filter;

/**
 * Query resolver to filter options
 */
class filters extends query_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        $input = $args['input'] ?? [];
        query_helper::validate_input($input, false);
        $params = query_helper::parse_input_to_params($input);

        $catalog = new catalog_retrieval();
        $errors = [];
        if (!empty($params)) {
            query_helper::validate_params($params, $errors, $catalog);
        }

        if (count($errors) > 0) {
            throw new filters_query_exception(implode(' ', $errors));
        }

        $filter_handler = filter_handler::instance();
        if ($filter_handler->get_current_browse_filter()) {
            $result['browse_filter'] = self::process_filter($filter_handler->get_current_browse_filter(), $params);
        }

        $filters = [];
        foreach ($filter_handler->get_enabled_panel_filters() as $filter) {
            $filters[] = $filter;
        }

        foreach ($filters as $filter) {
            $processed_filter = self::process_filter($filter, $params);
            if ($processed_filter !== null) {
                $result['filters'][] = $processed_filter;
            }
        }

        // Add sort options.
        if (!empty($options = query_helper::get_valid_sort_options($catalog))) {
            $result['sort'] = new filter('orderbykey', get_string('sort_by', 'totara_catalog'), 'single', $options);
        }

        return $result;
    }

    private static function process_filter($filter, $params): filter|null {
        $filter_selector = $filter->selector;
        if ($filter_selector instanceof filter_option) {
            if (!empty($params) && isset($params[$filter->datafilter->get_alias()])) {
                return $filter_selector->get_filter_options_data();
            }

            return $filter_selector->get_filter_options_data();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new reopen_session_for_writing()
        ];
    }
}
