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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package usagedata
 */

use core_phpunit\testcase;
use totara_job\job_assignment;
use totara_job\usagedata\configuration_count;

class totara_job_usagedata_configuration_count_test extends testcase {
    public function test_export() {
        $generator = $this->getDataGenerator();
        $users = [];
        $job_assignment_data = [
            [
                'fullname' => null,
                'shortname' => 't#0',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => 'this is test #1',
                'shortname' => null,
                'startdate' => time(),
                'enddate' => null,
                'positionid' => 1,
                'organisationid' => null,
                'appraiserid' => 1
            ],
            [
                'fullname' => 'this is test #2',
                'shortname' => 't#2',
                'startdate' => null,
                'enddate' => time(),
                'positionid' => null,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => 'this is test #3',
                'shortname' => 't#3',
                'startdate' => null,
                'enddate' => null,
                'positionid' => null,
                'organisationid' => null,
                'appraiserid' => null
            ],
            [
                'fullname' => 'this is test #4',
                'shortname' => null,
                'startdate' => null,
                'enddate' => null,
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#5',
                'startdate' => null,
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => 'this is test #6',
                'shortname' => 't#6',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => null,
                'organisationid' => null,
                'appraiserid' => null
            ],
            [
                'fullname' => null,
                'shortname' => 't#7',
                'startdate' => time(),
                'enddate' => null,
                'positionid' => null,
                'organisationid' => null,
                'appraiserid' => null
            ],
            [
                'fullname' => null,
                'shortname' => 't#8',
                'startdate' => null,
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#9',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => 'this is test #10',
                'shortname' => 't#10',
                'startdate' => time(),
                'enddate' => null,
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#11',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#12',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => 'this is test #13',
                'shortname' => 't#13',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#14',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#15',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#16',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#17',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#18',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ],
            [
                'fullname' => null,
                'shortname' => 't#19',
                'startdate' => time(),
                'enddate' => time(),
                'positionid' => 1,
                'organisationid' => 1,
                'appraiserid' => 1
            ]
        ];

        // Create number of users according to the definition
        for ($i=0; $i<count($job_assignment_data); $i++) {
            $users[$i] = $generator->create_user();
            // Create job assignments for each user
            job_assignment::create_default($users[$i]->id, $job_assignment_data[$i]);
        }

        $expect = [
            'total' => count($job_assignment_data),
            'with_fullname' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['fullname']) ? $carry : $carry + 1;
            }, 0),
            'with_shortname' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['shortname']) ? $carry : $carry + 1;
            }, 0),
            'with_idnumber' => count($job_assignment_data),
            'with_startdate' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['startdate']) ? $carry : $carry + 1;
            }, 0),
            'with_enddate' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['enddate']) ? $carry : $carry + 1;
            }, 0),
            'with_position' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['positionid']) ? $carry : $carry + 1;
            }, 0),
            'with_organisation' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['organisationid']) ? $carry : $carry + 1;
            }, 0),
            'with_manager' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['managerjaid']) ? $carry : $carry + 1;
            }, 0),
            'with_tempmanager' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['tempmanagerjaid']) ? $carry : $carry + 1;
            }, 0),
            'with_appraiser' => array_reduce($job_assignment_data, function($carry, $item) {
                return empty($item['appraiserid']) ? $carry : $carry + 1;
            }, 0),
        ];

        $data = (new configuration_count())->export();

        foreach ($data as $key => $value) {
            $this->assertEquals($expect[$key], $value);
        }
    }
}