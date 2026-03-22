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
 * @package    local
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use bi_intellidata\persistent\datatypeconfig;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\services\dbschema_service;
use bi_intellidata\services\config_service;
use bi_intellidata\testing\setup_helper;
use bi_intellidata\testing\test_helper;
use core_phpunit\testcase;

defined('MOODLE_INTERNAL') || die();


/**
 * Export methods test case.
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_optional_datatype_test extends testcase {

    public function setUp(): void {
        $this->setAdminUser();

        setup_helper::setup_tests_config();
    }

    /**
     * Test get all optional tables.
     *
     * @return void
     * @covers \bi_intellidata\services\dbschema_service::get_optional_tableslist
     */
    public function test_get_all_optional_table() {
        $userdatatype = 'user';
        $dbschemaservice = new dbschema_service();
        $alltables = $dbschemaservice->get_optional_tableslist();

        $this->assertIsArray($alltables);

        $this->assertArrayHasKey($userdatatype, $alltables);
    }

    /**
     * Test get all optional datatypes.
     *
     * @return void
     * @covers \bi_intellidata\services\datatypes_service::get_all_optional_datatypes
     */
    public function test_get_all_optional_datatype() {
        $alloptionaldatatypes = datatypes_service::get_all_optional_datatypes();

        $this->assertIsArray($alloptionaldatatypes);
    }

    /**
     * Test get optional table.
     *
     * @return void
     * @covers \bi_intellidata\services\datatypes_service::get_optional_table
     */
    public function test_format_optional_datatypes() {
        $dtservice = datatypes_service::class;

        $tablename = 'user';
        $datatype = $dtservice::generate_optional_datatype($tablename);
        $this->assertEquals($datatype, datatypeconfig::OPTIONAL_TABLE_PREFIX . $tablename);

        $table = $dtservice::get_optional_table($tablename);
        $this->assertEquals($tablename, $table);

        $alloptionaldatatypes = datatypes_service::get_all_optional_datatypes();

        $this->assertArrayHasKey($datatype, $alloptionaldatatypes);

        $this->assertIsArray($alloptionaldatatypes[$datatype]);

        $this->assertEquals($tablename, $alloptionaldatatypes[$datatype]['table']);
    }

    /**
     * Test get optional table.
     *
     * @return void
     * @covers \bi_intellidata\services\datatypes_service::get_optional_table
     */
    public function test_export_datatype() {
        $configservice = new config_service(datatypes_service::get_all_datatypes());
        $configservice->setup_config(false);

        $dbschemaservice = new dbschema_service();

        $exportdata = $dbschemaservice->export();
        $this->assertIsArray($exportdata);

        $datatype = datatypes_service::generate_optional_datatype('user');
        $this->assertArrayHasKey($datatype, $exportdata);

        $this->assertArrayHasKey('name', $exportdata[$datatype]);

        $this->assertArrayHasKey('fields', $exportdata[$datatype]);

        $this->assertIsBool($dbschemaservice->table_exists($datatype));
    }
}
