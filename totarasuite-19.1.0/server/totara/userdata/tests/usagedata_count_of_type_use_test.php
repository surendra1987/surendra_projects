<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package totara_userdata
 */

use core_phpunit\testcase;
use totara_userdata\usagedata\count_of_type_use;

class totara_userdata_usagedata_count_of_type_use_test extends testcase {
    /**
     * @throws dml_exception
     */
    public function test_export() {

        $generator_userdata = $this->getDataGenerator()->get_plugin_generator('totara_userdata');

        $generator_userdata->create_purge_type();
        $generator_userdata->create_purge_type();

        $generator_userdata->create_export_type();
        $generator_userdata->create_export_type();
        $generator_userdata->create_export_type();

        $results = (new count_of_type_use())->export();

        $this->assertEquals(2, $results['purge']);
        $this->assertEquals(3, $results['export']);
    }
}