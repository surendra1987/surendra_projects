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
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package perform_goal
 */

namespace perform_goal\testing;

use coding_exception;
use perform_goal\entity\goal_activity as goal_activity_entity;

/**
 * This class holds the configuration for creating goal activities.
 */
class goal_activity_generator_config {

    /** @var int */
    public $goal_id;

    /** @var int|null */
    public $user_id;

    /** @var string */
    public $activity_type = 'Test type';

    /** @var string */
    public $activity_info = 'Test info';

    /**
     * Use static::new(array $override_defaults) to construct an instance.
     */
    private function __construct() {
        // Placeholder
    }

    /**
     * Create a new instance of this class.
     *
     * @param array $override_defaults
     * @return self
     * @throws coding_exception
     */
    public static function new(array $override_defaults = []): self {
        $goal_activity_config_data = new self();

        // Next ID
        $next = goal_activity_entity::repository()->count() + 1;
        $goal_activity_config_data->activity_info .= ' ' . $next;

        // Override property values when passed in.
        foreach ($override_defaults as $property => $value) {
            if (!property_exists($goal_activity_config_data, $property)) {
                throw new coding_exception('Unknown property: ' . $property);
            }
            $goal_activity_config_data->{$property} = $value;
        }

        return $goal_activity_config_data;
    }
}