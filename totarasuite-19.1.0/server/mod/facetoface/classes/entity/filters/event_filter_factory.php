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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\entity\filters;

use core\orm\entity\filter\equal;
use core\orm\entity\filter\filter;
use core\orm\entity\filter\filter_factory;
use core\orm\entity\filter\greater_equal_than;
use core\orm\entity\filter\in;
use core\orm\query\builder;
use mod_facetoface\entity\filters\event\cancelled;
use mod_facetoface\entity\filters\event\course_id;
use mod_facetoface\entity\filters\event\events_ids;
use mod_facetoface\entity\filters\event\finishes_on_or_before;
use mod_facetoface\entity\filters\event\future;
use mod_facetoface\entity\filters\event\min_seats_available;
use mod_facetoface\entity\filters\event\not_cancelled;
use mod_facetoface\entity\filters\event\past;
use mod_facetoface\entity\filters\event\seminar_id;
use mod_facetoface\entity\filters\event\starts_after;
use mod_facetoface\entity\filters\event\status;
use mod_facetoface\entity\filters\event\tense;

defined('MOODLE_INTERNAL') || die();

/**
 * Filter factory for Seminar Events.
 */
class event_filter_factory implements filter_factory {

    private builder $builder;

    public function __construct(builder $builder) {
        $this->builder = $builder;
    }

    /**
     * @inheritDoc
     */
    public function create(string $key, $value, ?int $user_id = null): ?filter {
        return match($key) {
            'course_id' => $this->course_id($value),
            'seminar_id' => $this->seminar_id($value),
            'event_ids' => $this->event_ids($value),
            'status' => $this->status($value),
            'tense' => $this->tense($value),
            'starts_after' => $this->starts_after($value),
            'finishes_on_or_before' => $this->finishes_on_or_before($value),
            'min_seats_available' => $this->min_seats_available($value),
            'since_timemodified' => $this->since_timemodified($value),
        };
    }

    /**
     * Filter by course id
     * @param int $course_id
     * @return filter
     */
    public function course_id(int $course_id): filter {
        return (new course_id())
            ->set_value($course_id)
            ->set_builder($this->builder);
    }

    /**
     * Filter by seminar ID
     * @param int $seminar_id
     * @return filter
     */
    public function seminar_id(int $seminar_id): filter {
        return (new equal('facetoface'))
            ->set_value($seminar_id)
            ->set_builder($this->builder);
    }

    /**
     * Filter by a list of event IDs
     * @param array $ids
     * @return filter
     */
    public function event_ids(array $ids): filter {
        return (new in('id'))
            ->set_value($ids)
            ->set_builder($this->builder);
    }

    /**
     * Filter by a given status
     * @param string $status - Either ACTIVE, CANCELLED, or BOTH
     * @return filter
     */
    public function status(string $status): filter {
        return (new status())
            ->set_value($status)
            ->set_builder($this->builder);
    }

    /**
     * Filter by a given tense
     * @param string $tense - Either PAST, FUTURE, or BOTH
     * @return filter
     */
    public function tense(string $tense): filter {
        return (new tense())
            ->set_value($tense)
            ->set_builder($this->builder);
    }

    /**
     * Filter to events which start after a given time
     * @param mixed $time
     * @return filter
     */
    public function starts_after(mixed $time): filter {
        return (new starts_after())
            ->set_value($time)
            ->set_builder($this->builder);
    }

    /**
     * Filter to events which finish before or on a given time
     * @param mixed $time
     * @return filter
     */
    public function finishes_on_or_before(mixed $time): filter {
        return (new finishes_on_or_before())
            ->set_value($time)
            ->set_builder($this->builder);
    }

    /**
     * Filter to events which have a minimum amount of seats available
     * @param int $min_seats_available
     * @return filter
     */
    public function min_seats_available(int $min_seats_available): filter {
        return (new min_seats_available())
            ->set_value($min_seats_available)
            ->set_builder($this->builder);
    }

    /**
     * Filter to events which have been modified since a given time.
     * @param mixed $time
     * @return filter
     */
    public function since_timemodified(mixed $time): filter {
        return (new greater_equal_than('timemodified'))
            ->set_value($time)
            ->set_builder($this->builder);
    }
}
