<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totaralearning.com>
 * @package totara_customfield
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/totara/customfield/fieldlib.php');
require_once($CFG->dirroot . '/totara/customfield/field/decimal/field.class.php');

/**
 * @group totara_customfield_numeric
 */
class totara_customfield_decimal_test extends \core_phpunit\testcase {

    public function test_edit_validate_field() {
        $field = self::create_data(10.5, 50, 15, 0.5, 4);

        $item = (object)['customfield_number1' => 20.5];
        $errors = $field->edit_validate_field($item, 'course', 'course');
        $this->assertEmpty($errors);

        // Test 'min'
        $item->customfield_number1 = 9.05;
        $errors = $field->edit_validate_field($item, 'course', 'course');
        $this->assertStringContainsString($errors['customfield_number1'], "The value of 'number_custom_field' field must be greater than or equal to 10.5000 (minimum value)");

        // Test 'max'
        $item->customfield_number1 = 51;
        $errors = $field->edit_validate_field($item, 'course', 'course');
        $this->assertStringContainsString($errors['customfield_number1'], "The value of 'number_custom_field' field must be less than or equal to 50.0000 (maximum value)");
    }

    public static function td_step() {
        return [
            'def=15,step=0.50,value=14.00' => [true, 10, 20, 15, 0.5, 14],
            'def=15,step=0.33,value=14.67' => [true, 10, 20, 15, 0.33, 14.67],
            'def=15,step=0.33,value=14.66' => [false, 10, 20, 15, 0.33, 14.66],
            'def=15,step=0.34,value=14.66' => [true, 0, 20, 15, 0.34, 14.66],

            'def=-15,step=-0.50,value=-14.00' => [
                true, -50, -10, -15, -0.5, -14
            ],

            'def=-15,step=-0.33,value=-14.00' => [
                true, -50, -10, -15, -0.33, -14.67
            ],

            'def=-15,step=-0.33,value=-14.00' => [
                false, -50, -10, -15, -0.33, -14.66
            ]
        ];
    }

    /**
     * @dataProvider td_step
     */
    public function test_step(
        bool $should_pass,
        float $min,
        float $max,
        float $def,
        float $step,
        float $value
    ): void {
        $dec_places = 2;
        $formatted_def = format_float($def, $dec_places);
        $formatted_step = format_float($step, $dec_places);

        $field = self::create_data($min, $max, $def, $step, $dec_places);

        $item = (object)['customfield_number1' => $value];
        $errors = $field->edit_validate_field($item, 'course', 'course');

        $should_pass
            ? $this->assertEmpty($errors)
            : $this->assertStringContainsString(
                $errors['customfield_number1'],
                "The value must be a multiple of $formatted_step starting from $formatted_def"
            );
    }

    private function create_data(
        float $min,
        float $max,
        float $def,
        float $step,
        int $no_decimals
    ): customfield_decimal {
        $generator = $this->getDataGenerator();
        $course = $this->getDataGenerator()
            ->create_course(['fullname' => 'Course 1']);

        $settings = [
            'number_custom_field' => [
                'shortname' => 'number1',
                'min' => $min,
                'max' => $max,
                'def' => $def,
                'step' => $step,
                'no_decimals' => $no_decimals
            ]
        ];

        $numberids = $generator->get_plugin_generator('totara_customfield')
            ->create_decimal('course', $settings);

        return new customfield_decimal(
            $numberids['number_custom_field'], $course, 'course', 'course'
        );
    }
}