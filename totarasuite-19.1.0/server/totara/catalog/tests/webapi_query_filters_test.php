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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_catalog
 */

use core\webapi\execution_context;
use core_phpunit\testcase;
use totara_catalog\catalog_retrieval;
use totara_catalog\exception\filters_query_exception;
use totara_catalog\exception\url_query_validation_exception;
use totara_catalog\webapi\resolver\type\option;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_catalog\local\config;

/**
 * @group totara_catalog
 */
class totara_catalog_webapi_query_filters_test extends testcase {
    private const QUERY = 'totara_catalog_filters';

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_query_with_two_input_fields(): void {
        self::setAdminUser();

        $this->expectException(url_query_validation_exception::class);
        $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'query_string' => "catalog_learning_type_panel[]=playlist",
                    'query_structure' => '{ "catalog_learning_type_panel" : ["playlist"], "catalog_cat_panel": 1}'
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function test_url_query_validation_with_invalid_query_key(): void {
        self::setAdminUser();

        try {
            $this->resolve_graphql_query(self::QUERY,
                [
                    'input' => ['query_string' => "aa=1&bb=2"]
                ]
            );
        } catch (filters_query_exception $e) {
            self::assertEquals("'aa' is not a valid url query key. 'bb' is not a valid url query key.", $e->getMessage());
        }

        $this->expectException(filters_query_exception::class);
        $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'query_structure' => '{ "invalid_key" : ["playlist", "engage_article"], "invalid_key1": 1}'
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function test_url_query_validation_empty_query_value(): void {
        self::setAdminUser();

        try {
            $result = $this->resolve_graphql_query(self::QUERY,
                [
                    'input' => ['query_string' => ""]
                ]
            );
        } catch (\totara_catalog\exception\url_query_validation_exception $e) {
            self::assertEquals(
                "Query string cannot be empty.",
                $e->getMessage()
            );
        }

        $this->expectException(\totara_catalog\exception\filters_query_exception::class);

        $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'query_structure' => '{ "catalog_learning_type_panel" : ""}'
                ]
            ]
        );
    }

    public function test_query_without_input(): void {
        self::setAdminUser();
        $result = $this->resolve_graphql_query(self::QUERY, []);
        $filters = $result['filters'];
        foreach ($filters as $filter) {
            foreach ($filter as $filter_options) {
                self::assertInstanceOf(\totara_catalog\webapi\schema_objects\filter::class, $filter_options);
                self::assertNotEmpty($filter_options->get_key());
                self::assertNotEmpty($filter_options->get_title());

                foreach ($filter_options->get_options() as $filter_option_option) {
                    self::assertInstanceOf(\totara_catalog\webapi\schema_objects\option::class, $filter_option_option);
                    self::assertNotEmpty($filter_option_option->get_id());
                    self::assertNotEmpty($filter_option_option->get_label());
                    self::assertIsBool($filter_option_option->get_active());
                }
            }
        }

        self::assertTrue((new catalog_retrieval())->alphabetical_sorting_enabled());
        $sort = $result['sort'];
        self::assertInstanceOf(\totara_catalog\webapi\schema_objects\filter::class, $sort);
        self::assertEquals(get_string('sort_by', 'totara_catalog'), $sort->get_title());
        foreach ($sort->get_options() as $sort_option) {
            self::assertInstanceOf(\totara_catalog\webapi\schema_objects\option::class, $sort_option);
            self::assertNotEmpty($sort_option->get_id());
            self::assertNotEmpty($sort_option->get_label());
            self::assertIsBool($sort_option->get_active());
        }
    }

    /**
     * @return void
     */
    public function test_filters_with_input(): void {
        self::setAdminUser();
        config::instance()->update(['filters' => ['catalog_learning_type_panel' => 'Learning type', 'course_acttyp_panel' => 'Activity type', 'course_format_multi' => 'Format',],]);

        $generator = self::getDataGenerator();
        $cat1= $generator->create_category(['name' => 'name1']);
        $generator->create_category(['parent' => $cat1->id, 'name' => 'name2']);

        $result = $this->resolve_graphql_query(self::QUERY, []);
        self::assertEquals(3, count($result['filters']));
        self::assertNotEmpty($result['browse_filter']);
        $browse_filter = $result['browse_filter'];
        foreach ($browse_filter->get_options() as $option) {
            self::assertInstanceOf(\totara_catalog\webapi\schema_objects\tree_option::class, $option);
            self::assertTrue(in_array($option->get_label(), ['name1', 'name2', 'Miscellaneous', 'All']));
        }

        $result = $this->resolve_graphql_query(self::QUERY, ['input' => ['query_string' => "catalog_learning_type_panel[]=course&orderbykey=text&itemstyle=narrow"]]);

        $filters = $result['filters'];
        foreach ($filters as $filter) {
            foreach ($filter as $filter_options) {
                self::assertInstanceOf(\totara_catalog\webapi\schema_objects\filter::class, $filter_options);
                self::assertNotEmpty($filter_options->get_key());
                self::assertNotEmpty($filter_options->get_title());

                foreach ($filter_options->get_options() as $filter_option_option) {
                    self::assertInstanceOf(\totara_catalog\webapi\schema_objects\option::class, $filter_option_option);
                    self::assertNotEmpty($filter_option_option->get_id());
                    self::assertNotEmpty($filter_option_option->get_label());
                    if ($filter_option_option->get_label() == 'course') {
                        self::assertTrue($filter_option_option->get_active());
                    } else {
                        self::assertFalse($filter_option_option->get_active());
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    public function test_filter_options_with_special_characters(): void {
        self::setAdminUser();

        config::instance()->update(['filters' => ['tag_panel_1' => 'Topics']]);

        $gen = self::getDataGenerator();
        $tags = [];
        $gen->create_tag(['rawname' => '@#$%^&*()><']);
        $tags[] = '@#$%^<>&*()';

        $gen->create_course(['tags' => $tags]);
        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => ['query_string' => "orderbykey=time"]
            ]
        );

        $filters = $result['filters'];
        $filter = reset($filters);
        $options = $filter->get_options();
        $option = reset($options);
        $label = option::resolve('label', $option, [], $this->createMock(execution_context::class));
        self::assertEquals('@#$%^&*()', $label);
    }
}