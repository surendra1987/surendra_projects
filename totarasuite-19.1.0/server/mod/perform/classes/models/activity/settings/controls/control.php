<?php
/**
 * This file is part of Totara Perform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\models\activity\settings\controls;

use mod_perform\models\activity\activity;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\settings\visibility_conditions\none;

/**
 * Abstract class control
 *
 * @package mod_perform\models\activity\settings\control
 */
abstract class control {

    protected activity $activity;

    /**
     * @param int $activity_id
     */
    public function __construct(int $activity_id) {
        $this->activity = activity::load_by_id($activity_id);
    }

    /**
     * Get the array with all the data for this control.
     *
     * @return array
     */
    abstract public function get(): array;

    /**
     * Is the currently configured closure state valid depending on visibility condition.
     *
     * @return bool
     */
    protected function is_valid_closure_state(): bool {
        $settings = $this->activity->get_settings();

        if ($this->get_visibility_condition() !== none::VALUE) {
            $close_on_completion = $settings->lookup(activity_setting::CLOSE_ON_COMPLETION, false);
            $close_on_due_date = $settings->lookup(activity_setting::CLOSE_ON_DUE_DATE, false);
            if (!$close_on_completion && !$close_on_due_date) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the currently configured value for the visibility condition.
     *
     * @return int
     */
    protected function get_visibility_condition(): int {
        $settings = $this->activity->get_settings();
        return (int)$settings->lookup(activity_setting::VISIBILITY_CONDITION, none::VALUE);
    }
}
