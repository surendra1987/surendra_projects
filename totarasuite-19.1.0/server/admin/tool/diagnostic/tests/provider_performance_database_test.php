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
 * @package tool_diagnostic
 */

use core_phpunit\testcase;
use tool_diagnostic\provider\performance_database;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_performance_database_test extends testcase {

    public function test_content(): void {
        $provider = new performance_database(["enabled" => true]);
        $data = $provider->get_content()->get_data();

        $this->assertStringContainsString('Duration:', $data);
        $this->assertStringContainsString('Record count:', $data);
        $this->assertStringContainsString('Speed:', $data);
    }

    public function test_create_temp_table(): void {
        global $DB;

        $dbman = $DB->get_manager();
        $this->assertFalse($dbman->table_exists('performance_database_temp'));
        $provider = new performance_database(["enabled" => true]);
        $reflection = new ReflectionMethod($provider, 'create_temp_table');
        $reflection->setAccessible(true);
        $reflection->invoke($provider);

        $this->assertTrue($dbman->table_exists('performance_database_temp'));
    }

    public function test_drop_temp_table(): void {
        global $DB;

        $dbman = $DB->get_manager();
        $provider = new performance_database(["enabled" => true]);
        $reflection = new ReflectionMethod($provider, 'create_temp_table');
        $reflection->setAccessible(true);
        $reflection->invoke($provider);

        $this->assertTrue($dbman->table_exists('performance_database_temp'));
        $reflection = new ReflectionMethod($provider, 'drop_temp_table');
        $reflection->setAccessible(true);
        $reflection->invoke($provider);

        $this->assertFalse($dbman->table_exists('performance_database_temp'));
    }

    public function test_write_records(): void {
        global $DB;

        $dbman = $DB->get_manager();
        $provider = new performance_database(["enabled" => true]);
        $reflection = new ReflectionMethod($provider, 'create_temp_table');
        $reflection->setAccessible(true);
        $reflection->invoke($provider);

        $this->assertTrue($dbman->table_exists('performance_database_temp'));

        $reflection = new ReflectionMethod($provider, 'write_records');
        $reflection->setAccessible(true);
        $data = $reflection->invoke($provider);

        $this->assertStringContainsString('Duration:', $data);
        $this->assertStringContainsString('Record count:', $data);
        $this->assertStringContainsString('Speed:', $data);

        $this->assertTrue($dbman->table_exists('performance_database_temp'));
        $reflection = new ReflectionMethod($provider, 'drop_temp_table');
        $reflection->setAccessible(true);
        $reflection->invoke($provider);
    }
}
