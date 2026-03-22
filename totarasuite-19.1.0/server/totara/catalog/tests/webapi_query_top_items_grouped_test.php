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

/**
 * @group totara_catalog
 */
class totara_catalog_webapi_query_top_items_grouped_test extends testcase {
    private const QUERY = 'totara_catalog_top_items_grouped';

    use webapi_phpunit_helper;

    public function test_get_top_items_grouped_query(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $gen->create_course();
        $gen->create_course();
        $gen->create_course();

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=text&itemstyle=narrow&objecttype=course']
            ]
        );

        self::assertEquals(1, count($result['groups']));

        $group = reset($result['groups']);
        self::assertEquals(3, count($group->items));
        self::assertEquals('course', $group->objecttype);

        foreach ($group->items as $item) {
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

        $programgenerator = $gen->get_plugin_generator('totara_program');
        $programgenerator->create_program();
        $programgenerator->create_program();

        $programgenerator->create_certification();
        $programgenerator->create_certification();

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=text&itemstyle=narrow&objecttype[]=course&objecttype[]=program']
            ]
        );

        self::assertEquals(2, count($result['groups']));

        foreach ($result['groups'] as $group) {
            if ($group->objecttype === 'course') {
                self::assertEquals(3, count($group->items));
            }

            if ($group->objecttype === 'program') {
                self::assertEquals(2, count($group->items));
            }
        }
    }

    public function test_get_more_than_five_top_items_grouped(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        for($i = 0; $i < 20; $i++) {
            $gen->create_course();
        }

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=text&itemstyle=narrow&perpageload=10&objecttype=course']
            ]
        );

        self::assertEquals(1, count($result['groups']));
        $group = reset($result['groups']);

        self::assertEquals(10, count($group->items));
        self::assertEquals('course', $group->objecttype);

        foreach ($group->items as $item) {
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
    }

    public function test_get_top_items_grouped_with_filters(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        for($i=0; $i<8; $i++) {
            $gen->create_course();
        }

        /** @var engage_article\testing\generator $article_generator */
        $article_generator = $gen->get_plugin_generator('engage_article');
        $article_generator->create_public_article();

        // Expected only course returned.
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_structure' => '{"itemstyle":"narrow", "catalog_learning_type_panel": ["course", "engage_article"], "objecttype":"engage_article" }']
            ]
        );

        self::assertEquals(1, count($result['groups']));
        $group = reset($result['groups']);
        self::assertEquals(1, count($group->items));
        self::assertEquals('engage_article', $group->objecttype);
    }

    public function test_top_items_grouped_with_incorrect_objectypes(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $gen->create_course();
        $gen->create_course();
        $gen->create_course();

        self::expectExceptionMessage("'incorrect' is not invalid objecttype or not enabled.");
        self::expectException(\totara_catalog\exception\items_query_exception::class);
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=text&itemstyle=narrow&objecttype[]=incorrect']
            ]
        );
    }

    public function test_top_items_grouped_with_no_requested_objectypes(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();

        $programgenerator = $gen->get_plugin_generator('totara_program');
        $programgenerator->create_program();
        $programgenerator->create_program();

        $programgenerator->create_certification();
        $programgenerator->create_certification();

        /** @var engage_article\testing\generator $article_generator */
        $article_generator = $gen->get_plugin_generator('engage_article');
        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $gen->get_plugin_generator('container_workspace');
        /** @var \totara_playlist\testing\generator $playlist_generator */
        $playlist_generator = $gen->get_plugin_generator('totara_playlist');

        for ($i = 0; $i < 10; $i++) {
            $gen->create_course();
            $playlist_generator->create_public_playlist();
            $workspace_generator->create_workspace();
            $article_generator->create_public_article();
        }

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['query_string' => 'orderbykey=text&itemstyle=narrow']
            ]
        );

        self::assertEquals(6, count($result['groups']));
        foreach ($result['groups'] as $group) {
            if ($group->objecttype === 'program') {
                self::assertEquals(2, count($group->items));
            } else if ($group->objecttype === 'certification') {
                self::assertEquals(2, count($group->items));
            } else {
                self::assertEquals(5, count($group->items));
            }
        }
    }
}