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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package core
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \core\webapi\resolver\query\course_list
 *
 * @group core_user
 */
class core_webapi_query_course_list_test extends testcase {

    private const QUERY = 'core_course_list';

    use webapi_phpunit_helper;

    /**
     * Create some course data to test the query.
     */
    private static function faux_course_data(): array {
        $course_info = [
            'alpha one' => 'Course Apple One',
            'alpha two' => 'Course Apple two',
            'alpha three' => 'Course Apple Three',
            'beta one' => 'Course Banana One',
            'beta two' => 'Course Banana Two',
            'beta three' => 'Course Banana Three',
            'gamma one' => 'Course Grape One',
            'gamma two' => 'Course Grape Two',
            'gamma three' => 'Course Grape Three',
            'omega' => 'Owesome',
        ];

        $courses = [];
        foreach ($course_info as $shortname => $fullname) {
            $courses[] = self::getDataGenerator()->create_course([
                'shortname' => $shortname,
                'fullname' => $fullname
            ]);
        }

        return $courses;
    }

    /**
     * Test the functionality of the course selector graphql without login.
     */
    public function test_webapi_query_course_list_nologin() {
        $this->expectException(\require_login_exception::class);
        $this->expectExceptionMessage('Course or activity not accessible. (You are not logged in)');


        $items = $this->resolve_graphql_query(self::QUERY, []);
    }

    /**
     * Test the functionality of the course selector graphql with no courses.
     */
    public function test_webapi_query_course_list_empty() {
        $this->setAdminUser();

        $result = $this->resolve_graphql_query(self::QUERY,[[]]);
        self::assertEmpty($result);
    }

    /**
     * Test the functionality of the course list graphql on basic data.
     */
    public function test_webapi_query_course_list_basic() {
        $this->setAdminUser();

        $courses = self::faux_course_data();

        $included = [];
        $included[] = array_shift($courses);
        $included[] = array_shift($courses);
        $included[] = array_shift($courses);

        $course_ids = array_column($included, 'id');
        $result = $this->resolve_graphql_query(self::QUERY, ['course_ids' => $course_ids]);
        self::assertCount(3, $result);

        foreach ($result as $crs) {
            self::assertTrue(in_array($crs->id, $course_ids));

            $fullname = explode(' ', $crs->fullname);
            self::assertSame('Apple', $fullname[1]);

            $shortname = explode(' ', $crs->shortname);
            self::assertSame('alpha', $shortname[0]);
        }
    }

    /**
     * Test the functionality of the course selector graphql with visibility
     */
    public function test_webapi_query_course_list_visibility() {
        $this->setAdminUser();

        $courses = self::faux_course_data();

        $included = [];
        $included[] = array_shift($courses);
        $included[] = array_shift($courses);
        $included[] = array_shift($courses);

        $course_ids = array_column($included, 'id');
        $result = $this->resolve_graphql_query(self::QUERY, ['course_ids' => $course_ids]);
        self::assertCount(3, $result);

        $hidden = array_shift($included);
        \core_course\management\helper::action_course_hide_by_record($hidden);

        $course_ids = array_column($included, 'id');
        $result = $this->resolve_graphql_query(self::QUERY, ['course_ids' => $course_ids]);
        self::assertCount(2, $result);

        foreach ($result as $crs) {
            self::assertTrue(in_array($crs->id, $course_ids));

            $fullname = explode(' ', $crs->fullname);
            self::assertSame('Apple', $fullname[1]);
            self::assertNotSame('One', $fullname[2]);

            $shortname = explode(' ', $crs->shortname);
            self::assertSame('alpha', $shortname[0]);
            self::assertNotSame('one', $fullname[1]);
        }
    }
}
