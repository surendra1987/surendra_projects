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

use core\orm\entity\filter\equal;
use core\orm\entity\filter\filter;
use core\orm\entity\filter\greater_equal_than;
use core\orm\entity\filter\in;
use core\orm\entity\filter\like;

/**
 *  Base class for hierarchy filters
 */
class base_filters {
    /**
     * Returns the appropriate filter given the query key.
     *
     * @param string $key query key.
     * @param mixed $value search value(s).
     *
     * @return filter the filter if it was found or null if it wasn't.
     */
    public static function for_key(string $key, $value): ?filter {
        switch ($key) {
            case 'framework_id':
                return self::create_framework_filter($value);

            case 'parent_id':
                return self::create_parent_id_filter($value);

            case 'ids':
                $values = is_array($value) ? $value : [$value];
                return self::create_id_filter($values);

            case 'name':
                return self::create_name_filter($value);

            case 'type_id':
                return self::create_type_filter($value);

            case 'search':
                return self::create_search_filter($value);

            case 'visible':
                return self::create_visible_filter($value);

            case 'since_timemodified':
                return self::create_sincetimemodified_filter($value);
        }

        return null;
    }

    /**
     * Returns an instance of a hierarchy entity framework id filter.
     *
     * @param int $value the matching values.
     *
     * @return filter the filter instance.
     */
    public static function create_framework_filter(int $value): filter {
        return (new equal('frameworkid'))
            ->set_value($value);
    }

    /**
     * Returns an instance of a hierarchy entity parent id filter.
     *
     * @param int $value
     *
     * @return filter
     */
    public static function create_parent_id_filter(int $value): filter {
        return (new equal('parentid'))
            ->set_value($value);
    }

    /**
     * Returns an instance of a hierarchy entity id filter.
     *
     * @param int[] $values the matching values. Note this may be an empty array
     *        in which this filter will return nothing.
     *
     * @return filter the filter instance.
     */
    public static function create_id_filter(array $values): filter {
        return (new in('id'))
            ->set_value($values);
    }

    /**
     * Returns an instance of a hierarchy entity name filter.
     *
     * Note this does like '%name%" matches.
     *
     * @param string $value the matching value(s).
     *
     * @return filter the filter instance.
     */
    public static function create_name_filter(string $value): filter {
        return (new like('fullname'))
            ->set_value($value);
    }

    /**
     * Returns an instance of a hierarchy entity type filter.
     *
     * @param int $value id of the type.
     *
     * @return filter the filter instance.
     */
    public static function create_type_filter(int $value): filter {
        return (new equal('typeid'))
            ->set_value($value);
    }

    /**
     * Returns an instance of a hierarchy entity name filter.
     *
     * Note this does like '%name%" matches.
     *
     * @param array $value the matching value(s).
     *
     * @return filter the filter instance.
     */
    public static function create_search_filter(array $value): filter {
        return (new search())
            ->set_value($value);
    }

    /**
     * Returns an instance of a hierarchy entity visible filter.
     *
     * @param bool $value of the visible.
     *
     * @return filter the filter instance.
     */
    public static function create_visible_filter(bool $value): filter {
        return (new equal('visible'))
            ->set_value($value);
    }

    public static function create_sincetimemodified_filter(string $value): filter {
        return (new greater_equal_than('timemodified'))
            ->set_value($value);
    }
}
