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

use totara_catalog\local\config;
use core_phpunit\testcase;
use totara_catalog\catalog_retrieval;
use totara_catalog\exception\url_query_validation_exception;
use totara_catalog\local\filter_handler;
use totara_catalog\local\query_helper;

/**
 * @group totara_catalog
 */
class totara_catalog_query_helper_test extends testcase {

    public function test_validate_params(): void {
        $params = query_helper::parse_input_to_params([
            'query_string' => 'invalid[]=course&orderbykey=text&itemstyle=narrow'
        ]);

        $catalog = new catalog_retrieval();
        $errors = [];
        query_helper::validate_params($params, $errors, $catalog);

        self::assertTrue(in_array("'invalid' is not a valid url query key.", $errors));
    }

    public function test_get_valid_sort_options(): void {
        $filter_handler = filter_handler::instance();
        $catalog = new catalog_retrieval();
        $options = query_helper::get_valid_sort_options($catalog, $filter_handler);

        self::assertNotEmpty($options);
    }

    public function test_parse_input_to_params(): void {
        $params = query_helper::parse_input_to_params([
            'query_string' => 'catalog_learning_type_panel[]=course&tag_panel_1[]=1&orderbykey=text&itemstyle=narrow'
        ]);

        self::assertNotEmpty($params);
        $expected_input_params = ['cursor' => null, 'limitfrom' => 0, 'maxcount' => -1];
        self::assertEqualsCanonicalizing($expected_input_params, query_helper::parse_input_to_params([]));
    }

    public function test_validate_input(): void {
        $this->expectException(url_query_validation_exception::class);
        query_helper::validate_input([
            'query_string' => '',
            'query_structure' => []
        ]);
    }

    public function test_validate_active_filters(): void {
        self::assertFalse(query_helper::validate_active_filters('course_acttyp_browse=7&course_format_panel[0]=pathway'));
        self::assertFalse(query_helper::validate_active_filters('tag_panel_1[0]=1'));

        config::instance()->update(['filters' => ['catalog_learning_type_panel' => 'Learning type']]);
        self::assertTrue(query_helper::validate_active_filters('catalog_cat_browse=3&catalog_learning_type_panel[0]=1'));
    }

    public static function create_cursor_data_provider(): array {
        return [
            'Basic test' => ['limitfrom_input' => 22, 'maxcount_input' => 64],
            'Maxint test' => ['limitfrom_input' => PHP_INT_MAX, 'maxcount_input' => PHP_INT_MAX],
            'Zeroes test' => ['limitfrom_input' => 0, 'maxcount_input' => 0],
        ];
    }

    /**
     * @dataProvider create_cursor_data_provider
     */
    public function test_create_cursor($limitfrom_input, $maxcount_input): void {
        // Test requires a session cache.
        $cache = cache::make('totara_catalog', 'pagination_cursors');

        // Assert cursor properties.
        $page = ['limitfrom' => $limitfrom_input, 'maxcount' => $maxcount_input];
        $cursor = query_helper::create_cursor($page['limitfrom'], $page['maxcount']);
        self::assertNotEmpty($cursor);
        self::assertIsString($cursor);
        self::assertGreaterThanOrEqual('12', strlen($cursor));

        // Different cursor in same cache.
        $different_page = ['limitfrom' => 0, 'maxcount' => -1];
        $different_cursor = query_helper::create_cursor($different_page['limitfrom'], $different_page['maxcount']);
        self::assertNotEmpty($different_cursor);
        self::assertIsString($different_cursor);
        self::assertGreaterThanOrEqual('12', strlen($different_cursor));
        self::assertNotEquals($cursor, $different_cursor);

        // Asset that both are in cache.
        $test_cursor_cache = $cache->get($cursor);
        self::assertEqualsCanonicalizing($page, $test_cursor_cache);
        $different_cursor_cache = $cache->get($different_cursor);
        self::assertEqualsCanonicalizing($different_page, $different_cursor_cache);

        // Test reusing the key.
        $new_cursor = query_helper::create_cursor($different_page['limitfrom'], $different_page['maxcount'], $cursor);
        self::assertEquals($cursor, $new_cursor);
        $new_cursor_cache = $cache->get($cursor);
        self::assertEqualsCanonicalizing($different_page, $new_cursor_cache);
    }

    public static function resolve_cursor_data_provider(): array {
        return [
            'Empty test' => [
                'params_input' => [],
                'params_output' => ['limitfrom' => 0, 'maxcount' => -1, 'cursor' => null],
                'use_cursor' => false,
            ],
            'Invalid cursor test' => [
                'params_input' => ['limitfrom' => 22],
                'params_output' => ['limitfrom' => 0, 'maxcount' => -1, 'cursor' => null],
                'use_cursor' => false,
            ],
            'Tried to set maxcount test' => [
                'params_input' => ['maxcount' => 64],
                'params_output' => ['limitfrom' => 0, 'maxcount' => -1, 'cursor' => null],
                'use_cursor' => false,
            ],
            'Cursor test' => [
                'params_input' => ['limitfrom' => 22],
                'params_output' => ['limitfrom' => 22, 'maxcount' => -1, 'cursor' => 'cursor'],
                'use_cursor' => true,
            ],
            'Cursor test with maxcount' => [
                'params_input' => ['limitfrom' => 22, 'maxcount' => 64],
                'params_output' => ['limitfrom' => 22, 'maxcount' => 64, 'cursor' => 'cursor'],
                'use_cursor' => true,
            ],
            'Cursor test with no input' => [
                'params_input' => [],
                'params_output' => ['limitfrom' => 0, 'maxcount' => -1, 'cursor' => 'cursor'],
                'use_cursor' => true,
            ],
            'Cursor test with cursor input' => [
                'params_input' => ['cursor' => 'this is not a real cursor'],
                'params_output' => ['limitfrom' => 0, 'maxcount' => -1, 'cursor' => 'cursor'],
                'use_cursor' => true,
            ],
        ];
    }

    /**
     * @dataProvider resolve_cursor_data_provider
     */
    public function test_resolve_cursor($params_input, $params_output, $use_cursor): void {
        // Test requires a session cache.
        $cache = cache::make('totara_catalog', 'pagination_cursors');

        if ($use_cursor) {
            $cursor_input = [];
            $cursor_input['limitfrom'] = $params_input['limitfrom'] ?? 0;
            $cursor_input['maxcount'] = $params_input['maxcount'] ?? -1;
            $cursor = query_helper::create_cursor($cursor_input['limitfrom'], $cursor_input['maxcount']);
            $params_input['limitfrom'] = $cursor;
        }

        $test_params = query_helper::resolve_cursor($params_input);

        self::assertNotEmpty($test_params);
        foreach ($params_output as $key => $expected_value) {
            if ($expected_value === 'cursor') {
                $expected_value = $cursor;
            }
            self::assertEquals($expected_value, $test_params[$key], "mismatch on $key");
        }
    }
}
