<?php
/**
 * This file is part of Totara Core
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_catalog
 */

use core_phpunit\testcase;
use totara_catalog\usagedata\configuration;

class totara_catalog_usagedata_configuration_test extends testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        $results = (new configuration())->export();

        self::assertEquals('course,engage_article,certification,playlist,program,workspace', $results['included_items']);
        self::assertEquals(20, $results['page_size']);
        self::assertEquals('category', $results['browse_menu']);
        self::assertEquals('disabled', $results['featured_source']);
        self::assertFalse($results['details_content']);
        self::assertTrue($results['image_displayed']);
        self::assertEquals('catalog_learning_type_panel', $results['filters']);
    }
}