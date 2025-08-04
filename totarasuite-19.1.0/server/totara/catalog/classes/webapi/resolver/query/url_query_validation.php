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
use totara_catalog\exception\url_query_validation_exception;
use totara_catalog\local\query_helper;
use totara_catalog\webapi\schema_objects\url_query_validation_result;

/**
 * Query resolver to validate url query.
 */
class url_query_validation extends query_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return url_query_validation_result
     */
    public static function resolve(array $args, execution_context $ec): url_query_validation_result {
        try {
            $input = $args['input'];
            query_helper::validate_input($input);
            $params = query_helper::parse_input_to_params($input);

            $catalog = new catalog_retrieval();
            $errors = [];
            query_helper::validate_params($params, $errors, $catalog);
            if (count($errors) > 0) {
                throw new url_query_validation_exception(implode(' ', $errors));
            }

            return $catalog->get_url_query_validate_result(query_helper::get_sort_value($params));
        } catch (url_query_validation_exception $e) {
            $result = new url_query_validation_result();
            $result->error_message = $e->getMessage();
            $result->error = true;

            return $result;
        }
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
