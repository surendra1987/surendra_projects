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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\testing\generator;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core_phpunit\testcase as phpunit_testcase;

/**
 * Base test class for saml tests.
 */
abstract class base_saml_testcase extends phpunit_testcase {
    /**
     * @var generator
     */
    protected generator $ssosaml_generator;

    /**
     * @return void
     */
    protected function setUp(): void {
        $this->ssosaml_generator = $this->getDataGenerator()->get_plugin_generator('auth_ssosaml');
        $this->ssosaml_generator->enable_plugin();
        parent::setUp();
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        unset($this->ssosaml_generator);
        parent::tearDown();
    }

    /**
     * @param middleware $middleware
     * @param array|null $input
     * @param callable|null $closure
     * @return void
     */
    protected function call_middleware(middleware $middleware, ?array $input, ?callable $closure = null): void {
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


    /**
     * @return void
     */
    protected function skip_on_no_openssl(): void {
        if (!\auth_ssosaml\local\util::is_openssl_loaded()) {
            $this->markTestSkipped('PHP extension openssl was not available');
        }
    }
}