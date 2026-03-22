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
 * @package totara_core
 */


use core\orm\query\builder;

class totara_core_usagedata_menu_test extends \core_phpunit\testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        builder::table('totara_navigation')->update_record(['id' => 16, 'classname' => '\totara_core\totara\menu\home', 'icon' => 'custom']);

        $results = (new \totara_core\usagedata\menu())->export();
        $this->assertEquals(34, $results['total_menu_items']);
        $this->assertEquals(0, $results['visibility_rules_items']);
        $this->assertEquals(0, $results['max_visibility_rules']);
        $this->assertEquals(1, $results['custom_icon_items']);
        $this->assertEquals(15, $results['max_title_length']);
        $this->assertEquals(13, count($results['active_classnames']));
        $classnames = [
            '\totara_core\totara\menu\home',
            '\container_workspace\totara\menu\your_spaces',
            '\totara_core\totara\menu\learn',
            '\totara_core\totara\menu\myreports',
            '\totara_core\totara\menu\perform',
            '\totara_engage\totara\menu\library',
            '\mod_approval\totara\menu\dashboard',
            '\mod_perform\totara\menu\my_activities',
            '\totara_catalog\totara\menu\findlearning',
            '\totara_competency\totara\menu\my_competencies',
            '\totara_evidence\totara\menu\my_evidence',
            '\totara_hierarchy\totara\menu\mygoals',
            '\totara_plan\totara\menu\recordoflearning'
        ];

        foreach ($results['active_classnames'] as $classname) {
            self::assertTrue(in_array($classname, $classnames));
        }
    }
}