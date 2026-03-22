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

use core\date_format;
use core_phpunit\testcase;

use totara_customfield\webapi\resolver\type\value_type_datetime;
use totara_webapi\phpunit\webapi_phpunit_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_type_value_type_datetime_test extends testcase {
    use webapi_phpunit_helper;

    public function test_resolve__default_format_is_unix_timestamp() {

        $ec = $this->create_webapi_context('');

        $course_context = context_course::instance(1);

        $ec->set_variable('context', $course_context);
        $datetime = '410227200';

        $resolved_value = value_type_datetime::resolve(
            'value',
            $datetime,
            [],
            $ec,
        );

        $this->assertEquals($datetime, $resolved_value);
    }

    public function test_resolve__with_argument_format_given() {
        $ec = $this->create_webapi_context('');

        $course_context = context_course::instance(1);

        $ec->set_variable('context', $course_context);
        $datetime = '410227200';

        $resolved_value = value_type_datetime::resolve(
            'value',
            $datetime,
            [
                'format' => date_format::FORMAT_DATE,
            ],
            $ec,
        );

        $this->assertEquals('1 January 1983', $resolved_value);
    }
}
