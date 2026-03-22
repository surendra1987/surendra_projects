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
 * @package mod_certificate
 */

use core\webapi\execution_context;
use core_phpunit\testcase;
use totara_webapi\graphql;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Query download certificate resolver unit tests
 */
class mod_certificate_webapi_resolver_query_download_test extends testcase {
    private const QUERY = 'mod_certificate_download';
    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_download_query(): void {
        self::setAdminUser();
        [$course, $certificate] = $this->create_test_data();

        $result = $this->resolve_graphql_query(self::QUERY,
            ['input' => ['cm_id' => $certificate->cmid]]
        );
        self::assertNotEmpty($result->name);
        self::assertNotEmpty($result->intro);
        self::assertNotEmpty($result->introformat);
        self::assertEmpty($result->showdescription);
        self::assertNotEmpty($result->id);
        self::assertNotEmpty($result->completion);
        self::assertNotEmpty($result->completionstatus);
        self::assertIsBool($result->completionenabled);
        self::assertEmpty($result->certificate_issues);
        self::assertIsInt($result->downloadsize);
    }

    /**
     * @return void
     */
    public function test_download_persistent_query(): void {
        self::setAdminUser();
        [$course, $certificate] = $this->create_test_data();

        $filerecord = array(
            'contextid' => context_module::instance($certificate->cmid)->id,
            'component' => 'mod_certificate',
            'filearea' => 'intro',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => "file.txt",
        );
        $fs = get_file_storage();

        $fs->create_file_from_string($filerecord, 'certificate contents');

        $result = graphql::execute_operation(
            execution_context::create('mobile', self::QUERY),
            ['input' => ['cm_id' => $certificate->cmid]]
        );

        $data = $result->data['mod_certificate_download'];
        self::assertNotEmpty($data['name']);
        self::assertNotEmpty($data['intro']);
        self::assertNotEmpty($data['introformat']);
        self::assertEmpty($data['certificate_issues']);
        self::assertCount(1, $data['attachments']);
        self::assertStringContainsString('file.txt', $data['attachments'][0]['file_url']);
        self::assertGreaterThan(0, $data['attachments'][0]['file_size']);
    }

    /**
     * @return void
     */
    public function test_download_with_invalid_cm_id(): void {
        self::setAdminUser();

        self::expectExceptionMessage('Invalid course module ID');
        self::expectException(moodle_exception::class);
        $this->resolve_graphql_query(self::QUERY,
            ['input' => ['cm_id' => 6]]
        );
    }

    /**
     * @return void
     */
    public function test_download_with_course_not_mobile_friendly(): void {
        self::setAdminUser();

        $gen = self::getDataGenerator();
        $course = $gen->create_course();
        $certificate = $gen->create_module('certificate', ['course' => $course]);
        self::expectExceptionMessage('This course is not compatible with this mobile friendly course.');
        self::expectException(moodle_exception::class);
        $this->resolve_graphql_query(self::QUERY,
            ['input' => ['cm_id' => $certificate->cmid]]
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
        $certificate = $gen->create_module('certificate', ['course' => $course, 'name' => "it's certificate", 'intro' => "it's certificate"]);

        return [$course, $certificate];
    }
}