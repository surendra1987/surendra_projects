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
 * @author Mark Metcalfe <mark.metcalfe@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\entity\filters;

use core\orm\entity\filter\filter;
use core\orm\entity\filter\filter_factory;
use core\orm\entity\filter\greater_equal_than;
use core\orm\entity\filter\in;
use core\orm\query\builder;

/**
 * Filter factory for Seminars.
 */
class seminar_filter_factory implements filter_factory {

    private builder $builder;

    public function __construct(builder $builder) {
        $this->builder = $builder;
    }

    /**
     * @inheritDoc
     */
    public function create(string $key, $value, ?int $user_id = null): ?filter {
        return match($key) {
            'ids' => $this->seminar_ids($value),
            'since_timemodified' => $this->since_timemodified($value),
        };
    }

    /**
     * Filter by seminar IDs
     * @param int[] $seminar_ids
     * @return filter
     */
    public function seminar_ids(array $seminar_ids): filter {
        return (new in('id'))
            ->set_value($seminar_ids)
            ->set_builder($this->builder);
    }

    /**
     * Filter to seminars which have been modified since a given time.
     * @param mixed $time
     * @return filter
     */
    public function since_timemodified(mixed $time): filter {
        return (new greater_equal_than('timemodified'))
            ->set_value($time)
            ->set_builder($this->builder);
    }
}
