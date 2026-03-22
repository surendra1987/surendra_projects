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

use auth_ssosaml\model\idp;
use auth_ssosaml\model\user\manager;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\provider\data\saml_request;
use auth_ssosaml\provider\process\bindings_handler;
use auth_ssosaml\provider\process\login\handle_response;
use auth_ssosaml\provider\process\login\make_request;
use auth_ssosaml\provider\saml_contract;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @group auth_ssosaml
 */
class auth_ssosaml_provider_process_login_test extends base_saml_testcase {
    /**
     * @param string|null $url
     * @return void
     * @covers \auth_ssosaml\provider\process\login\make_request
     * @testWith ["http://example.com/url"]
     *           [null]
     */
    public function test_make_request_no_url(?string $url): void {
        $idp = idp::create(['status' => true], []);

        $saml_request = $this->createMock(saml_request::class);

        $provider = $this->createMock(saml_contract::class);
        $provider
            ->expects($this->once())
            ->method('make_login_request')
            ->with($this->equalTo($url))
            ->willReturn($saml_request);

        $bindings = $this->createMock(bindings_handler::class);
        $bindings->expects($this->once())
            ->method('submit')
            ->with($this->equalTo($saml_request));

        $make_request = (new make_request($idp, $url))->set_provider($provider);

        // Inject our bindings handler
        $make_request->set_bindings_handler($bindings);

        $make_request->execute();
    }

    /**
     * @return void
     * @covers \auth_ssosaml\provider\process\login\handle_response
     */
    public function test_handle_response(): void {
        $idp = idp::create(['status' => true], []);

        $response = $this->createMock(authn_response::class);

        $provider = $this->createMock(saml_contract::class);
        $provider
            ->expects($this->once())
            ->method('get_login_response')
            ->willReturn($response);

        $user_manager = $this->createMock(manager::class);
        $user_manager
            ->expects($this->once())
            ->method('process_login')
            ->willReturn(['status' => true]);

        $handle_response = (new handle_response($idp, $user_manager))->set_provider($provider);
        $result = $handle_response->execute();

        $this->assertSame(['status' => true], $result);
    }
}
