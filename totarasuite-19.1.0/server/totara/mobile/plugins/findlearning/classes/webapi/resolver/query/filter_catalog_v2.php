<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package totara_mobile
 */

namespace mobile_findlearning\webapi\resolver\query;

use coding_exception;
use core\orm\entity\traits\json_trait;
use core\webapi\query_resolver;
use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use mobile_findlearning\catalog as mobile_catalog;

/**
 * Query mobile_findlearning_filter_catalog_v2
 */
class filter_catalog_v2 extends query_resolver {
    use json_trait;

    /**
     * Fetch the data required to resolve a catalog page.
     *
     * @param array $args
     * @param execution_context $ec
     * @return object
     */
    public static function resolve(array $args, execution_context $ec): object{
        if (!isset($args['filter_data'])) {
            throw new coding_exception('missing required arguments for query');
        }

        $filter_data = $args['filter_data'];
        $params = [];
        if (!empty($filter_data)) {
            $params = static::json_decode_to_array(trim($filter_data['filter_structure']));
        }

        return mobile_catalog::load_filtered_page_objects_v2($args['limit_from'] ?? 0, $params);
    }

    public static function get_middleware(): array {
        return [
            require_login::class
        ];
    }
}
