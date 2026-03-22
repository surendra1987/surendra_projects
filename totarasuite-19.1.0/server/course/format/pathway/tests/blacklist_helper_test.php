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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package format_pathway
 */

use core_phpunit\testcase;
use format_pathway\blacklist_helper;

/**
 * @group format_pathway
 */
class format_pathway_blacklist_helper_test extends testcase {

    public function test_get_blacklist(): void {
        self::assertEquals(['wiki', 'book', 'folder', 'data', 'label', 'lesson'], blacklist_helper::get_blacklist());
    }

    public function test_is_blacklisted(): void {
        $modules = [
            'wiki' => true,
            'book' => true,
            'folder' => true,
            'data' => true,
            'label' => true,
            'quiz' => false,
            'facetoface' => false,
            'test_third_party_module' => false,
        ];
        foreach ($modules as $module => $truth) {
            self::assertEquals($truth, blacklist_helper::is_blacklisted($module));
        }
    }
}