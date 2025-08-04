<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_catalog
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group totara_catalog
 */
class totara_catalog_webapi_query_url_query_validation_test extends testcase {
    private const QUERY = 'totara_catalog_url_query_validation';

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_url_query_validation_with_two_input_feilds(): void {
        self::setAdminUser();

        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'query_string' => "catalog_learning_type_panel[]=playlist",
                    'query_structure' => '{ "catalog_learning_type_panel" : ["playlist", "engage_article"], "catalog_cat_panel": 1}'
                ]
            ]
        );
        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertTrue($result->error);
        self::assertEmpty($result->total_count);
        self::assertEquals('Can not query two input fields.', $result->error_message);
    }

    /**
     * @return void
     */
    public function test_url_query_validation_without_input_fields(): void {
        self::setAdminUser();

        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => []
            ]
        );
        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertTrue($result->error);
        self::assertEmpty($result->total_count);
        self::assertStringContainsString('You must provide a query string or query structure.', $result->error_message);
    }

    /**
     * @return void
     */
    public function test_url_query_validation_with_invalid_query_key(): void {
        self::setAdminUser();

        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => ['query_string' => "aa=1&bb=2"]
            ]
        );
        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertTrue($result->error);
        self::assertEmpty($result->total_count);
        self::assertStringContainsString("'aa' is not a valid url query key. 'bb' is not a valid url query key", $result->error_message);

        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'query_structure' => '{"invalid_key" : ["playlist", "engage_article"], "invalid_key1" : 1}'
                ]
            ]
        );

        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertTrue($result->error);
        self::assertEmpty($result->total_count);
        self::assertStringContainsString("'invalid_key' is not a valid url query key. 'invalid_key1' is not a valid url query key", $result->error_message);


        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => ['query_string' => 'perpageload=invalid&limitfrom=invalid']
            ]
        );

        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertTrue($result->error);
        self::assertEmpty($result->total_count);
        self::assertEquals("perpageload's value must be numeric.", $result->error_message);
    }

    /**
     * @return void
     */
    public function test_url_query_validation_empty_query_value(): void {
        self::setAdminUser();

        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => ['query_string' => ""]
            ]
        );
        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertTrue($result->error);
        self::assertEmpty($result->total_count);
        self::assertStringContainsString('Query string cannot be empty.', $result->error_message);

        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'query_structure' => '{ "catalog_learning_type_panel" : []}'
                ]
            ]
        );

        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertTrue($result->error);
        self::assertEmpty($result->total_count);
        self::assertStringContainsString(
            "catalog_learning_type_panel's value must not be empty.",
            $result->error_message);
    }

    /**
     * @return void
     */
    public function test_url_query_validation_with_query_string_filtered(): void {
        self::setAdminUser();
        $this->create_mock_data();

        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => ['query_string' => "catalog_learning_type_panel[]=course&catalog_learning_type_panel[]=program"]
            ]
        );
        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertFalse($result->error);
        self::assertEmpty($result->error_message);
        self::assertEquals(4, $result->total_count);
    }

    /**
     * @return void
     */
    public function test_url_query_validation_with_all(): void {
        self::setAdminUser();
        $this->create_mock_data();
        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=featured']
            ]
        );

        self::assertFalse($result->error);
        self::assertEmpty($result->error_message);
        self::assertEquals(7, $result->total_count);
    }

    /**
     * @return void
     */
    public function test_url_query_validation_with_query_structure(): void {
        self::setAdminUser();
        $this->create_mock_data();
        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'query_structure' => '{ "catalog_learning_type_panel" : ["program", "engage_article"]}'
                ]
            ]
        );

        self::assertInstanceOf(totara_catalog\webapi\schema_objects\url_query_validation_result::class, $result);
        self::assertFalse($result->error);
        self::assertEmpty($result->error_message);
        self::assertEquals(3, $result->total_count);
    }

    /**
     * @return void
     */
    private function create_mock_data(): void {
        $gen = self::getDataGenerator();

        $gen->create_course();
        $gen->create_course();
        $gen->create_course();

        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $gen->get_plugin_generator('totara_program');
        $program_generator->create_certification();
        $program_generator->create_program();

        /** @var \engage_article\testing\generator $resources_generator */
        $resources_generator = $gen->get_plugin_generator('engage_article');

        $resources_generator->create_public_article();
        $resources_generator->create_public_article();
    }
}
