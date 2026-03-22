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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_mobile
 */

namespace totara_mobile\completion;

use stdClass;

/**
 * Class to an map activity completion request into object that can be passed as a parameter to
 * activity_completion_handler::sync_activity_completion() and other methods
 */
class sync_activity_completion_input {
    /**
     * @var stdClass
     */
    private $cm;

    /**
     * @var stdClass
     */
    private $course;

    /**
     * @var array
     */
    private array $input;

    /**
     * @param array $input
     */
    public function __construct(array $input) {
        $this->map($input);
    }

    /**
     * @param array $input
     *
     * @return void
     */
    private function map(array $input): void {
        foreach ($input as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return stdClass
     */
    public function get_cm(): stdClass {
        return $this->cm;
    }

    /**
     * @return stdClass
     */
    public function get_course(): stdClass {
        return $this->course;
    }

    /**
     * @return int
     */
    public function get_completion_tracking(): int {
        return completion_helper::convert_completion_tracking_value($this->input['completion_tracking']);
    }

    /**
     * @return int
     */
    public function get_cm_id(): int {
        return $this->input['cm_id'];
    }

    /**
     * @return bool|null
     */
    public function get_completed(): ?bool {
        return $this->input['completed'];
    }
}