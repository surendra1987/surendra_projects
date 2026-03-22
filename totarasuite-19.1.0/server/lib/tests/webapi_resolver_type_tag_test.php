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
 * @package core
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;
use core_tag\model\tag;


/**
 * @group core_tag
 */
class core_webapi_resolver_type_tag_test extends testcase {

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_tag_is_not_enabled(): void {
        set_config('usetags', 0);

        $tags = core_tag_tag::create_if_missing(1, ['rawname' => 'tag1'], true);
        $tag = reset($tags);
        $model = tag::load_by_id($tag->id);

        self::expectExceptionMessage('Tag feature is not enabled.');
        self::expectException(moodle_exception::class);
        $result = $this->resolve_graphql_type(
            'core_tag',
            'name',
            $model,
        );
    }

    /**
     * @return void
     */
    public function test_type_name(): void {
        $tags = core_tag_tag::create_if_missing(1, ['rawname' => 'tag1'], true);
        $tag = reset($tags);
        $model = tag::load_by_id($tag->id);

        $result = $this->resolve_graphql_type(
            'core_tag',
            'name',
            $model,
        );

        self::assertEquals('tag1', $result);
    }


    /**
     * @return void
     */
    public function test_type_rawname(): void {
        $tags = core_tag_tag::create_if_missing(1, ['rawname' => 'raw_tage'], true);
        $tag = reset($tags);
        $model = tag::load_by_id($tag->id);

        $result = $this->resolve_graphql_type(
            'core_tag',
            'rawname',
            $model,
        );

        self::assertEquals('raw_tage', $result);
    }
}