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

trait decimal_field_helper {

    /**
     * Prints out the form snippet for the part of creating or
     * editing a custom field specific to the current data type
     *
     * @param object $form instance of the moodleform class
     */
    public function define_decimal_form_specific(&$form): void {
        // the 'step' will allow to play as type="decimal" when validating the data.
        $attributes = ['size' => '6', 'step' => '0.00000000001'];
        // Default data.
        $form->addElement('decimal', 'defaultdata', get_string('defaultdata', static::LANG_COMPONENT), $attributes);
        $form->setType('defaultdata', PARAM_FLOAT);
        $form->setDefault('defaultdata', format_float('0.0', self::DECIMAL_POINTS, false));
        $form->addRule('defaultdata', null, 'decimal', null, 'client');
        $form->addRule('defaultdata', get_string('required'), 'required', null, 'client');
        $form->addHelpButton('defaultdata', 'defaultdata', static::LANG_COMPONENT);

        // Param 1 for number type is the min value.
        $form->addElement('text', 'param1', get_string('min_number', static::LANG_COMPONENT), $attributes);
        $form->setType('param1', PARAM_TEXT);
        $form->addRule('param1', null, 'decimal', null, 'client');
        $form->addHelpButton('param1', 'min_number', static::LANG_COMPONENT);

        // Param 2 for number type is the max value.
        $form->addElement('text', 'param2', get_string('max_number', static::LANG_COMPONENT), $attributes);
        $form->setType('param2', PARAM_TEXT);
        $form->addRule('param2', null, 'decimal', null, 'client');
        $form->addHelpButton('param2', 'max_number', static::LANG_COMPONENT);

        // Param 3 for number type is the step value.
        $form->addElement('decimal', 'param3', get_string('step_decimal', static::LANG_COMPONENT), $attributes);
        $form->setType('param3', PARAM_FLOAT);
        $form->setDefault('param3', format_float(self::DECIMAL_STEP, self::DECIMAL_POINTS, false));
        $form->addRule('param3', null, 'decimal', null, 'client');
        $form->addRule('param3', get_string('required'), 'required', null, 'client');
        $form->addHelpButton('param3', 'step_decimal', static::LANG_COMPONENT);

        $attributes['min'] = '0';
        $attributes['step'] = '1';
        // Param 4 integer value to allow decimals into the decimal places.
        $form->addElement('integer', 'param4', get_string('decimal_points', static::LANG_COMPONENT), $attributes);
        $form->setType('param4', PARAM_INT);
        $form->setDefault('param4', self::DECIMAL_POINTS);
        $form->addRule('param4', null, 'integer', null, 'client');
        $form->addRule('param4', get_string('required'), 'required', null, 'client');
        $form->addHelpButton('param4', 'decimal_points', static::LANG_COMPONENT);
    }

