<?php
/**
 * This file is part of Totara Learn
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
 * @author  Gihan Hewaralalage <gihan.hewaralalage@totara.com>
 * @package core
 */
defined('MOODLE_INTERNAL') || die();

use totara_webapi\phpunit\webapi_phpunit_helper;

class core_webapi_resolver_query_categories_test extends \core_phpunit\testcase {
    private const QUERY = 'core_categories';

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_get_default_categories() {
        $this->setAdminUser();

        $result = $this->resolve_graphql_query(self::QUERY);
        self::assertCount(1,$result);
        self::assertEquals("Miscellaneous", reset($result)->name);
    }

    /**
     * @return void
     */
    public function test_get_categories() {
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $cat = $generator->create_category();

        $result = $this->resolve_graphql_query(self::QUERY);
        self::assertContains($cat->name, array_column($result, 'name'));
        self::assertCount(2,$result);
    }

    /**
     * @return void
     */
    public function test_get_visible_categories_as_user(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $cat_visible = $generator->create_category();
        $cat_not_visible = $generator->create_category((object)[
            "visible" => 0
        ]);

        // Login as user.
        $this->setUser($user);

        $result = $this->resolve_graphql_query(self::QUERY);

        // Should return only visible categories. (Default + 1)
        self::assertCount(2,$result);
        self::assertContains($cat_visible->name, array_column($result, 'name'));
        self::assertNotContains($cat_not_visible->name, array_column($result, 'name'));
    }
}