<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package format_pathwat
 */

use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;
use format_pathway\webapi\resolver\middleware\validate_format_pathway_course;

/**
 * @group format_pathway
 */
class format_pathway_webapi_resolver_middleware_validate_format_pathway_course_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_validate_format_pathway_course(): void {
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);
        $context = self::create_webapi_context('testing');
        $payload = payload::create(['course_id' => $course->id], $context);
        $middleware = new validate_format_pathway_course('course_id');

        $next = function (payload $payload): result {
            $course = $payload->get_variable('course');
            return new result($course);
        };

        $result = $middleware->handle($payload, $next);
        $actual_course = $result->get_data();

        self::assertEquals($course->id, $actual_course->id);
    }

    /**
     * @return void
     */
    public function test_invalid_course_exception(): void {
        $context = self::create_webapi_context('testing');
        $payload = payload::create(['course_id' => 55], $context);
        $middleware = new validate_format_pathway_course('course_id');

        $next = function (payload $payload): result {
            $course = $payload->get_variable('course');
            return new result($course);
        };

        self::expectException(moodle_exception::class);
        self::expectExceptionMessage(get_string('invalidcourse', 'error'));
        $middleware->handle($payload, $next);
    }

    /**
     * @return void
     */
    public function test_not_pathway_format_course_exception(): void {
        $course = self::getDataGenerator()->create_course();
         $context = self::create_webapi_context('testing');
        $payload = payload::create(['course_id' => $course->id], $context);
        $middleware = new validate_format_pathway_course('course_id');

        $next = function (payload $payload): result {
            $course = $payload->get_variable('course');
            return new result($course);
        };

        self::expectException(moodle_exception::class);
        self::expectExceptionMessage('The pathway course is required.');
        $middleware->handle($payload, $next);
    }
}