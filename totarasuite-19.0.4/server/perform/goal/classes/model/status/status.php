<?php

/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

namespace perform_goal\model\status;

/**
 * This base class sets out a common structure for goal status values, to be implemented by perform_goal core or goaltype
 * sub-plugins.
 */
abstract class status {

    /**
     * @var string Status code
     */
    public $id;

    /**
     * @var string Translated label string
     */
    public $label;

    public function __construct() {
        $this->id = static::get_code();
        $this->label = static::get_label();
    }

    /**
     * Get the code (usually the class name) for this status.
     *
     * @return string
     */
    public static function get_code(): string {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getShortName();
    }

    /**
     * Gets a string representation of the object.
     *
     * @return string
     */
    public function __toString(): string {
        return static::get_code();
    }

    /**
     * Get the translated label for this status.
     *
     * @return string
     */
    abstract public static function get_label(): string;

    /**
     * Is this status considered not started, meaning that there is no progress,
     * or that progress has been reverted?
     *
     * @return bool
     */
    public static function is_not_started(): bool {
        return false;
    }

    /**
     * Is this status considered in progress, meaning that progress is expected?
     *
     * @return bool
     */
    public static function is_in_progress(): bool {
        return false;
    }

    /**
     * Is this status considered an achievement, meaning that the goal target was reached,
     * or that significant progress was made?
     *
     * @return bool
     */
    public static function is_achieved(): bool {
        return false;
    }

    /**
     * Is this status considered complete or closed, meaning that there is no further progress expected?
     *
     * @return bool
     */
    public static function is_closed(): bool {
        return false;
    }

    /**
     * An integer to sort by for bringing the statuses in the desired order.
     *
     * @return int
     */
    public static function get_sort_order(): int {
        return 0;
    }
}
