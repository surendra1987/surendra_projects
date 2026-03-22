<?php
/**
 *  This file is part of Totara Talent Experience Platform
 *
 *  Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author Aaron Machin <aaron.machin@totara.com>
 *  @package tool_usagedata
 */

use core_phpunit\testcase;
use tool_usagedata\exporter;
use tool_usagedata\export;

class tool_usagedata_exported_data_formatting_test extends testcase {

    /**
     * Checks that export classes which are of type export::TYPE_OBJECT do not contain whitespaces in their column names.
     *
     * This is to prevent issues when writing the related Athena schema.
     * @return void
     */
    public function test_export_with_type_object_does_not_contain_whitespace_in_column_names(): void {
        $export_classes = $this->callInternalMethod(new exporter(), 'initialise_data', []);

        foreach ($export_classes as $export_class) {
            $export_class_instance = $this->callInternalMethod($export_class, 'instance', []);
            if ($export_class_instance->get_type() !== export::TYPE_OBJECT) {
                continue;
            }

            $data = $export_class->export();

            // Data returned is empty, nothing to check
            if ($data === null || is_object($data)) {
                continue;
            }

            foreach (array_keys($data) as $column_name) {
                $column_name_stripped_of_whitespace = preg_replace('/\s+/', '', $column_name);

                $this->assertEquals(
                    $column_name_stripped_of_whitespace,
                    $column_name,
                    "$export_class->name: Exported data of type object should not include whitespaces in column names"
                );
            }
        }
    }

    /**
     * Checks that export data with type export::TYPE_ARRAY returns a list.
     *
     * This is a sanity check to ensure that the type is set correctly for arrays.
     *
     * A list here is defined as an array which has incrementing numbers, starting at 0, for the array keys.
     * @return void
     */
    public function test_export_data_with_type_array_returns_a_list(): void {
        $export_classes = $this->callInternalMethod(new exporter(), 'initialise_data', []);

        foreach ($export_classes as $export_class) {
            $export_class_instance = $this->callInternalMethod($export_class, 'instance', []);
            if ($export_class_instance->get_type() !== export::TYPE_ARRAY) {
                continue;
            }

            $data = $export_class->export();

            // Data returned is empty, nothing to check
            if ($data === null) {
                continue;
            }

            $index_expected = 0;
            foreach ($data as $index_actual => $value) {
                $this->assertEquals(
                    $index_expected,
                    $index_actual,
                    "$export_class->name: Export data of type array should return a list (array indexed by incrementing numbers)"
                );

                $index_expected++;
            }
        }
    }

    /**
     * Checks that the get_summary() method successfully runs on export classes.
     *
     * This is a sanity check to ensure the export::get_summary() method on export classes does not throw an exception.
     *
     * An example of where this might occur, and the type of scenario this test will catch:
     * get_string() call has been incorrectly written (for instance, the wrong component specified)
     * @return void
     */
    public function test_export_data_has_get_summary_correctly_set(): void {
        $export_classes = $this->callInternalMethod(new exporter(), 'initialise_data', []);

        foreach ($export_classes as $export_class) {
            $export_class_instance = $this->callInternalMethod($export_class, 'instance', []);
            // Run each export::get_summary() method to ensure none throw exceptions
            $export_class_instance->get_summary();
        }
        $this->assertTrue(true);
    }


}