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

use core\orm\query\builder;
use core_phpunit\testcase;
use totara_catalog\usagedata\content;

class totara_catalog_usagedata_content_test extends testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        $system_context = context_system::instance();
        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 1, 'objecttype' => 'course']);
        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 2, 'objecttype' => 'course']);
        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 3, 'objecttype' => 'course']);

        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 1, 'objecttype' => 'program']);
        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 2, 'objecttype' => 'program']);

        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 1, 'objecttype' => 'certification']);

        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 1, 'objecttype' => 'workspace']);

        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 1, 'objecttype' => 'playlist']);
        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 2, 'objecttype' => 'playlist']);

        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 1, 'objecttype' => 'engage_article']);
        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 2, 'objecttype' => 'engage_article']);
        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 3, 'objecttype' => 'engage_article']);

        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 1, 'objecttype' => 'xyz']);
        builder::table('catalog')->insert(['contextid' => $system_context->id, 'objectid' => 1, 'objecttype' => 'abc']);

        $results = (new content())->export();

        self::assertEquals(14, $results['total_items']);
        self::assertEquals(3, $results['courses']);
        self::assertEquals(2, $results['programs']);
        self::assertEquals(1, $results['certifications']);
        self::assertEquals(1, $results['workspaces']);
        self::assertEquals(2, $results['playlists']);
        self::assertEquals(3, $results['resources']);
        self::assertEquals(2, $results['other']);
    }
}