<?php
/*
 * This file is part of Totara Suite
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package totara_core
 */

use totara_core\version_specific\version_specific_feature;
use totara_core\version_specific\version_specific_setting_base;

class totara_core_version_specific_feature_test extends \core_phpunit\testcase {
    public function test_get_config_name() {
        $this->assertEquals(
            'totara_19_1_core_pizzabake',
            version_specific_feature::get_config_name('totara_19_1_core_pizzabake')
        );
    }

    /**
     * Dynamic list and the settings are ephemeral - they change from version to version.
     * But we can check that any that are here are working correctly.
     */
    public function test_get_available() {
        $available = version_specific_feature::get_available();
        foreach ($available as $class => $settingname) {
            $this->assertInstanceOf(version_specific_setting_base::class, new $class());
            $this->assertEquals($settingname, $class::get_setting_name());
        }
    }
}
