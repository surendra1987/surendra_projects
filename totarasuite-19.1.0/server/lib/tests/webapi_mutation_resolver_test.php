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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core
 */

use core\webapi\mutation_resolver;
use core_phpunit\testcase;

/**
 * @group core_webapi
 */
class core_webapi_mutation_resolver_test extends testcase {
    public function test_cost_per_call() {
        $this->assertEquals(10, mutation_resolver::cost_per_call());
    }

    public function test_get_middleware() {
        $this->assertEquals([], mutation_resolver::get_middleware());
    }

    private function basic_success_result(): array {
        return [
            'success' => true,
            'return_url' => null,
            'message' => null,
            'code' => null
        ];
    }

    public function test_success_result() {
        // Test protected static method.
        $reflection = new \ReflectionClass(mutation_resolver::class);
        $method = $reflection->getMethod('success_result');
        $method->setAccessible(true);

        // Invoke success.
        $expected = $this->basic_success_result();
        $result = $method->invokeArgs(null, [true]);
        $this->assertEqualsCanonicalizing($expected, $result);

        // Invoke failure.
        $expected = $this->basic_success_result();
        $expected['success'] = false;
        $expected['message'] = 'No pizza available.';
        $expected['code'] = 'no-pizza';
        $result = $method->invokeArgs(null, [false, 'No pizza available.', 'no-pizza']);
        $this->assertEqualsCanonicalizing($expected, $result);
    }

    public function test_success_url_result() {
        // Test protected static method.
        $reflection = new \ReflectionClass(mutation_resolver::class);
        $method = $reflection->getMethod('success_url_result');
        $method->setAccessible(true);

        // Invoke success.
        $expected = $this->basic_success_result();
        $result = $method->invokeArgs(null, [true]);
        $this->assertEqualsCanonicalizing($expected, $result);

        // Invoke failure.
        $expected = $this->basic_success_result();
        $expected['success'] = false;
        $expected['return_url'] = 'pizza.com';
        $expected['message'] = 'No pizza available.';
        $expected['code'] = 'no-pizza';
        $result = $method->invokeArgs(null, [false, 'pizza.com', 'No pizza available.', 'no-pizza']);
        $this->assertEqualsCanonicalizing($expected, $result);
    }
}
