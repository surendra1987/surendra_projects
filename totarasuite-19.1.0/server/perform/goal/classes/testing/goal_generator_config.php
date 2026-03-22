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
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\goal_category;
use perform_goal\model\target_type\date as target_type_date;

/**
 * This class holds the configuration for creating goals and related objects.
 */
class goal_generator_config {

    /** @var \context */
    public $context;

    /** @var goal_category */
    public $goal_category;

    /** @var string */
    public $name = 'Test goal';

    /** @var int|null */
    public $owner_id;

    /** @var int|null */
    public $user_id;

    /** @var string|null */
    public $id_number = 'test-goal-id-number';

    /** @var string|null */
    public $description = "{\"type\":\"doc\",\"content\":[{\"type\":\"paragraph\",\"attrs\":{},\"content\":[{\"type\":\"text\",\"text\":\"Test goal description\"}]}]}";

    /** @var int|null */
    public $start_date;

    /** @var string|null */
    public $target_type;

    /** @var int|null */
    public $target_date;

    /** @var float|null */
    public $target_value = 80;

    /** @var float|null */
    public $current_value = 72.5;

    /** @var int|null */
    public $current_value_updated_at;

    /** @var string|null */
    public $status = 'not_started';

    /** @var int|null */
    public $status_updated_at;

    /** @var int|null */
    public $closed_at;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /** @var int */
    public $number_of_activities = 0;

    /** @var string */
    public $plugin_name;

    /** @var int */
    public $number_of_tasks = 0;

    /**
     * Use static::new(array $override_defaults) to construct an instance.
     */
    private function __construct() {
        // Placeholder
    }

    /**
     * Create a new configuration instance with a new generated goal_category.
     *
     * @param array $override_defaults
     * @return static
     */
    public static function new(array $override_defaults = []): self {
        $goal_category = generator::instance()->create_goal_category();

        return self::with_category($goal_category, $override_defaults);
    }

    /**
     * Create a new configuration instance for a specific goal_category.
     *
     * @param goal_category $goal_category
     * @param array $override_defaults
     * @return self
     * @throws coding_exception
     */
    public static function with_category(goal_category $goal_category, array $override_defaults = []): self {
        global $USER;
        $goal_config_data = new self();

        // Use system context to start.
        $goal_config_data->context =
            is_siteadmin($USER)
                ? \context_system::instance()
                : \context_user::instance($USER->id);

        // Set the goal_category.
        $goal_config_data->goal_category = $goal_category;

        // Next ID
        $next = goal_entity::repository()->count() + 1;
        $goal_config_data->name .= ' ' . $next;
        $goal_config_data->id_number .= '_' . $next;

        // Set default values for time fields.
        $now = time();
        $goal_config_data->start_date = $now;
        $goal_config_data->target_date = $now + WEEKSECS;
        $goal_config_data->current_value_updated_at = $now + 100;
        $goal_config_data->status_updated_at = $now + 100;

        // Set some defaults for required fields.
        $goal_config_data->target_type = target_type_date::get_type();
        $goal_config_data->plugin_name = 'basic';

        // Override property values when passed in.
        foreach ($override_defaults as $property => $value) {
            if (!property_exists($goal_config_data, $property)) {
                throw new coding_exception('Unknown property: ' . $property);
            }
            $goal_config_data->{$property} = $value;
        }

        return $goal_config_data;
    }
}