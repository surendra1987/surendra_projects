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
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use totara_catalog\exception\search_filter_options_exception;
use totara_catalog\local\filter_handler;
use totara_catalog\merge_select\filter_option;

/**
 * Query resolver to search_filter_options query.
 */
class search_filter_options extends query_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        $input = $args['input'];
        $search_term = \core_text::strtolower(trim($input['search_term']));
        $filter_key = $input['filter_key'];

        $filters = [];
        foreach (filter_handler::instance()->get_active_filters() as $filter) {
            $filters[$filter->key] = $filter;
        }

        if (!array_key_exists($filter_key, $filters) || $filter_key === 'catalog_fts') {
            throw new search_filter_options_exception("{$filter_key} is invalid and not active yet or unsupported search filter key.");
        }


        foreach ($filters as $key => $filter) {
            if ($filter_key !== $key) {
                continue;
            }

            $options = [];
            $filter_selector = $filter->selector;
            if ($filter_selector instanceof filter_option) {
                foreach (($filter_selector->get_filter_options_data())->get_options() as $option) {
                    if (strpos(\core_text::strtolower($option->get_label()), $search_term) !== false) {
                        $options[] = $option->get_label();
                        if (count($options) === 10) {
                            break;
                        }
                    }
                }
                break;
            }
        }

        return ['filter_options' => $options ?? []];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
        ];
    }
}