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
use totara_catalog\catalog_retrieval;
use totara_catalog\exception\items_query_exception;
use totara_catalog\webapi\schema_objects\top_items_grouped as top_items_grouped_object;
use totara_catalog\local\config;
use totara_catalog\local\query_helper;
use totara_catalog\provider_handler;
use totara_catalog\webapi\schema_objects\item;

/**
 * Query resolver to top_items_grouped query.
 */
class top_items_grouped extends query_resolver {
    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        $input = $args['input'];
        query_helper::validate_input($input);
        $params = query_helper::parse_input_to_params($input);

        $catalog = new catalog_retrieval();
        $errors = [];
        query_helper::validate_params($params, $errors, $catalog);

        if (count($errors) > 0) {
            throw new items_query_exception(implode(' ', $errors));
        }

        $object_types = [];
        if (!isset($params['objecttype'])) {
            foreach (provider_handler::instance()->get_active_providers() as $provider_class) {
                $object_types[] = $provider_class::get_object_type();
            }
        } else {
            $object_types = $params['objecttype'];
        }

        // Always compute maxcount.
        $maxcount = -1;

        // Always get just the first page of items
        $limit_from = 0;

        $perpageload = 5;
        if (isset($params['perpageload'])) {
            $perpageload = $params['perpageload'];
        }

        $types = $object_types;
        $catalog = new catalog_retrieval();
        $config = config::instance();
        $provider_handler = provider_handler::instance();

        if (is_array($types)) {
            foreach ($types as $type) {
                $page = $catalog->get_page_of_objects_by_type($type, $perpageload, $limit_from, $maxcount, query_helper::get_sort_value($params));
                $groups['groups'][] = new top_items_grouped_object($type, item::make_items($page->objects, $provider_handler, $config));
            }

            return $groups;
        }

        // Only one requested object_type.
        $page = $catalog->get_page_of_objects_by_type($types, $perpageload, $limit_from, $maxcount, query_helper::get_sort_value($params));
        $groups['groups'][] = new top_items_grouped_object($types, item::make_items($page->objects, $provider_handler, $config));

        return $groups;
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
