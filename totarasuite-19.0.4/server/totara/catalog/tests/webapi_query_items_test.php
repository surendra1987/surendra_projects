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

use core_phpunit\testcase;
use totara_catalog\catalog_retrieval;
use totara_catalog\exception\items_query_exception;
use totara_catalog\exception\url_query_validation_exception;
use totara_catalog\local\query_helper;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_catalog\local\config;

/**
 * @group totara_catalog
 */
class totara_catalog_webapi_query_items_test extends testcase {
    private const QUERY = 'totara_catalog_items';

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
        } catch (items_query_exception $e) {
            self::assertEquals("'aa' is not a valid url query key. 'bb' is not a valid url query key.", $e->getMessage());
        }

        $this->expectException(items_query_exception::class);
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

        $this->expectException(\totara_catalog\exception\items_query_exception::class);

        $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'query_structure' => '{ "catalog_learning_type_panel" : ""}'
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function test_query_items(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $gen->create_course();
        $gen->create_course();
        $programgenerator = $gen->get_plugin_generator('totara_program');
        $programgenerator->create_program();

        config::instance()->update(['filters' => ['catalog_learning_type_panel' => 'Learning type', 'course_acttyp_panel' => 'Activity type', 'course_format_multi' => 'Format',],]);

        $generator = self::getDataGenerator();
        $cat1= $generator->create_category(['name' => 'name1']);
        $generator->create_category(['parent' => $cat1->id, 'name' => 'name2']);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=time&itemstyle=narrow']
            ]
        );
        self::assertEquals(3, $result['maxcount']);
        self::assertEquals(3, count($result['items']));
        // Check cursor properties.
        self::assertNotEquals(3, $result['limitfrom']);
        self::assertIsString($result['limitfrom']);
        self::assertGreaterThanOrEqual('12', strlen($result['limitfrom']));

        foreach ($result['items'] as $item) {
            self::assertNotEmpty($item->itemid);
            self::assertNotEmpty($item->title);
            self::assertNotEmpty($item->redirecturl);
            self::assertNotEmpty($item->image);
            self::assertIsBool($item->image_enabled);
            self::assertIsBool($item->featured);
            self::assertEmpty($item->logo);
            self::assertNotEmpty($item->objecttype);
            self::assertFalse($item->hero_data_icon_enabled);
            self::assertFalse($item->hero_data_text_enabled);
            self::assertNotEmpty($item->hero_data_type);
            self::assertNull($item->hero_data_text);
            self::assertNull($item->hero_data_icon);
            self::assertFalse($item->description_enabled);
            self::assertNull($item->description);
            self::assertFalse($item->progress_bar_enabled);
            self::assertNull($item->progress_bar);
            self::assertTrue($item->text_placeholders_enabled);
            self::assertNotEmpty($item->text_placeholders);
        }

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
                self::assertInstanceOf(\totara_catalog\webapi\schema_objects\options::class, $filter_options);
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
    public function test_query_string_pagination(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        for($i=0; $i<23; $i++) {
            $gen->create_course();
        }

        self::assertEquals(20, config::instance()->get_value('items_per_load'));

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=time&itemstyle=narrow']
            ]
        );

        self::assertEquals(23, $result['maxcount']);
        self::assertEquals(20, count($result['items']));
        // Check cursor properties.
        self::assertNotEquals(20, $result['limitfrom']);
        self::assertIsString($result['limitfrom']);
        self::assertGreaterThanOrEqual('12', strlen($result['limitfrom']));

        $cursor = $result['limitfrom'];
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => "orderbykey=time&itemstyle=narrow&limitfrom={$cursor}&maxcount=23"]
            ]
        );

        self::assertEquals(23, $result['maxcount']);
        // Still the first page because query string input.
        self::assertEquals(20, count($result['items']));
        // Check cursor properties.
        // Different cursor because query string input.
        self::assertNotEquals($cursor, $result['limitfrom']);
        self::assertIsString($result['limitfrom']);
        self::assertGreaterThanOrEqual('12', strlen($result['limitfrom']));

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
    public function test_query_structure_pagination(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        for($i=0; $i<23; $i++) {
            $gen->create_course();
        }

        self::assertEquals(20, config::instance()->get_value('items_per_load'));

        $structure_json = query_helper::json_encode(['orderbykey' => 'time', 'itemstyle' => 'narrow']);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_structure' => $structure_json]
            ]
        );

        self::assertEquals(23, $result['maxcount']);
        self::assertEquals(20, count($result['items']));
        // Check cursor properties.
        self::assertNotEquals(20, $result['limitfrom']);
        self::assertIsString($result['limitfrom']);
        self::assertGreaterThanOrEqual('12', strlen($result['limitfrom']));

        $cursor = $result['limitfrom'];
        $structure_json = query_helper::json_encode(['orderbykey' => 'time', 'itemstyle' => 'narrow', 'limitfrom' => $cursor, 'maxcount' => 23]);
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_structure' => $structure_json]
            ]
        );

        self::assertEquals(23, $result['maxcount']);
        // Second page because query structure input.
        self::assertEquals(3, count($result['items']));
        // Check cursor properties.
        // Same cursor because query structure input.
        self::assertEquals($cursor, $result['limitfrom']);
        self::assertIsString($result['limitfrom']);
        self::assertGreaterThanOrEqual('12', strlen($result['limitfrom']));

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
    public function test_query_string_with_perpageload(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        for($i=0; $i<23; $i++) {
            $gen->create_course();
        }

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=time&itemstyle=narrow&perpageload=5']
            ]
        );

        self::assertEquals(5, count($result['items']));
        self::assertEquals(23, $result['maxcount']);
        // Check cursor properties.
        self::assertNotEquals(5, $result['limitfrom']);
        self::assertIsString($result['limitfrom']);
        self::assertGreaterThanOrEqual('12', strlen($result['limitfrom']));
    }
}
