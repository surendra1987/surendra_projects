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
 * Database repository migration test case.
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\repositories\database_repository;
use core_phpunit\testcase;

/**
 * Export_id_repository migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_database_repository_test extends testcase {

    /**
     * Test init() method.
     *
     * @return void
     * @covers \bi_intellidata\repositories\database_repository::init
     */
    public function test_init() {
        // Validate empty.
        $this->assertNull(database_repository::$encriptionservice);
        $this->assertNull(database_repository::$exportservice);
        $this->assertNull(database_repository::$exportlogrepository);
        $this->assertNull(database_repository::$writerecordslimits);

        database_repository::init();

        // Validate empty table.
        $this->assertInstanceOf('bi_intellidata\services\encryption_service', database_repository::$encriptionservice);
        $this->assertInstanceOf('bi_intellidata\services\export_service', database_repository::$exportservice);
        $this->assertInstanceOf('bi_intellidata\repositories\export_log_repository', database_repository::$exportlogrepository);
        $this->assertEquals(
            database_repository::$writerecordslimits,
            (int)SettingsHelper::get_setting('migrationwriterecordslimit')
        );
    }
}