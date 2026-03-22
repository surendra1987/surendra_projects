<?php
/**
 * This file is part of Totara Core
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_customfield
 */

use core_phpunit\testcase;
use totara_customfield\webapi\formatter\value_type_multiselect_option_formatter;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_formatter_value_type_multiselect_option_formatter_test extends testcase {

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_format(): void {
        $context = context_system::instance();

        $options = [
            'option' => 'test',
        ];

        $multiselect_formatter = new value_type_multiselect_option_formatter(
            $options,
            $context
        );

        $this->assertEquals('test', $multiselect_formatter->format('option'));
    }
}
