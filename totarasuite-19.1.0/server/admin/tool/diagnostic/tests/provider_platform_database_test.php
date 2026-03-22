<?php
/*
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package tool_diagnostic
 */

use core_phpunit\testcase;
use tool_diagnostic\provider\platform_database;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_platform_database_test extends testcase {

    public function test_missing_whitelist(): void {
        $config = [
            'enabled' => true,
            'blacklist' => []
        ];

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("missing key 'dbconfig_whitelist' for provider platform_database");
        $provider = new platform_database($config);
        // Make code inspection happy.
        unset($provider);
    }

    public function test_static_data_with_empty_whitelist(): void {
        global $DB;

        $config = [
            'enabled' => true,
            'dbconfig_whitelist' => []
        ];
        $provider = new platform_database($config);
        $content_text = $provider->get_content()->get_data();

        $db_config = $DB->export_dbconfig();
        $this->assertStringContainsString('DB family:' . PHP_EOL . $DB->get_dbfamily(), $content_text);
        $this->assertStringContainsString('DB server info:' . PHP_EOL . json_encode($DB->get_server_info(), JSON_THROW_ON_ERROR), $content_text);
        $this->assertStringContainsString('DB type: ' . PHP_EOL . $db_config->dbtype, $content_text);
        $this->assertStringContainsString('DB library: ' . PHP_EOL . $db_config->dblibrary, $content_text);
        if ($DB->get_dbfamily() === 'mysql') {
            $db_collation = 'DB collation: ' . PHP_EOL . $DB->get_dbcollation();
        } else {
            $db_collation = 'DB collation: ' . PHP_EOL . 'na';
        }
        $this->assertStringContainsString($db_collation, $content_text);

        // Can't really test the db options as they can't be changed dynamically.
        $this->assertStringContainsString('DB Options: ' . PHP_EOL, $content_text);
    }
}
