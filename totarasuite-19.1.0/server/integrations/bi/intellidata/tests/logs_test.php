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
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use bi_intellidata\persistent\logs;
use bi_intellidata\entities\users\user;
use core_phpunit\testcase;

/**
 * User migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_logs_test extends testcase {

    /**
     * Test create log.
     *
     * @covers \bi_intellidata\persistent\logs::create
     */
    public function test_create() {
        $context = context_system::instance();
        $datatype = user::TYPE;
        $component = 'bi_intellidata';

        // File details.
        $filerecord = [
            'component' => $component,
            'filearea' => $datatype,
            'contextid' => $context->id,
            'filepath' => '/',
            'filename' => 'logs_create.zip',
            'itemid' => 0
        ];

        $logdata = [
            'datatype'  => $datatype,
            'type'      => logs::TYPE_FILE_EXPORT,
            'action'    => logs::ACTION_CREATED,
            'details'   => json_encode($filerecord)
        ];

        // Create log.
        $log = new logs(0, $logdata);
        $log->save();

        // Validate record exists.
        $this->assertEquals(true, logs::record_exists($log->get('id')));

        // Compare record data.
        $logrecord = new logs($log->get('id'));
        $this->assertEquals($log->get('id'), $logrecord->get('id'));
        $this->assertEquals($logdata['datatype'], $logrecord->get('datatype'));
        $this->assertEquals($logdata['type'], $logrecord->get('type'));
        $this->assertEquals($logdata['action'], $logrecord->get('action'));
        $this->assertEquals($logdata['details'], $logrecord->get('details'));
    }
}