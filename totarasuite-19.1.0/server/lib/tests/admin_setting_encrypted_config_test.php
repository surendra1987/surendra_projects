<?php
/*
 * This file is part of Totara Talent Experience Platform
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core
 */

use core_phpunit\testcase;

class core_admin_setting_encrypted_config_test extends testcase {

    public static function encrypted_options(): array {
        return [
            ['use_plugin' => true],
            ['use_plugin' => false],
        ];
    }

    /**
     * @dataProvider encrypted_options
    */
    public function test_writing_and_reading_setting(bool $use_plugin) {
        global $DB;
        $config = new admin_setting_encryptedconfig('unit', 'unit test', 'unit test description', '');

        // test plugin save
        if ($use_plugin) {
            $config->plugin = 'unit_test';
        }
        $config->write_setting('encrypted_value');
        $saved_value = $config->get_setting();
        $this->assertEquals('encrypted_value', $saved_value);

        // Check the database value.
        $raw_value = $use_plugin
            ? $DB->get_field('config_plugins', 'value', ['name' => 'unit', 'plugin' => 'unit_test'])
            : $DB->get_field('config', 'value', ['name' => 'unit']);
        $this->assertNotEquals('encrypted_value', $raw_value);

        // Update the value
        $config->write_setting('encrypted_value_2');
        $saved_value = $config->get_setting();
        $this->assertEquals('encrypted_value_2', $saved_value);

        // Check the database value.
        $raw_value = $use_plugin
            ? $DB->get_field('config_plugins', 'value', ['name' => 'unit', 'plugin' => 'unit_test'])
            : $DB->get_field('config', 'value', ['name' => 'unit']);
        $this->assertNotEquals('encrypted_value_2', $raw_value);

        // Delete value
        $config->write_setting(null);
        $saved_value = $config->get_setting();
        $this->assertNull($saved_value);

        // Check the record is deleted.
        $raw_value = $use_plugin
            ? $DB->get_field('config_plugins', 'value', ['name' => 'unit', 'plugin' => 'unit_test'])
            : $DB->get_field('config', 'value', ['name' => 'unit']);
        $this->assertFalse($raw_value);
    }
}
