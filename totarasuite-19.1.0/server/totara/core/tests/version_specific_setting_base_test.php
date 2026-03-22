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

use totara_core\advanced_feature;
use totara_core\version_specific\version_specific_setting_base;

/**
 * Testing the public static methods that are testable on the version-specific setting base class.
 */
class totara_core_version_specific_setting_base_test extends \core_phpunit\testcase {
    public function test_get_component_name() {
        $this->assertEquals('totara_core', version_specific_setting_base::get_component_name());
    }

    public function test_get_base_name() {
        $this->assertEquals('version_specific_setting_base', version_specific_setting_base::get_base_name());
    }

    public function test_get_default() {
        $this->assertEquals(advanced_feature::DISABLED, version_specific_setting_base::get_default());
    }

    public function test_get_nextmajor_default() {
        $this->assertEquals(advanced_feature::ENABLED, version_specific_setting_base::get_nextmajor_default());
    }

    public function test_require_purge_caches() {
        $this->assertFalse(version_specific_setting_base::require_purge_caches());
    }
}
