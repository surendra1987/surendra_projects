<?php
/**
 * This file is part of Totara Learn
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */


use bi_intellidata\repositories\statistics_repository;
use core\orm\query\builder;
use core_phpunit\testcase;

class bi_intellidata_statistics_repository_test extends testcase {

    /**
     * @return void
     */
    public function test_get_site_info(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $gen->create_course();
        $gen->create_course();

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $gen->get_plugin_generator('container_workspace');
        $workspace_generator->create_workspace();
        $workspace_generator->create_workspace();

        $course_number = builder::get_db()->count_records('course');

        // default site, 2 courses and 2 workspaces.
        $this->assertEquals(5, $course_number);
        $info = statistics_repository::get_site_info();
        // Only course included
        $this->assertEquals(2, $info['courses_count']);
    }
}