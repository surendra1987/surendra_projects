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
 * @package mfa_totp
 */

use core_phpunit\testcase;
use mfa_totp\totp;
use totara_webapi\client_aware_exception;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use mfa_totp\webapi\middleware\validate_token;

/**
 * @coversDefaultClass \mfa_totp\webapi\middleware\validate_token
 * @group mfa_totp
 */
class mfa_totp_webapi_middleware_validate_token_test extends testcase {
    /**
     * @return void
     */
    public function test_valid_token(): void {
        $secret = 'uj2gtj7xyuorebcxe3ma';
        $totp = new totp($secret);
        $middleware = new validate_token();

        $rc = new ReflectionClass($middleware);
        $time_property = $rc->getProperty('time');
        $time_property->setAccessible(true);
        $time_property->setValue($middleware, 2e10);

        $this->call_middleware($middleware, [
            'secret' => $secret,
            'token' => $totp->generate(2e10),
        ]);
    }

    /**
     * @return void
     */
    public function test_invalid_token(): void {
        $secret = 'uj2gtj7xyuorebcxe3ma';
        $middleware = new validate_token();

        $rc = new ReflectionClass($middleware);
        $time_property = $rc->getProperty('time');
        $time_property->setAccessible(true);
        $time_property->setValue($middleware, 2e10);

        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage(get_string('error:invalid_token', 'mfa_totp'));

        $this->call_middleware($middleware, [
            'secret' => $secret,
            'token' => '123456',
        ]);
    }

    /**
     * TODO: move to webapi_phpunit_helper?
     *
     * @param middleware $middleware
     * @param array|null $input
     * @param \Closure|null $closure
     * @return void
     */
    protected function call_middleware(middleware $middleware, ?array $input, ?\Closure $closure = null): void {
        $payload = $this->createMock(payload::class);

        if ($input !== null) {
            $payload
                ->expects($this->once())
                ->method('get_variable')
                ->with('input')
                ->willReturn($input);
        }

        $result = $this->createMock(result::class);

        $middleware->handle($payload, $closure ?? function () use ($result) {
            // Closure doesn't matter
            return $result;
        });
    }
}
