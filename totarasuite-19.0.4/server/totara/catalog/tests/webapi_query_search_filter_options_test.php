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
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_catalog\local\config;


/**
 * @group totara_catalog
 */
class totara_catalog_webapi_query_search_filter_options_test extends testcase {
    private const QUERY = 'totara_catalog_search_filter_options';

    use webapi_phpunit_helper;

    public function test_search_filter_options_with_exception(): void {
        self::setAdminUser();

        $this->expectException(\totara_catalog\exception\search_filter_options_exception::class);
        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'search_term' => "aaa",
                    'filter_key' => 'invalid'
                ]
            ]
        );
    }

    public function test_search_filter_options(): void {
        self::setAdminUser();

        config::instance()->update(['filters' => ['catalog_learning_type_panel' => 'Learning type', 'course_acttyp_panel' => 'Activity type', 'tag_panel_1' => 'Topics']]);

        $gen = self::getDataGenerator();
        $tags = [];
        for($i = 0; $i < 15; $i++) {
            if ($i < 5) {
                $gen->create_tag(['rawname' => 'matag' . $i]);
                $tags[] = 'matag' . $i;
            } else if ($i > 5 && $i < 8) {
                $gen->create_tag(['rawname' => 'ta' . $i]);
                $tags[] = 'ta' . $i;
            } else {
                $gen->create_tag(['rawname' => 'bala' . $i]);
                $tags[] = 'bala' . $i;
            }
        }

        $gen->create_course(['tags' => $tags]);
        $result = $this->resolve_graphql_query(self::QUERY,
            [
                'input' => [
                    'search_term' => "ta",
                    'filter_key' => 'tag_panel_1'
                ]
            ]
        );

        $expected = ["matag0", "matag1", "matag2", "matag3", "matag4", "ta6", "ta7"];
        self::assertSame($expected, $result['filter_options']);
    }
}