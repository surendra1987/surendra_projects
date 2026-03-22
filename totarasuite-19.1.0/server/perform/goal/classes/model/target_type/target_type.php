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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

namespace perform_goal\model\target_type;

use coding_exception;
use perform_goal\model\goal;

/**
 * This class sets out a common structure for the 'target_type' object associated with a goal.
 */
abstract class target_type {

    /**
     * @var goal
     */
    protected goal $goal;

    /**
     * @param string $target_type
     * @return string - i.e.  the full classname.
     */
    public static function by_type(string $target_type): string {
        $class = "perform_goal\\model\\target_type\\{$target_type}";
        if (class_exists($class)) {
            return $class; // avoid issue with earlier PHP versions, i.e. "Cannot use ::class with dynamic class name".
        }

        throw new coding_exception("The '{$target_type}' target type class must exist");
    }

    /**
     * @param goal $goal
     * @return mixed
     */
    public static function for_goal(goal $goal) {
        $class = "perform_goal\\model\\target_type\\{$goal->target_type}";
        if (class_exists($class)) {
            return new $class($goal);
        }

        throw new coding_exception("The '{$goal->target_type}' target type class must exist");
    }

    /**
     * @return string
     */
    public static function get_label(): string {
        return 'not defined';
    }

    abstract public static function get_type();

    abstract public function get_progress_percent();

    abstract public function get_progress_raw();
}
