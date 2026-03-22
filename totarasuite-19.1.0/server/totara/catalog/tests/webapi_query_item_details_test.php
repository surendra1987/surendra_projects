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
 * @author Simon Chester <simon.chester@totara.com>
 * @package totara_catalog
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group totara_catalog
 */
class totara_catalog_webapi_query_item_details_test extends testcase {
    private const QUERY = 'totara_catalog_item_details';

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_query_details(): void {
        global $DB;
        static::setAdminUser();
        $gen = static::getDataGenerator();
        $course = $gen->create_course();

        $record = $DB->get_record('catalog', ['objecttype' => 'course', 'objectid' => $course->id], 'id');

        $result = $this->resolve_graphql_query(static::QUERY,
            [
                'input' => ['itemid' => $record->id]
            ]
        );

        $details = $result['details'];
        $this->assertNotEmpty($details->title);
        $this->assertNotEmpty($details->manage_link);
        $this->assertNotEmpty($details->manage_link->url);
        $this->assertNotEmpty($details->manage_link->label);
        $this->assertNotEmpty($details->details_link);
        $this->assertNotEmpty($details->details_link->description);
        $this->assertNotEmpty($details->details_link->button->url);
        $this->assertNotEmpty($details->details_link->button->label);
        $this->assertNotEmpty($details->text_placeholders);
    }

    /**
     * @return void
     */
    public function test_query_details_invalid_itemid(): void {
        static::setAdminUser();

        self::expectExceptionMessage('Item not found.');
        self::expectException(totara_catalog\exception\items_query_exception::class);
        $result = $this->resolve_graphql_query(static::QUERY,
            [
                'input' => ['itemid' => 999]
            ]
        );
    }

    /**
     * @return void
     */
    public function test_query_details_without_access(): void {
        global $DB;
        static::setAdminUser();
        $gen = static::getDataGenerator();
        /** @var engage_article\testing\generator $article_generator */
        $article_generator = $gen->get_plugin_generator('engage_article');
        $article = $article_generator->create_article();

        $record = $DB->get_record('catalog', ['objecttype' => 'engage_article', 'objectid' => $article->get_instanceid()], 'id');


        // Login as other user
        $user = $gen->create_user();
        self::setUser($user);
        self::expectExceptionMessage('Tried to access data without permission');
        self::expectException(totara_catalog\exception\items_query_exception::class);
        $result = $this->resolve_graphql_query(static::QUERY,
            [
                'input' => ['itemid' => $record->id]
            ]
        );
    }
}