<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_mobile
 */

use totara_webapi\phpunit\webapi_phpunit_helper;
use core\webapi\execution_context;
use core_phpunit\testcase;
use totara_webapi\graphql;

/**
 * Tests the get_supported_offline_activities query resolver
 */
class totara_mobile_mobile_webapi_resolver_query_get_supported_offline_activities_test extends testcase {

    private const QUERY = 'totara_mobile_get_supported_offline_activities';

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_query_get_supported_offline_activities(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course();

        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);

        //no downloadable activity
        $gen->create_module('quiz', ['course' => $course]);
        $result = $this->resolve_graphql_query(self::QUERY, [
            'input' => [
                'course_id' => $course->id,
            ]
        ]);
        self::assertEmpty($result);
        //with downloadable activity
        $gen->create_module('label', ['course' => $course]);
        $gen->create_module('page', ['course' => $course]);
        $gen->create_module('certificate', ['course' => $course]);
        $gen->create_module('resource', ['course' => $course]);

        $result = $this->resolve_graphql_query(self::QUERY, [
            'input' => [
                'course_id' => $course->id,
            ]
        ]);

        foreach ($result as $row) {
            self::assertContains($row->type, ['page', 'label', 'certificate', 'resource']);
            self::assertNotEmpty($row->version);
            self::assertNotEmpty($row->cmid);
            self::assertNotEmpty($row->name);
            self::assertIsBool($row->enablecompletion);
        }
    }

    /**
     * @return void
     */
    public function test_mobile_query_get_supported_offline_activities(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course();

        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);

        //no downloadable activity
        $gen->create_module('quiz', ['course' => $course]);
        $result = graphql::execute_operation(
            execution_context::create('mobile', self::QUERY),
            [ 'input' => [
                'course_id' => $course->id,
            ]]
        );
        self::assertEmpty($result->toArray()['data']['activities']);
        //with downloadable activity
        $gen->create_module('scorm', ['course' => $course]);
        $result = graphql::execute_operation(
            execution_context::create('mobile', self::QUERY),
            [ 'input' => [
                'course_id' => $course->id,
            ]]
        );
        foreach ($result->toArray()['data']['activities'] as $row) {
            self::assertEquals('scorm', $row['type']);
            self::assertNotEmpty($row['version']);
            self::assertNotEmpty($row['cmid']);
            self::assertNotEmpty($row['name']);
            self::assertIsBool($row['enablecompletion']);
        }
    }
}
