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
 * @author Scott Davies <scott.davies@totara.com>
 * @package bi_intellidata
 */


use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\testing\generator as bi_intellidata_generator;

/**
 * Unit tests for the require_plugin_enabled middleware class.
 * @group bi_intellidata
 */
class bi_intellidata_webapi_middleware_require_plugin_enabled_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_with_plugin_enabled(): void {
        // Set up.
        $original_enabled_setting = SettingsHelper::get_setting('enabled');
        $generator = bi_intellidata_generator::instance();
        [$encryptionkey, $clientidentifier] = $generator->create_credentials();
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        SettingsHelper::set_setting('enabled', '1');

        // Operate.
        $result = $this->resolve_graphql_query('bi_intellidata_validate_credentials',
            ['input' => ['code' => $clientidentifier]]
        );

        /// Assert.
        self::assertEquals('ok', $result['data']);

        // Tear down.
        SettingsHelper::set_setting('enabled', $original_enabled_setting);
    }

    /**
     * @return void
     */
    public function test_with_plugin_disabled(): void {
        // Set up.
        $original_enabled_setting = SettingsHelper::get_setting('enabled');
        $generator = bi_intellidata_generator::instance();
        [$encryptionkey, $clientidentifier] = $generator->create_credentials();
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);
        SettingsHelper::set_setting('enabled', '0');

        // Operate.
        try {
            $result = $this->resolve_graphql_query('bi_intellidata_validate_credentials',
                ['input' => ['code' => $clientidentifier]]
            );
        } catch (moodle_exception $exc) {
            // Assert.
            self::assertSame('The IntelliData plugin is not enabled.', $exc->getMessage());
        }

        // Tear down.
        SettingsHelper::set_setting('enabled', $original_enabled_setting);
    }
}
