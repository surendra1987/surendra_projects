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

use totara_customfield\webapi\resolver\type\value_type_location;
use totara_webapi\phpunit\webapi_phpunit_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_type_value_type_location_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve(): void {
        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $location = [
            "address" => "186 Willis Street",
            "size" => "medium",
            "view" => "map",
            "zoom" => 12,
            "display" => "address",
            "location" => [
                "latitude" => -41.2917599,
                "longitude" => 174.7704544
            ]
        ];
        $location_json_encoded_string = json_encode($location);

        $resolved_value = value_type_location::resolve(
            'address',
            $location_json_encoded_string,
            [],
            $ec,
        );
        $this->assertEquals($location['address'], $resolved_value);

        $resolved_value = value_type_location::resolve(
            'size',
            $location_json_encoded_string,
            [],
            $ec,
        );
        $this->assertEquals('MEDIUM', $resolved_value);

        $resolved_value = value_type_location::resolve(
            'view',
            $location_json_encoded_string,
            [],
            $ec,
        );
        $this->assertEquals('MAP', $resolved_value);

        $resolved_value = value_type_location::resolve(
            'zoom',
            $location_json_encoded_string,
            [],
            $ec,
        );
        $this->assertEquals($location['zoom'], $resolved_value);

        $resolved_value = value_type_location::resolve(
            'display',
            $location_json_encoded_string,
            [],
            $ec,
        );
        $this->assertEquals('ADDRESS', $resolved_value);

        // we copy location to coordinates as it's a more accurate naming of the data
        $resolved_value = value_type_location::resolve(
            'coordinates',
            $location_json_encoded_string,
            [],
            $ec,
        );
        $this->assertEqualsCanonicalizing($location['location'], $resolved_value);
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve__with_malformed_json(): void {
        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $malformed_json = '{"address":"my address",mymalformedkey}';
        $resolved_value = value_type_location::resolve(
            'address',
            $malformed_json,
            [],
            $ec,
        );

        $this->assertNull($resolved_value);
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve__with_empty_value(): void {
        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $resolved_value = value_type_location::resolve(
            'address',
            '',
            [],
            $ec,
        );

        $this->assertNull($resolved_value);
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve__with_null_value(): void {
        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $resolved_value = value_type_location::resolve(
            'address',
            null,
            [],
            $ec,
        );

        $this->assertNull($resolved_value);
    }
}
