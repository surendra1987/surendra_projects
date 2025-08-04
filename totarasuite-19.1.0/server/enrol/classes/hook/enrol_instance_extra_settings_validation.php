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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_enrol
 */

namespace core_enrol\hook;

use context_course;
use enrol_plugin;
use stdClass;

/**
 * The purpose of this hook is to validate any extra settings that
 * have been defaulted or submitted into the core enrolment editinstance_form.
 *
 * The hook provides $data and $files, plus the enrolment instance for
 * reference.
 */
class enrol_instance_extra_settings_validation extends enrol_instance_extra_settings_base {

    /**
     * Editinstance_form data being validated.
     */
    private array $data;

    /**
     * Editinstance_form files being validated.
     */
    private array $files;

    /**
     * Internal list of validation errors
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Instantiate the hook.
     *
     * @param array $data
     * @param array $files
     * @param stdClass $enrolment_instance
     * @param enrol_plugin $plugin
     * @param context_course $context
     */
    public function __construct(array $data, array $files, stdClass $enrolment_instance, enrol_plugin $plugin, context_course $context) {
        $this->data = $data;
        $this->files = $files;
        $this->enrolment_instance = $enrolment_instance;
        $this->plugin = $plugin;
        $this->context = $context;
    }

    /**
     * Get the data to be validated.
     *
     * @return array
     */
    public function get_data(): array {
        return $this->data;
    }

    /**
     * Get the files to be validated.
     *
     * @return array
     */
    public function get_files(): array {
        return $this->files;
    }

    /**
     * Get the validation errors known to the hook
     *
     * @return array
     */
    public function get_errors(): array {
        return $this->errors;
    }

    /**
     * Inform the hook of a validation error. These will be merged with core validation errors and reported to the user,
     * and the form will not be submitted.
     *
     * @param string $key
     * @param string $error
     * @return void
     */
    public function set_error(string $key, string $error): void {
        $this->errors[$key] = $error;
    }
}