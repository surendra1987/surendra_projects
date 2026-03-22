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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package totara_webapi
 */

use core_phpunit\testcase;
use totara_webapi\endpoint_type\ajax;
use totara_webapi\endpoint_type\dev;
use totara_webapi\endpoint_type\external;

class totara_webapi_endpoint_type_test extends testcase {

    public function test_validation_rules(): void {
        $dev = new dev();
        self::assertEmpty($dev->get_validation_rules());

        $ajax = new ajax();
        self::assertEmpty($ajax->get_validation_rules());

        $external = new external();
        self::assertNotEmpty($external->get_validation_rules());
        self::assertCount(1, $external->get_validation_rules());
    }

    /**
     * @return void
     */
    public function test_client_validation_rules(): void {
        // introspection disabled
        $client = $this->createMock(\totara_api\model\client::class);
        $client_settings = $this->createMock(\totara_api\model\client_settings::class);
        $client_settings->expects($this->any())->method('__get')->with('enable_introspection')->willReturn(0);
        $client->expects($this->any())->method('get_client_settings')->willReturn($client_settings);

        $external = new external();
        $this->assertCount(1, $external->get_client_validation_rules($client));
        // client setting shouldn't affect global validation rules
        $this->assertCount(1, $external->get_validation_rules());

        // introspection enabled
        $client = $this->createMock(\totara_api\model\client::class);
        $client_settings = $this->createMock(\totara_api\model\client_settings::class);
        $client_settings->expects($this->any())->method('__get')->with('enable_introspection')->willReturn(1);
        $client->expects($this->any())->method('get_client_settings')->willReturn($client_settings);

        $external = new external();
        $this->assertCount(0, $external->get_client_validation_rules($client));
        // client setting shouldn't affect global validation rules
        $this->assertCount(1, $external->get_validation_rules());
    }
}