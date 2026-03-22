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

use totara_customfield\webapi\resolver\type\value_type_url;
use totara_webapi\phpunit\webapi_phpunit_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_type_value_type_url_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve__with_json_url_object_with_valid_values(): void {
        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $url_data = [
            "url" => "https://mytotallyrealwebsite.com",
            "text" => "A really totally real website",
        ];

        $json_encoded_url_data = json_encode($url_data);

        $resolved_value = value_type_url::resolve(
            'url',
            $json_encoded_url_data,
            [],
            $ec,
        );
        $this->assertEquals($url_data['url'], $resolved_value);

        $resolved_value = value_type_url::resolve(
            'text',
            $json_encoded_url_data,
            [],
            $ec,
        );
        $this->assertEquals($url_data['text'], $resolved_value);
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve__with_json_url_object_with_null_values(): void {

        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $url_data = [
            "url" => null,
            "text" => null,
        ];

        $json_encoded_url_data = json_encode($url_data);

        $resolved_value = value_type_url::resolve(
            'url',
            $json_encoded_url_data,
            [],
            $ec,
        );
        $this->assertEquals($url_data['url'], $resolved_value);

        $resolved_value = value_type_url::resolve(
            'text',
            $json_encoded_url_data,
            [],
            $ec,
        );
        $this->assertEquals($url_data['text'], $resolved_value);
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve__with_plain_text_url(): void {
        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $url_data = 'https://anotherrealsite.com';

        $resolved_value = value_type_url::resolve(
            'url',
            $url_data,
            [],
            $ec,
        );
        $this->assertEquals($url_data, $resolved_value);

        $resolved_value = value_type_url::resolve(
            'text',
            $url_data,
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

        $url_data = '';

        $resolved_value = value_type_url::resolve(
            'url',
            $url_data,
            [],
            $ec,
        );
        $this->assertEquals($url_data, $resolved_value);

        $resolved_value = value_type_url::resolve(
            'text',
            $url_data,
            [],
            $ec,
        );
        $this->assertNull($resolved_value);
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_resolve_with_null_value(): void {
        $ec = $this->create_webapi_context('');

        $context = context_system::instance();
        $ec->set_variable('context', $context);

        $url_data = null;

        $resolved_value = value_type_url::resolve(
            'url',
            $url_data,
            [],
            $ec,
        );
        $this->assertEquals($url_data, $resolved_value);

        $resolved_value = value_type_url::resolve(
            'text',
            $url_data,
            [],
            $ec,
        );
        $this->assertNull($resolved_value);
    }
}
