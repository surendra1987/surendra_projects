<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package container_course
 */

namespace container_course;

use stdClass;

class non_interactive_enrolment_result {

    /**
     * @var mixed
     */
    private mixed $result;

    /**
     * @var mixed It can be stdClass or enrol_entity and model.
     */
    private mixed $enrol_instance;

    public function __construct(mixed $result, mixed $enrol_instance) {
        $this->result = $result;
        $this->enrol_instance = $enrol_instance;
    }

    /**
     * @return mixed
     */
    public function get_result(): mixed {
        return $this->result;
    }

    /**
     * @return mixed
     */
    public function get_enrol_instance(): mixed {
        return $this->enrol_instance;
    }
}