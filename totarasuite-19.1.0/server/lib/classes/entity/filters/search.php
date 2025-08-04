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
 * @package core
 */

namespace core\entity\filters;

use core\orm\entity\filter\filter;
use core\orm\query\builder;

/**
 * Search filter for cohort. To use name, idnumber and description column
 *
 */
class search extends filter {

    /**
     * @return void
     */
    public function apply() {
        if (!is_string($this->value) || trim($this->value) === '') {
            return;
        }

        $search = $this->value;
        $alias = $this->builder->get_alias();

        $this->builder->where(
            function (builder $builder) use ($alias, $search) {
                $builder->where($alias . ".name", 'ILIKE', $search)
                    ->or_where($alias . ".idnumber", 'ILIKE', $search);
            }
        );
    }
}