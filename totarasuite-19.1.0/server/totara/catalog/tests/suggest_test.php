<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @package totara_catalog
 */

defined('MOODLE_INTERNAL') || die();

use totara_catalog\local\config;
use totara_catalog\local\query_helper;
use totara_catalog\suggest\mock_suggest;
use totara_catalog\suggest\suggest_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Test for the suggest's did you mean feature
 */
class totara_catalog_suggest_test extends \core_phpunit\testcase {
    use webapi_phpunit_helper;
    private const QUERY = 'totara_catalog_items';

    /**
     * Tests the webapi returns the expected suggestions (when enabled)
     * @return void
     */
    public function test_suggest(): void {
        self::setAdminUser();

        $config = config::instance();

        $word = 'this is bad';
        $config->update(['suggest_plugin' => suggest_feature::SUGGEST_PLUGIN_NONE]);
        $structure_json = query_helper::json_encode(['catalog_fts' => $word]);
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'query_structure' => $structure_json
                ]
            ]
        );
        self::assertArrayNotHasKey('suggestion', $result);

        $plugin = str_replace('\\', '__', mock_suggest::class);
        $config->update(['suggest_plugin' => $plugin]);
        mock_suggest::$search = 'bad';
        mock_suggest::$replace = 'good';

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'query_structure' => $structure_json
                ]
            ]
        );
        self::assertArrayHasKey('suggestion', $result);
        self::assertEquals('this is good', $result['suggestion']);
    }

    public function test_invalid_plugin() {
        self::setAdminUser();
        $config = config::instance();
        $config->update(['suggest_plugin' => 'some_plugin_that_doesnt_exist']);
        // will also generate error log
        self::assertNull(suggest_feature::get_configured_plugin_instance(\core\session\manager::get_realuser()));
        $config->update(['suggest_plugin' => suggest_feature::SUGGEST_PLUGIN_NONE]);
        self::assertNull(suggest_feature::get_configured_plugin_instance(\core\session\manager::get_realuser()));
    }

    public function test_extract_lang() {
        $user = new stdClass();
        $user->lang = 'en';
        $user->country = 'nz';

        $info = suggest_feature::extract_lang($user);
        self::assertCount(2, $info);
        self::assertEquals('en', $info[0]);
        self::assertEquals('nz', $info[1]);

        $user->lang = 'fr_be';
        $user->country = 'nz';
        $info = suggest_feature::extract_lang($user);
        self::assertCount(2, $info);
        self::assertEquals('fr', $info[0]);
        self::assertEquals('be', $info[1]);

        $user->lang = '';
        $user->country = null;
        $info = suggest_feature::extract_lang($user);
        self::assertCount(2, $info);
        // CFG is inconsistently setup for PHPUnit, so can't test it reverts to that,
        // only that when the user's lang is null, it doesn't crash
    }
}