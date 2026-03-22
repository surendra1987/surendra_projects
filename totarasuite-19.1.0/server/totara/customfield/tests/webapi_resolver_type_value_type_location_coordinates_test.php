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

use totara_customfield\webapi\resolver\type\value_type_location_coordinates;
use totara_webapi\phpunit\webapi_phpunit_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_type_value_type_location_coordinates_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve(): void {
        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $coordinates = [
            "latitude" => -41.2917599,
            "longitude" => 174.7704544,
        ];

        $resolved_value = value_type_location_coordinates::resolve(
            'latitude',
            $coordinates,
            [],
            $ec,
        );
        $this->assertEquals($coordinates['latitude'], $resolved_value);

        $resolved_value = value_type_location_coordinates::resolve(
            'longitude',
            $coordinates,
            [],
            $ec,
        );
        $this->assertEquals($coordinates['longitude'], $resolved_value);
    }
}
