<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

use core_phpunit\testcase;
use tool_usagedata\local\data;
use tool_usagedata\export;

class tool_usagedata_local_data_test extends testcase {

    /**
     * @covers data::get_export_data()
     */
    public function test_export_with_type_array() {
        $mock_export_class = new class implements export {

            const COMPONENT = 'tool_usagedata';

            public function get_summary(): string {
                return '';
            }

            public function get_type(): int {
                return export::TYPE_ARRAY;
            }

            public function export(): array {
                return ['some', 'data', 'here'];
            }
        };

        $result = (new data(get_class($mock_export_class)))->export();

        $this->assertNotEmpty($result);
        $this->assertEquals('["some","data","here"]', json_encode($result));
    }

    /**
     * @covers data::get_export_data()
     */
    public function test_export_with_type_array_empty() {
        $mock_export_class = new class implements export {

            const COMPONENT = 'tool_usagedata';

            public function get_summary(): string {
                return '';
            }

            public function get_type(): int {
                return export::TYPE_ARRAY;
            }

            public function export(): array {
                return [];
            }
        };

        $result = (new data(get_class($mock_export_class)))->export();

        $this->assertEmpty($result);
        $this->assertEquals('[]', json_encode($result));
    }

    /**
     * @covers data::get_export_data()
     */
    public function test_export_with_type_object() {
        $mock_export_class = new class implements export {

            const COMPONENT = 'tool_usagedata';

            public function get_summary(): string {
                return '';
            }

            public function get_type(): int {
                return export::TYPE_OBJECT;
            }

            public function export(): array {
                return [
                    'some' => 'data',
                    'here' => '<3',
                ];
            }
        };

        $result = (new data(get_class($mock_export_class)))->export();

        $this->assertNotEmpty($result);
        $this->assertEquals('{"some":"data","here":"<3"}', json_encode($result));
    }

    /**
     * @covers data::get_export_data()
     */
    public function test_export_with_type_object_empty() {
        $mock_export_class = new class implements export {

            const COMPONENT = 'tool_usagedata';

            public function get_summary(): string {
                return '';
            }

            public function get_type(): int {
                return export::TYPE_OBJECT;
            }

            public function export(): array {
                return [];
            }
        };

        $result = (new data(get_class($mock_export_class)))->export();

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertEquals('{}', json_encode($result));
    }

    /**
     * @covers data::handle_error()
     * @covers data::get_error()
     * @covers data::export()
     */
    public function test_export_error_exception() {
        global $CFG;

        $mock_export_class = new class implements export {

            const COMPONENT = 'tool_usagedata';

            public function get_summary(): string {
                return '';
            }

            public function get_type(): int {
                return export::TYPE_ARRAY;
            }

            public function export(): array {
                throw new Exception('test_exception');
            }
        };

        // Prevent standard logging.
        ini_set('error_log', "$CFG->dataroot/testlog.log");

        $data = new data(get_class($mock_export_class));
        $result = $data->export();

        $this->assertDebuggingCalled();
        $this->assertNull($result);

        $error = $data->get_error();
        $this->assertEquals([
            'component_type' => 'tool',
            'component_name' => 'usagedata',
            'export_name' => get_class($mock_export_class),
            'description' => 'Exception occurred',
            'error' => [
                'class' => Exception::class,
                'message' => 'test_exception',
            ],
        ], $error);
    }

    /**
     * @covers data::handle_error()
     * @covers data::get_error()
     * @covers data::export()
     */
    public function test_export_error_throwable() {
        global $CFG;

        $mock_export_class = new class implements export {

            const COMPONENT = 'tool_usagedata';

            public function get_summary(): string {
                return '';
            }

            public function get_type(): int {
                return export::TYPE_ARRAY;
            }

            public function export(): array {
                throw new Error('test_throwable');
            }
        };

        // Prevent standard logging.
        ini_set('error_log', "$CFG->dataroot/testlog.log");

        $data = new data(get_class($mock_export_class));
        $result = $data->export();

        $this->assertDebuggingCalled();
        $this->assertNull($result);

        $error = $data->get_error();
        $this->assertEquals([
            'component_type' => 'tool',
            'component_name' => 'usagedata',
            'export_name' => get_class($mock_export_class),
            'description' => 'Throwable occurred',
            'error' => [
                'class' => Error::class,
                'message' => 'test_throwable',
            ],
        ], $error);
    }

    public function test_export_type_not_valid() {
        $mock_export_class = new class implements export {
            const COMPONENT = 'tool_usagedata';

            public function get_summary(): string {
                return '';
            }

            public function get_type(): int {
                return 64;
            }

            public function export(): array {
                return [];
            }
        };

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Type must be set correctly on export classes');

        new data(get_class($mock_export_class));
    }
}