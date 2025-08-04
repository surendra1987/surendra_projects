<?php
/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\entity\goal_category as goal_category_entity;
use perform_goal\model\goal_category;

/** @var core_config $CFG */
global $CFG;
require_once($CFG->dirroot . '/perform/goal/db/upgradelib.php');

/**
 * Unit tests for upgradelib function perform_goal_create_basic_goal_category().
 *
 * @group perform_goal
 */
class perform_goal_upgrade_create_basic_goal_category_test extends testcase {

    public function test_create_basic_goal_category() {
        // Delete the installed goal_category.
        goal_category_entity::repository()->where('id_number', '=', 'system-basic')->delete();

        $id = perform_goal_create_basic_goal_category();
        $basic_category = goal_category::load_by_id($id);
        $this->assertEquals('basic', $basic_category->plugin_name);
        $this->assertEquals('Basic', $basic_category->name);
        $this->assertEquals('system-basic', $basic_category->id_number);
        $this->assertTrue($basic_category->active);
    }

    public function test_create_basic_goal_category_idempotent() {
        $basic_category_entity = goal_category_entity::repository()
            ->where('id_number', '=', 'system-basic')
            ->one(true);

        $id = perform_goal_create_basic_goal_category();
        $this->assertEquals($basic_category_entity->id, $id);
    }
}
