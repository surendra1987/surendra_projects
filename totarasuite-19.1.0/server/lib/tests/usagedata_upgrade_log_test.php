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
 * @package core
 */

use core\usagedata\upgrade_log;
use core_phpunit\testcase;

class core_usagedata_upgrade_log_test extends testcase {

    /**
     * @throws dml_exception
     */
    public function test_export() {
        global $DB;

        $data = [
            'type' => 0,
            'plugin' => 'core',
            'timemodified' => time(),
            'version' => 123,
            'targetversion' => 246,
            'info' => 'Some information',
            'userid' => 0,
        ];

        $inserted_upgrade_log_id = (string) $DB->insert_record('upgrade_log', $data);

        $export_result = (new upgrade_log())->export();

        $test_upgrade_record = null;
        foreach ($export_result as $upgrade_log_record) {
            if ($upgrade_log_record['id'] !== $inserted_upgrade_log_id) {
                continue;
            }

            $test_upgrade_record = $upgrade_log_record;
        }

        $this->assertNotNull($test_upgrade_record);

        $this->assertEquals($data['type'], $test_upgrade_record['type']);
        $this->assertEquals($data['plugin'], $test_upgrade_record['plugin']);
        $this->assertEquals($data['timemodified'], $test_upgrade_record['timemodified']);
        $this->assertEquals($data['version'], $test_upgrade_record['version']);
        $this->assertEquals($data['targetversion'], $test_upgrade_record['targetversion']);
        $this->assertEquals($data['info'], $test_upgrade_record['info']);
    }
}