    /**
     * Validate the data from the add/edit custom field form
     * that is specific to the current data type
     *
     * @param object $data from the add/edit custom field form
     * @return array associative array of error messages
     */
    public function define_decimal_validate_specific($data) {
        $errors = [];

        if ($data->param4 !== '' && !is_numeric($data->param4) || (int)$data->param4 === 0) {
            return ['param4' => get_string('decimal_points_error', static::LANG_COMPONENT)];
        }
        $decimals = $data->param4 ?: static::DECIMAL_POINTS;
        // $data->param4 is accepting a comma separator as the different countries use it.
        // unformat is required to do the validation.
        $decimals = unformat_float($decimals);

        if ($data->defaultdata === '' || !self::is_float_value($data->defaultdata)) {
            return ['defaultdata' => get_string('defaultdata_error', static::LANG_COMPONENT)];
        }

        $data->defaultdata = unformat_float($data->defaultdata);
        $data->defaultdata = format_float($data->defaultdata, $decimals, false);
        if ($data->param1 !== '' && $data->param1 !== null) {
            if (!self::is_float_value($data->param1)) {
                return ['param1' => get_string('min_number_error', static::LANG_COMPONENT)];
            }
            $data->param1 = unformat_float($data->param1);
            $data->param1 = format_float($data->param1, $decimals, false);
        }
        if ($data->param2 !== '' && $data->param2 !== null) {
            if (!self::is_float_value($data->param2)) {
                return ['param2' => get_string('max_number_error', static::LANG_COMPONENT)];
            }
            $data->param2 = unformat_float($data->param2);
            $data->param2 = format_float($data->param2, $decimals, false);
        }
        if ($data->defaultdata !== '' && $data->param1 !== '' && (float)$data->param1 > (float)$data->defaultdata) {
            return ['defaultdata' => get_string('defaultdata_between_error', static::LANG_COMPONENT)];
        }
        if ($data->defaultdata !== '' && $data->param2 !== '' && (float)$data->param2 < (float)$data->defaultdata) {
            return ['defaultdata' => get_string('defaultdata_between_error', static::LANG_COMPONENT)];
        }
        if ($data->param1 !== '' && $data->param2 !== '' && ((float)$data->param1 > (float)$data->param2)) {
            $errors['param1'] = get_string('min_max_number_error', static::LANG_COMPONENT);
        }
        if ($data->param3 === '' || !self::is_float_value($data->param3)) {
            $errors['param3'] = get_string('step_number_error', static::LANG_COMPONENT);
        }
        return $errors;
    }

    /**
     * Preprocess data from the add/edit custom field form
     * before it is saved. This method is a hook for the child
     * classes to override.
     *
     * @param object $data from the add/edit custom field form
     * @return object $data processed data object
     */
    public function define_decimal_save_preprocess($data): \stdClass {
        // Converts locale specific floating point/comma number back to standard PHP float value
        // Do NOT try to do any math operations before this conversion on any user submitted floats!
        // Requires to save to DB as the standard PHP float value
        $data->defaultdata = unformat_float($data->defaultdata);

        if ($data->param1  !== '' && $data->param1 !== null) {
            $data->param1 = unformat_float($data->param1);
        }
        if ($data->param2 !== '' && $data->param2 !== null) {
            $data->param2 = unformat_float($data->param2);
        }

        $data->param3 = unformat_float($data->param3);

        $data->param4 = (int)$data->param4 ?: static::DECIMAL_POINTS;

        return $data;
    }

    /**
     * Adds the custom field to the moodle form class
     *
     * @param $mform instance of the moodleform class
     */
    public function edit_decimal_field_add(&$mform): void {
        $attributes = ['size' => 6];
        $decimals = static::DECIMAL_POINTS;
        if ($this->field->param4) {
            $decimals = (int) $this->field->param4;
        }
        if ($this->field->param1 !== '' && $this->field->param1 !== null) {
            $attributes['min'] = format_float($this->field->param1, $decimals, false);
        }
        if ($this->field->param2 !== '' && $this->field->param2 !== null) {
            $attributes['max'] = format_float($this->field->param2, $decimals, false);
        }
        $step = $this->field->param3 ?? static::DECIMAL_STEP;
        $attributes['step'] = format_float($step, $decimals, false);
        // Create the form field
        $mform->addElement('decimal', $this->inputname, $this->get_display_fullname(), $attributes);
        $mform->setType($this->inputname, PARAM_FLOAT);
        $mform->addRule($this->inputname, null, 'decimal', null, 'client');
    }

    /**
     * @inheritDoc
     */
    function edit_decimal_field_set_default(&$mform) {
        $value = $this->format_data($this->field->defaultdata);
        $mform->setDefault($this->inputname, $value);
    }

    /**
     * @inheritDoc
     */
    public function edit_load_item_deciaml_data(&$item) {
        $value = $this->format_data($this->data);
        $item->{$this->inputname} = $value;
    }

