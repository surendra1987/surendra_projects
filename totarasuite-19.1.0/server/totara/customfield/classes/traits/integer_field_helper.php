<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package totara_customfield
 */

namespace totara_customfield\traits;

trait integer_field_helper {
    /**
     * Prints out the form snippet for the part of creating or
     * editing a custom field specific to the current data type
     *
     * @param object $form instance of the moodleform class
     */
    public function define_integer_form_specific(&$form): void {
        $attributes = ['size' => '6'];
        // Default data.
        $form->addElement('integer', 'defaultdata', get_string('defaultdata', static::LANG_COMPONENT), $attributes);
        $form->setType('defaultdata', PARAM_INT);
        $form->setDefault('defaultdata', '0');
        $form->addRule('defaultdata', null, 'integer', null, 'client');
        $form->addRule('defaultdata', get_string('required'), 'required', null, 'client');
        $form->addHelpButton('defaultdata', 'defaultdata', static::LANG_COMPONENT);

        // Param 1 for number type is the min value.
        $form->addElement('integer', 'param1', get_string('min_number', static::LANG_COMPONENT), $attributes);
        $form->setType('param1', PARAM_TEXT);
        $form->addRule('param1', null, 'integer', null, 'client');
        $form->addHelpButton('param1', 'min_number', static::LANG_COMPONENT);

        // Param 2 for number type is the max value.
        $form->addElement('integer', 'param2', get_string('max_number', static::LANG_COMPONENT), $attributes);
        $form->setType('param2', PARAM_TEXT);
        $form->addRule('param2', null, 'integer', null, 'client');
        $form->addHelpButton('param2', 'max_number', static::LANG_COMPONENT);

        // Param 3 for number type is the step value.
        $form->addElement('integer', 'param3', get_string('step_number', static::LANG_COMPONENT), $attributes);
        $form->setType('param3', PARAM_INT);
        $form->setDefault('param3', '1');
        $form->addRule('param3', null, 'integer', null, 'client');
        $form->addRule('param3', get_string('required'), 'required', null, 'client');
        $form->addHelpButton('param3', 'step_number', static::LANG_COMPONENT);
    }

    /**
     * Validate the data from the add/edit custom field form
     * that is specific to the current data type
     *
     * @param object $data from the add/edit custom field form
     * @return array associative array of error messages
     */
    public function define_integer_validate_specific($data): array {
        $errors = [];
        if ($data->defaultdata === '' || !is_numeric($data->defaultdata)) {
            $errors['defaultdata'] = get_string('defaultdata_error', static::LANG_COMPONENT);
        }
        if ($data->param1 !== '' && !is_numeric($data->param1)) {
            $errors['param1'] = get_string('min_number_error', static::LANG_COMPONENT);
        }
        if ($data->param2 !== '' && !is_numeric($data->param2)) {
            $errors['param2'] = get_string('max_number_error', static::LANG_COMPONENT);
        }
        if ($data->param3 === '' || !is_numeric($data->param3) || $data->param3 == 0) {
            $errors['param3'] = get_string('step_number_error', static::LANG_COMPONENT);
        }
        if ($errors) {
            return $errors;
        }
        if ($data->defaultdata !== '' && $data->param1 !== '' && (int)$data->param1 > (int)$data->defaultdata) {
            return ['defaultdata' => get_string('defaultdata_between_error', static::LANG_COMPONENT)];
        }
        if ($data->defaultdata !== '' && $data->param2 !== '' && (int)$data->param2 < (int)$data->defaultdata) {
            return ['defaultdata' => get_string('defaultdata_between_error', static::LANG_COMPONENT)];
        }
        if ($data->param1 !== '' && $data->param2 !== '' && ((int)$data->param1 > (int)$data->param2)) {
            $errors['param1'] = get_string('min_max_number_error', static::LANG_COMPONENT);
        }
        return $errors;
    }

    /**
     * Adds the custom field to the moodle form class
     *
     * @param $mform instance of the moodleform class
     */
    public function edit_integer_field_add($mform): void {
        $attributes = ['size' => 6];
        if ($this->field->param1 !== '' && $this->field->param1 !== null) {
            $attributes['min'] = (int)$this->field->param1;
        }
        if ($this->field->param2 !== '' && $this->field->param2 !== null) {
            $attributes['max'] = (int)$this->field->param2;
        }
        $attributes['step'] = 1;
        if ($this->field->param3 !== '' && $this->field->param3 !== null && $this->field->param3 != 0) {
            $attributes['step'] = (int)$this->field->param3;
        }
        // Create the form field
        $mform->addElement('integer', $this->inputname, $this->get_display_fullname(), $attributes);
        $mform->setType($this->inputname, PARAM_INT);
        $mform->addRule($this->inputname, null, 'integer', null, 'client');
    }

    /**
     * @inheritDoc
     */
    public function sync_data_preprocess($itemnew): \stdClass {
        $fieldname = $this->inputname;
        if (!isset($itemnew->{$fieldname})) {
            return $itemnew;
        }
        $itemnew->{$fieldname} = clean_param($itemnew->{$fieldname}, PARAM_INT);
        return $itemnew;
    }

    /**
     * @inheritDoc
     */
    public static function display_item_data($data, $extradata = []): ?string {
        if ($data === '' || $data === null) {
            return $data;
        }
        return (string)intval($data);
    }

    /**
     * Validate the form field from edit page
     *
     * @param object $data
     * @return string contains error message otherwise NULL
     */
    public function edit_validate_integer_field($data): array {
        $value = $data->{$this->inputname} ?? 0;
        if ($value !== '' && !is_numeric($value)) {
            return [$this->inputname => get_string('user_value_error', static::LANG_COMPONENT, $this->get_display_fullname())];
        }
        if ($value !== '' && $this->field->param1 !== '' && (int)$this->field->param1 > (int)$value) {
            $a = (object) ['min' => $this->field->param1, 'field' => $this->get_display_fullname()];
            return [$this->inputname => get_string('user_value_min_error', static::LANG_COMPONENT, $a)];
        }
        if ($value !== '' && $this->field->param2 !== '' && (int)$this->field->param2 < (int)$value) {
            $a = (object) ['max' => $this->field->param2, 'field' => $this->get_display_fullname()];
            return [$this->inputname => get_string('user_value_max_error', static::LANG_COMPONENT, $a)];
        }

        // Check if the input is a valid multiple of the step value from the
        // default value. NB: the step and default are mandatory ie they always
        // have valid numeric values, never blanks. Moreover, the default will
        // always be between the min and max values if they are defined.
        $step = $this->field->param3;
        $default_value = $this->field->defaultdata;

        $difference = $value - $default_value;
        $remainder = abs($difference % $step);

        if ($remainder !== 0) {
            $a = (object) ['step' => $step, 'default' => $default_value];
            return [$this->inputname => get_string('user_value_step_error', static::LANG_COMPONENT, $a)];
        }

        return [];
    }
}