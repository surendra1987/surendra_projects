<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package totara_hierarchy
 */

namespace totara_hierarchy\entity\filters;

use core\orm\entity\filter\filter;

/**
 *  Search for hierarchy and custom field data
 */
class search extends filter {

    /**
     * @return void
     */
    public function apply() {
        global $CFG;
        require_once $CFG->dirroot.'/totara/core/searchlib.php';

        // Extract quoted strings from query.
        $keywords = totara_search_parse_keywords($this->value['search']);

        $alias = $this->builder->get_alias();
        $this->builder->left_join(
            [
                "{$this->value['shortprefix']}_type_info_data",
                'cf'
            ],
            "$alias.id",
            "cf.{$this->value['prefix']}id"
        );

        // Construct the sql that will search for the given keywords.
        $fields = [
            "$alias.fullname",
            "$alias.shortname",
            "$alias.description",
            "$alias.idnumber",
            'cf.data',
        ];

        list($sql, $params) = totara_search_get_keyword_where_clause($keywords, $fields, SQL_PARAMS_NAMED, 'search');

        $this->builder->select_raw($this->value['custom_fields'])->where_raw($sql, $params);
    }

}