<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_mobile
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use core\webapi\execution_context;
use totara_webapi\graphql;
use totara_webapi\phpunit\webapi_phpunit_helper;


class totara_mobile_mobile_webapi_resolver_query_download_course_test extends testcase {

    private const QUERY = 'totara_mobile_download_course';

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_download_course_persistent_query(): void {
        self::setAdminUser();
        [$course, $label] = $this->create_test_data();

        $result = graphql::execute_operation(
            execution_context::create('mobile', self::QUERY),
            ['input' => ['course_id' => $course->id]]
        );

        $data = $result->data['content'];
        self::assertEquals($course->id, $data['id']);
        self::assertEquals($course->fullname, $data['fullname']);
        self::assertEquals($course->shortname, $data['shortname']);
        self::assertEquals($course->summary, $data['summary']);
        self::assertEmpty($data['attachments']);
        self::assertEquals($course->lang, $data['lang']);
        self::assertNotEmpty($data['startdate']);
        self::assertEmpty($data['enddate']);
        self::assertNotEmpty(6, count($data['sections']));
        self::assertIsBool($data['showGrades']);
        self::assertIsBool($data['completionEnabled']);
        self::assertEquals('All', $data['criteriaaggregation']);
        self::assertEmpty($data['criteria']);
        self::assertNotEmpty($data['completion']);
        // Created modules in first section
        $section = reset($data['sections']);
        foreach ($section['modules'] as $module) {
            if (in_array($module['modtype'], ['scorm', 'label'])) {
                self::assertTrue($module['downloadable']);
            } else {
                self::assertFalse($module['downloadable']);
            }
        }
    }

    /**
     * @return void
     */
    public function test_download_course_query_resolver(): void {
        self::setAdminUser();
        [$course, $label] = $this->create_test_data();

        $result = $this->resolve_graphql_query(self::QUERY,
            ['input' => ['course_id' => $course->id]]
        );

        self::assertInstanceOf(\core_course\model\course::class, $result);
    }

    /**
     * @return void
     */
    public function test_download_with_course_not_mobile_friendly(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $course = $gen->create_course();
        self::expectExceptionMessage('This course is not compatible with this mobile friendly course.');
        self::expectException(moodle_exception::class);
        $this->resolve_graphql_query(self::QUERY,
            ['input' => ['course_id' => $course->id]]
        );
    }

    /**
     * @return void
     */
    public function test_download_not_enrolled(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);
        [$course, $label] = $this->create_test_data();
        self::expectExceptionMessage('Course or activity not accessible. (Not enrolled)');
        self::expectException(require_login_exception::class);
        $this->resolve_graphql_query(self::QUERY,
            ['input' => ['course_id' => $course->id]]
        );
    }

    /**
     * @return array
     */
    private function create_test_data(): array {
        global $DB;

        $gen = self::getDataGenerator();
        $course = $gen->create_course();
        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);
        $label = $gen->create_module('label', ['course' => $course, 'name' => "it's label", 'intro' => "it's label"]);
        $scorm = $gen->create_module('scorm', ['course' => $course]);
        $forum = $gen->create_module('forum', ['course' => $course]);
        return [$course, $label, $scorm];
    }
}