<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @package cohort
 */

namespace core\entity;

use core\orm\entity\filter\equal;
use core\orm\entity\filter\filter;
use core\orm\entity\filter\filter_factory;
use core\orm\entity\filter\greater_equal_than;
use core\orm\entity\filter\in;
use core\orm\entity\filter\not_in;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

class cohort_static_cohorts_filters implements filter_factory {

    /**
     * @param string $key
     * @param $value
     * @param int|null $user_id
     * @return filter|null
     */
    public function create(string $key, $value, ?int $user_id = null): ?filter {
        switch ($key) {
            case 'type':
                return $this->create_type_filter($value);
            case 'not_component':
                return $this->exclude_component_filter($value);
            case 'visible':
                return $this->create_visible_filter($value);
            case 'contextids':
                return $this->create_context_filter($value);
            case 'tag':
                return $this->create_tag_filter($value);
            case 'since_timecreated':
                return $this->create_since_timecreated_filter($value);
            case 'since_timemodified':
                return $this->create_since_timemodified_filter($value);
        }
        return null;
    }


    /**
     * @param int $value the matching value(s). Either cohort::TYPE_STATIC or
     *        cohort::TYPE_DYNAMIC.
     *
     * @return filter the filter instance.
     */
    public function create_type_filter(int $value): filter {
        // 1 means STATIC, 2 means DYNAMIC;
        if (!in_array($value, [1, 2], true)) {
            $allowed = implode(', ', [1, 2]);
            throw new moodle_exception("cohort type filter only accepts $allowed");
        }

        return (new equal('cohorttype'))
            ->set_value($value)
            ->set_entity_class(cohort::class);
    }

    /**
     * @param string $value
     * @return filter
     */
    public function exclude_component_filter(string $value): filter {
        return (new not_in('component'))
            ->set_value($value)
            ->set_entity_class(cohort::class);
    }

    /**
     * @param bool $value
     * @return filter
     */
    public function create_visible_filter(bool $value): filter {
        return (new equal('visible'))
            ->set_value($value)
            ->set_entity_class(cohort::class);
    }

    /**
     * @param $value
     * @return filter
     */
    public function create_context_filter($value): filter {
        return (new in('contextid'))
            ->set_value($value)
            ->set_entity_class(cohort::class);
    }

    /**
     * @param array $values
     * @return filter
     */
    public function create_tag_filter(array $values): filter {
        return (new in('id'))
            ->set_value($values)
            ->set_entity_class(cohort::class);
    }

    /**
     * @param int $value
     * @return filter
     */
    public function create_since_timecreated_filter(int $value): filter {
        return (new greater_equal_than('timecreated'))
            ->set_value($value)
            ->set_entity_class(cohort::class);
    }

    /**
     * @param int $value
     * @return filter
     */
    public function create_since_timemodified_filter(int $value): filter {
        return (new greater_equal_than('timemodified'))
            ->set_value($value)
            ->set_entity_class(cohort::class);
    }
}