    /**
     * @inheritDoc
     */
    public function sync_data_preprocess($itemnew): \stdClass {
        $fieldname = $this->inputname;
        if (!isset($itemnew->$fieldname)) {
            return $itemnew;
        }
        $itemnew->{$fieldname} = unformat_float($itemnew->{$fieldname});
        return $itemnew;
    }

    /**
     * @inheritDoc
     */
    public static function display_item_data($data, $extradata = []): ?string {
        if ($data === '' || $data === null) {
            return $data;
        }
        return format_float($data, $extradata['param4'] ?? static::DECIMAL_POINTS);
    }

    /**
     * Validate the form field from edit page
     *
     * @param object $data
     * @return string contains error message otherwise NULL
     **/
    public function edit_validate_decimal_field($data): array {
        $value = $data->{$this->inputname} ?? '';
        if ($value === '') {
            return [];
        }
        $decimals = $this->field->param4 ?? static::DECIMAL_POINTS;
        $value = format_float($value, $decimals, false);

        if ($this->field->param1 !== '') {
            $min = format_float($this->field->param1, $decimals, false);
            if ((float) $min > (float) $value) {
                $a = (object) ['min' => $min, 'field' => $this->get_display_fullname()];
                return [$this->inputname => get_string('user_value_min_error', static::LANG_COMPONENT, $a)];
            }
        }
        if ($this->field->param2 !== '') {
            $max = format_float($this->field->param2, $decimals, false);
            if ((float)$max < (float)$value) {
                $a = (object) ['max' => $max, 'field' => $this->get_display_fullname()];
                return [$this->inputname => get_string('user_value_max_error', static::LANG_COMPONENT, $a)];
            }
        }
        // Check if the input is a valid multiple of the step value from the
        // default value. NB: the step and default are mandatory ie they always
        // have valid numeric values, never blanks. Moreover, the default will
        // always be between the min and max values if they are defined.
        $step = $this->field->param3;
        $default_value = $this->field->defaultdata;

        // Unfortunately, there is no limit to the maximum no of decimal places
        // a user can key in. That can cause problems when converting a floating
        // point number to an integer with a decimal place count as a multiplier
        // eg 20000000.0000001 will overflow in a 32 bit system. As will a small
        // number but having > 8 decimal places.
        //
        // Also note the precision needs to be applied _individually_ to a raw
        // value, not after an arithmetic operation. Checking for remainder of 0
        // will not work if you apply it after an arithmetic operation.
        $precision = pow(10, $decimals);
        $difference = (int)($value * $precision) - (int)($default_value * $precision);

        $remainder = $difference % (int)($step * $precision);
        if ($remainder !== 0) {
            $a = (object) [
                'step' => $this->format_data($step),
                'default' => $this->format_data($default_value)
            ];
            return [$this->inputname => get_string('user_value_step_error', static::LANG_COMPONENT, $a)];
        }

        return [];
    }

    /**
     * Hook for child classes to process the data before it gets saved in database
     *
     * @param mixed $data
     * @return mixed $data
     */
    public function edit_save_decimal_data_preprocess($data) {
        // Converts locale specific floating point/comma number back to standard PHP float value
        // Do NOT try to do any math operations before this conversion on any user submitted floats!
        // Requires to save to DB as the standard PHP float value
        return unformat_float($data);
    }

    /**
     * Test if a string is a float
     *
     * @param $value
     * @return bool
     */
    private static function is_float_value($value): bool {
        return (bool)preg_match('/^-?\d*([.,]\d+)?$/', $value);
    }

    /**
     * Format the default data before processing it into the form.
     *
     * @params $value
     * @return void
     */
    private function format_data($value = '') {
        if (!empty($value)) {
            $decimals = static::DECIMAL_POINTS;
            if ($this->field->param4) {
                $decimals = (int) $this->field->param4;
            }
            $value = format_float($value, $decimals, false);
        }
        return $value;
    }
}