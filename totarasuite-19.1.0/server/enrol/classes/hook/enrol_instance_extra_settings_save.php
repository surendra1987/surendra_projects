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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package core_enrol
 */

namespace core_enrol\hook;

use context_course;
use stdClass;

/**
 * This hook provides an option for injecting extra settings into an
 * enrolment instance.
 */
class enrol_instance_extra_settings_save extends enrol_instance_extra_settings_base {

    /**
     * The submitted information from the enrolment form to be saved
     * into this instance.
     */
    private stdClass $data;

    /**
     * Create a new enrol_instance_extra_settings hook
     *
     * @param stdClass $data
     * @param stdClass $enrolment_instance
     */
    public function __construct(stdClass $data, stdClass $enrolment_instance) {
        global $CFG;
        require_once("$CFG->libdir/enrollib.php");

        $this->data = $data;
        $this->enrolment_instance = $enrolment_instance;
        $this->plugin = enrol_get_plugin($enrolment_instance->enrol);
        $this->context = context_course::instance($enrolment_instance->courseid);
    }

    /**
     * Getter for private $data
     *
     * @returns stdClass $data
     */
    public function get_data(): stdClass {
        return $this->data;
    }

    /**
     * Setter for private $data
     *
     * @param stdClass $data
     */
    public function set_data(stdClass $data): void {
        $this->data = $data;
    }

    /**
     * Setter for private $enrolment_instance
     *
     * @param stdClass $enrolment_instance
     */
    public function set_enrolment_instance(stdClass $enrolment_instance): void {
        $this->enrolment_instance = $enrolment_instance;
    }
}
