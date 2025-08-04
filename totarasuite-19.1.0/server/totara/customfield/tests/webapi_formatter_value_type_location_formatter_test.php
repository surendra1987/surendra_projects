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
use totara_customfield\webapi\formatter\value_type_location_formatter;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_formatter_value_type_location_formatter_test extends testcase {

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_format(): void {
        $context = context_system::instance();

        $location = [
            "address" => "186 Willis Street",
            "size" => "medium",
            "view" => "map",
            "zoom" => 12,
            "display" => "address",
            "coordinates" => [
                "latitude" => -41.2917599,
                "longitude" => 174.7704544,
            ]
        ];

        $location_formatter = new value_type_location_formatter(
            $location,
            $context
        );

        $this->assertEquals($location['address'], $location_formatter->format('address', format::FORMAT_PLAIN));
        $this->assertEquals('MEDIUM', $location_formatter->format('size', format::FORMAT_PLAIN));
        $this->assertEquals('MAP', $location_formatter->format('view', format::FORMAT_PLAIN));
        $this->assertEquals(12, $location_formatter->format('zoom', format::FORMAT_PLAIN));
        $this->assertEquals('ADDRESS', $location_formatter->format('display', format::FORMAT_PLAIN));
        $this->assertEqualsCanonicalizing($location['coordinates'], $location_formatter->format('coordinates', format::FORMAT_PLAIN));
    }
}
