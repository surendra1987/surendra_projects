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
 * Core draft status implementation.
 */
class not_started extends status {

    /**
     * Get the translated label for this status.
     *
     * @return string
     */
    public static function get_label(): string {
        return get_string('status_not_started', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public static function is_not_started(): bool {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function get_sort_order(): int {
        return 100;
    }
}
