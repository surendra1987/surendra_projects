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
require_once($CFG->dirroot . '/totara/customfield/field/integer/field.class.php');

/**
 * @group totara_customfield_numeric
 */
class totara_customfield_integer_test extends \core_phpunit\testcase {
    public function test_edit_validate_field(): void {
        $field = self::create_data(10, 50, 15, 5);

        $item = (object)['customfield_number1' => 20];
        $errors = $field->edit_validate_field($item, 'course', 'course');
        $this->assertEmpty($errors);

        // Test 'min'
        $item->customfield_number1 = 9;
        $errors = $field->edit_validate_field($item, 'course', 'course');
        $this->assertStringContainsString($errors['customfield_number1'], "The value of 'number_custom_field' field must be greater than or equal to 10 (minimum value)");

        // Test 'max'
        $item->customfield_number1 = 51;
        $errors = $field->edit_validate_field($item, 'course', 'course');
        $this->assertStringContainsString($errors['customfield_number1'], "The value of 'number_custom_field' field must be less than or equal to 50 (maximum value)");
    }

    public static function td_step() {
        return [
            [true,  10, 50, 15, 5, 10],
            [false, 10, 50, 15, 5, 11],
            [true,  10, 50, 15, 5, 15],
            [true,  10, 50, 15, 5, 50],
            [false, 10, 50, 15, 5, 46],

            [true,  -50, -10, -20, -5, -50],
            [false, -50, -10, -20, -5, -33],
            [true,  -50, -10, -20, -5, -20],
            [true,  -50, -10, -20, -5, -10],
            [false, -50, -10, -20, -5, -18]
        ];
    }

    /**
     * @dataProvider td_step
     */
    public function test_step(
        bool $should_pass,
        int $min,
        int $max,
        int $def,
        int $step,
        int $value
    ): void {
        $field = self::create_data($min, $max, $def, $step);

        $item = (object)['customfield_number1' => $value];
        $errors = $field->edit_validate_field($item, 'course', 'course');

        $should_pass
            ? $this->assertEmpty($errors)
            : $this->assertStringContainsString(
                $errors['customfield_number1'],
                "The value must be a multiple of $step starting from $def"
            );
    }

    private function create_data(
        int $min,
        int $max,
        int $def,
        int $step
    ): customfield_integer {
        $generator = $this->getDataGenerator();
        $course = $this->getDataGenerator()
            ->create_course(['fullname' => 'Course 1']);

        $settings = [
            'number_custom_field' => [
                'shortname' => 'number1',
                'min' => $min,
                'max' => $max,
                'def' => $def,
                'step' => $step
            ]
        ];

        $numberids = $generator->get_plugin_generator('totara_customfield')
            ->create_integer('course', $settings);

        return new customfield_integer(
            $numberids['number_custom_field'], $course, 'course', 'course'
        );
    }
}