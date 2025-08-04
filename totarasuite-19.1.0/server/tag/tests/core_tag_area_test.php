<?php
/**
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package core_tag
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;

class core_tag_core_tag_area_test extends testcase {

    public function test_get_default_collection_for(): void {
        global $DB;
        $default_tag_collection_id = core_tag_collection::get_default();

        // Random component just returns the default.
        self::assertEquals($default_tag_collection_id, core_tag_area::get_default_collection_for('xyz'));

        // An engage component will return the shared tag collection ID of all the other engage components.
        $DB->execute("
            UPDATE {tag_area}
               SET tagcollid = 123
             WHERE component in ('engage_article', 'container_workspace', 'engage_survey')
         ");
        $topic_tag_collection_id = core_tag_collection::get_default();
        self::assertEquals(123, core_tag_area::get_default_collection_for('container_workspace'));

        // An engage component will return the default when there is are no existing engage components.
        $DB->delete_records('tag_area');
        self::assertEquals($default_tag_collection_id, core_tag_area::get_default_collection_for('container_workspace'));
    }
}
