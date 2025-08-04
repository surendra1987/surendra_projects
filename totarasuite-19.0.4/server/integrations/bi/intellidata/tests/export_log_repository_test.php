<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Export_log_repository test case.
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

use bi_intellidata\repositories\export_log_repository;
use bi_intellidata\persistent\export_logs;
use core_phpunit\testcase;

/**
 * Export_logs_repository test case.
 *
 * @package    local
 * @subpackage intellidata
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_export_log_repository_test extends testcase {

    /**
     * Test save() method.
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @covers \bi_intellidata\repositories\export_log_repository::get_datatype
     */
    public function test_get_datatype() {
        $exportlogrepository = new export_log_repository();
        $this->assertInstanceOf('bi_intellidata\repositories\export_log_repository', $exportlogrepository);

        $datatypename = 'users';
        $exportlogrepository->reset_datatype($datatypename, export_logs::TABLE_TYPE_UNIFIED);

        // Validate existing datatype.
        $datatype = $exportlogrepository->get_datatype($datatypename);

        $this->assertInstanceOf('bi_intellidata\persistent\export_logs', $datatype);

        $this->assertEquals($datatypename, $datatype->get('datatype'));
        $this->assertEquals(export_logs::TABLE_TYPE_UNIFIED, $datatype->get('tabletype'));

        // Validate not existing datatype.
        $this->assertFalse(
            $exportlogrepository->get_datatype('notexistingdatatype')
        );
    }
}
