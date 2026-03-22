<?php
/*
 *
 *  This file is part of Totara Core
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
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author Aaron Machin <aaron.machin@totara.com>
 *  @package totara_customfield
 */

use core_phpunit\testcase;

use totara_customfield\webapi\resolver\type\value_type_multiselect;
use totara_webapi\phpunit\webapi_phpunit_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_type_value_type_multiselect_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_resolve(): void {
        $ec = $this->create_webapi_context('');
        $ec->set_variable('context', context_system::instance());
        $options = [
            '0cc175b9c0f1b6a831c399e269772661' => [
                'option' => 'a',
                'icon' => '',
                'default' => '0',
                'delete' => '0'
            ],
            '92eb5ffee6ae2fec3ad71c777531578f' => [
                'option' => 'b',
                'icon' => '',
                'default' => '0',
                'delete' => '0'
            ],
        ];

        $resolved_value = value_type_multiselect::resolve(
            'options',
            json_encode($options),
            [],
            $ec,
        );

        $this->assertEquals($options, $resolved_value);
    }

    /**
     * @return void
     */
    public function test_resolve__with_empty_value(): void {
        $ec = $this->create_webapi_context('');
        $ec->set_variable('context', context_system::instance());

        $resolved_value = value_type_multiselect::resolve(
            'options',
            '',
            [],
            $ec,
        );

        $this->assertNull($resolved_value);
    }

    /**
     * @return void
     */
    public function test_resolve__with_null_value(): void {
        $ec = $this->create_webapi_context('');
        $ec->set_variable('context', context_system::instance());

        $resolved_value = value_type_multiselect::resolve(
            'options',
            null,
            [],
            $ec,
        );

        $this->assertNull($resolved_value);
    }
}
