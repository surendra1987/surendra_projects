<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package core_course
 */

namespace core_course\data_provider;

use core\entity\course as course_entity;
use core\entity\user;
use core\orm\query\raw_field;
use totara_core\data_provider\provider;
use totara_core\data_provider\provider_interface;
use core\orm\entity\filter\filter_factory;

class course extends provider implements provider_interface {

    // Mapping of sort field display names to physical entity _columns_.
    public const SORT_FIELDS = [
        'course_id' => 'id',
        'course_name' => 'fullname'
    ];

    /**
     * @inheritDoc
     */
    public static function create(?filter_factory $filter_factory = null): provider {
        return new static(
            course_entity::repository(),
            self::SORT_FIELDS,
            $filter_factory
        );
    }

    /**
     * Creates the course_data_provider for a specific user, so that course visibility is respected.
     *
     * Results will also be limited to legacy course records ('container_course').
     *
     * @param filter_factory|null $filter_factory
     * @param user|null $user
     * @return provider
     */
    public static function create_with_course_visibility(?filter_factory $filter_factory = null, ?user $user = null): provider {
        $user = $user ?? user::logged_in();

        if (!$user) {
            throw new \coding_exception(
                'There is no logged in user. A user must be logged in in order to check visibility permissions'
            );
        }

        [$sql_where, $sql_params] = totara_visibility_where($user->id);
        return new static(
            course_entity::repository()
                ->where_raw($sql_where, $sql_params)
                ->where('containertype', 'container_course'),
            self::SORT_FIELDS,
            $filter_factory
        );
    }

    /**
     * @inheritDoc
     */
    public static function get_type(): string {
        return 'course';
    }

    /**
     * @inheritDoc
     */
    public static function get_summary_format_select() {
        return 'summaryformat';
    }

}