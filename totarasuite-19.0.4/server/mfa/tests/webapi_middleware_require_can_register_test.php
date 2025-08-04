<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core_mfa\webapi\middleware\require_can_register;
use core_phpunit\testcase;

/**
 * @coversDefaultClass \core_mfa\webapi\middleware\require_can_register
 * @group core_mfa
 */
class core_mfa_webapi_middleware_require_can_register_test extends testcase {
    /**
     * Assert that you can register the MFA with the capabilities
     *
     * @return void
     */
    public function test_with_permission(): void {
        $middleware = new require_can_register();
        set_config('mfa_plugins', 'button,totp');

        $this->setAdminUser();

        // Assert that the middleware succeeds without interruption
        $result = new result(['success' => true]);
        $payload = $this->createMock(payload::class);
        $call_result = $middleware->handle($payload, function () use ($result) {
            return $result;
        });

        $this->assertSame($result, $call_result);
    }

    /**
     * Assert that you cannot register the MFA without the capabilities
     *
     * @return void
     */
    public function test_without_permission(): void {
        $this->setUser(self::getDataGenerator()->create_user());
        $middleware = new require_can_register();

        set_config('mfa_plugins', 'button,totp');

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('nopermissions', 'error', get_string('register_a_new_factor', 'mfa')));

        $result = new result(['success' => true]);
        $payload = $this->createMock(payload::class);
        $middleware->handle($payload, function () use ($result) {
            return $result;
        });
    }
}
