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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */
defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use engage_course\totara_engage\resource\course;

class engage_course_create_course_test extends testcase {
    /**
     * @return void
     */
    public function test_create_course_resource(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        $this->setUser($user);

        $data = [
            'name' => "Hello world",
            'instanceid' => $course->id
        ];

        $resourcetype = course::get_resource_type();
        $resource = course::create($data);

        $sql = '
            SELECT 1 FROM "ttr_engage_resource" er
            WHERE er.instanceid = :instanceid AND er.resourcetype = :type
        ';

        $params = [
            'instanceid' => $resource->get_instanceid(),
            'type' => $resourcetype
        ];

        $this->assertTrue($DB->record_exists_sql($sql, $params));
    }
    public function test_create_course_failed(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $data = [
            'name' => "Hello world",
        ];

        try {
            course::create($data);
        } catch (coding_exception $ex) {
            $this->assertStringContainsString('Course not found!', $ex->getMessage());
        }
    }
}