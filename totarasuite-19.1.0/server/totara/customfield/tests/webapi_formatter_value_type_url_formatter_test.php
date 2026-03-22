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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package totara_customfield
 */

use core\format;
use core_phpunit\testcase;
use totara_customfield\webapi\formatter\value_type_url_formatter;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_formatter_value_type_url_formatter_test extends testcase {

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_format(): void {
        $context = context_system::instance();

        $url = [
            "url" => "https://www.example.com",
            "text" => "A really cool website",
        ];

        $url_formatter = new value_type_url_formatter(
            $url,
            $context
        );

        $this->assertEquals($url['url'], $url_formatter->format('url', format::FORMAT_PLAIN));
        $this->assertEquals($url['text'], $url_formatter->format('text', format::FORMAT_PLAIN));
    }
}
