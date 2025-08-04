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
 * @package totara_reportbuilder
 */

use core_phpunit\testcase;
use totara_reportbuilder\usagedata\count_reports_by_source;

class totara_reportbuilder_usagedata_count_reports_by_source_test extends testcase {

    /**
     * @throws dml_exception
     */
    public function test_export() {
        global $DB;

        $generator_report_builder = $this->getDataGenerator()->get_plugin_generator('totara_reportbuilder');

        // user (total: 3)
        $generator_report_builder->create_default_custom_report(['source' => 'user', 'shortname' => 'test_1']);
        $generator_report_builder->create_default_custom_report(['source' => 'user', 'shortname' => 'test_2']);
        $generator_report_builder->create_default_custom_report(['source' => 'user', 'shortname' => 'test_3']);

        // courses (total: 1)
        $generator_report_builder->create_default_custom_report(['source' => 'courses', 'shortname' => 'test_4']);

        // reports (total: 1, embedded doesn't count)
        $generator_report_builder->create_default_custom_report(['source' => 'reports', 'shortname' => 'test_5']);

        // Create an embedded report
        $report_id = $generator_report_builder->create_default_custom_report(['source' => 'reports', 'shortname' => 'test_6']);
        $DB->update_record('report_builder', [
           'id' => $report_id,
           'embedded' => 1
        ]);

        $results = (new count_reports_by_source())->export();

        $this->assertEquals(3, $results['user']);
        $this->assertEquals(1, $results['courses']);
        $this->assertEquals(1, $results['reports']);
    }
}