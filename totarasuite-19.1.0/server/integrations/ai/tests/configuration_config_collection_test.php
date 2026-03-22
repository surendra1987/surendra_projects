<?php
/*
 * This file is part of Totara Talent Experience Platform
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

use core_ai\configuration\config_collection;
use core_ai\configuration\option;
use core_phpunit\testcase;

/**
 * @group ai_integrations
 */
class core_ai_configuration_config_collection_test extends testcase {

    public function test_json_serialize_hides_secure_options() {
        $setting_one = new admin_setting_configtext('test_name_1', 'AI test', '', '');
        $setting_one->write_setting('value 1');
        $setting_two = new admin_setting_configtext('test_name_2', 'AI test 2', '', '');
        $setting_two->write_setting('value 2');
        $config_collection = new config_collection([
            new option($setting_one),
            new option($setting_two, true),
        ]);

        // secure values are not included in a json serialize
        $this->assertEquals(
            json_encode([
                'test_name_1' => 'value 1'
            ]),
            json_encode($config_collection)
        );
    }

    public function test_creating_config_collection() {
        $option_mock = $this->createMock(option::class);
        $option_mock->method('get_admin_setting')->willReturn($this->createMock(admin_setting::class));
        $config_collection = new config_collection([$option_mock]);

        // test has options
        $this->assertTrue($config_collection->has_options());
    }

    public function test_has_options() {
        $option_mock = $this->createMock(option::class);
        $admin_setting_mock = $this->createMock(admin_setting::class);
        $option_mock->method('get_admin_setting')->willReturn($admin_setting_mock);
        $config_collection = new config_collection([$option_mock]);

        // test has options
        $this->assertTrue($config_collection->has_options());

        // Test has no options
        $this->assertFalse((new config_collection([]))->has_options());
    }

    public function test_add_to_settings_page() {
        $setting_one = new admin_setting_configtext('test_name_1', 'AI test', '', '');
        $setting_two = new admin_setting_configtext('test_name_2', 'AI test 2', '', '');
        $config_collection = new config_collection([
            new option($setting_one),
            new option($setting_two, true),
        ]);

        $setting_page = new admin_settingpage('test_page','Test page for AI settings');
        $config_collection->add_to_settings_page($setting_page);

        $this->assertEquals($setting_one->name, $setting_page->settings->test_name_1->name);
        $this->assertEquals($setting_two->name, $setting_page->settings->test_name_2->name);
    }

    public function test_get_value() {
        $admin_setting = new admin_setting_configtext('test_name', 'AI test', '', '');
        $admin_setting->write_setting('test_value');
        $option = new option($admin_setting);

        $config_collection = new config_collection([$option]);
        $this->assertEquals('test_value', $config_collection->get_value('test_name'));
    }
}

