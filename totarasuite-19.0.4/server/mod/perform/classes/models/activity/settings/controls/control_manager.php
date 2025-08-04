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

use coding_exception;

/**
 * Class control manager
 *
 * "Controls" are groups of related activity settings and options. The concept has been introduced to make dealing
 * with the various types of activity settings easier.
 *
 * @package mod_perform\models\activity
 */
class control_manager {

    private int $activity_id;

    /**
     * @param int $activity_id
     */
    public function __construct(int $activity_id) {
        $this->activity_id = $activity_id;
    }

    /**
     * @param array $control_keys
     * @return array
     * @throws coding_exception
     */
    public function get_controls(array $control_keys): array {
        $result = [];
        foreach ($control_keys as $control_key) {
            $result[$control_key] = $this->get_control($control_key);
        }
        return $result;
    }

    /**
     * @param string $control_key
     * @return array
     * @throws coding_exception
     */
    public function get_control(string $control_key): array {
        $class_name = "mod_perform\\models\\activity\\settings\\controls\\{$control_key}";
        if (!class_exists($class_name)) {
            $error_msg = get_string('activity_control_error_invalid_control_key', 'mod_perform', $control_key);
            throw new coding_exception($error_msg, $error_msg);
        }
        return (new $class_name($this->activity_id))->get();
    }
}
