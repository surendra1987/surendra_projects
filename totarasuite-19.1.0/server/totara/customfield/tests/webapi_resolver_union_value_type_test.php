<?php
/*
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
use GraphQL\Type\Definition\ResolveInfo;
use totara_customfield\webapi\resolver\type\value_type_generic;
use totara_customfield\webapi\resolver\type\value_type_text;
use totara_customfield\webapi\resolver\union\value_type;
use totara_webapi\phpunit\webapi_phpunit_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_union_value_type_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_resolve_type__with_supported_type(): void {
        $ec = $this->create_webapi_context('');
        $ec->set_variable('custom_field_type', 'text');

        $resolve_info_mock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolved_type = value_type::resolve_type(
            'my_value',
            $ec,
            $resolve_info_mock,
        );

        $this->assertEquals(value_type_text::class, $resolved_type);
    }

    /**
     * @return void
     */
    public function test_resolve_type__unknown_type_resolves_to_generic_type(): void {
        $ec = $this->create_webapi_context('');
        $ec->set_variable('custom_field_type', 'my_unknown_type');

        $resolve_info_mock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolved_type = value_type::resolve_type(
            'my_value',
            $ec,
            $resolve_info_mock,
        );

        $this->assertDebuggingCalled(['Unknown type found, using type_generic for type `my_unknown_type`']);
        $this->assertEquals(value_type_generic::class, $resolved_type);
    }

    /**
     * @return void
     */
    public function test_resolve_type__unset_type_resolves_to_generic_type(): void {
        $ec = $this->create_webapi_context('');

        $resolve_info_mock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolved_type = value_type::resolve_type(
            'my_value',
            $ec,
            $resolve_info_mock,
        );

        $this->assertDebuggingCalled(['Unknown type found, using type_generic for type ``']);
        $this->assertEquals(value_type_generic::class, $resolved_type);
    }
}
