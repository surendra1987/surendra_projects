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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core
 */

use core\collection;
use core_phpunit\testcase;
use core_tag\ai\interaction\suggest_tags;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \core\webapi\resolver\query\ai_course_suggest_tags
 *
 * @group core_tag
 */
class core_webapi_query_ai_course_suggest_tags_test extends testcase {

    private const QUERY = 'core_ai_course_suggest_tags';

    use webapi_phpunit_helper;

    private static function query_args_from_course($course) {
        return ['input' => [
            'course_id' => $course->id,
            'course' => [
                'fullname' => $course->fullname,
                'shortname' => $course->shortname,
                'description' => $course->summary,
                'description_format' =>  $course->summaryformat,
            ]
        ]];
    }

    /**
     * @param int $count
     * @return collection
     */
    private function create_courses(int $count = 5): collection {
        $courses = [];
        foreach (range(0, $count - 1) as $i) {
            $uniq = uniqid();
            $fullname = 'Test course ' . $uniq;
            $shortname = 'testcourse' . $uniq;
            $course = self::getDataGenerator()->create_course([
                'shortname' => $shortname,
                'fullname' => $fullname,
                'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'summaryformat' => '2',
            ]);
            $tags = [
                'Unique tag ' . $uniq,
                'Standard tag'
            ];
            core_tag_tag::set_item_tags('core', 'course', $course->id, context_course::instance($course->id), $tags);
            $courses[] = $course;
        }

        return collection::new($courses);
    }

    /**
     * Basic functionality test for the happy path when querying ai_course_suggest_tags using noai support.
     *
     * @covers ::resolve
     */
    public function test_query_ai_course_suggest_tags(): void {
        global $CFG, $DB;
        $user = $this->getDataGenerator()->create_user();
        // Each course has one standard tag and one unique tag; we need to make sure there are enough tags for a response.
        $courses = $this->create_courses(suggest_tags::MIN_TAGS + 2);

        // Not logged in.
        try {
            $result = $this->resolve_graphql_query(self::QUERY, self::query_args_from_course($courses[0]));
            $this->fail('Expected exception not thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(require_login_exception::class, $e);
            $this->assertStringContainsStringIgnoringCase('You are not logged in', $e->getMessage());
        }

        // Logged in, no permission.
        $this->setUser($user);
        try {
            $result = $this->resolve_graphql_query(self::QUERY, self::query_args_from_course($courses[0]));
            $this->fail('Expected exception not thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(required_capability_exception::class, $e);
            $this->assertStringContainsStringIgnoringCase('Change course tags', $e->getMessage());
        }

        // Assign role with permission
        $editing_trainer = $DB->get_record('role', array('shortname' => 'editingteacher'), '*', MUST_EXIST);
        role_assign($editing_trainer->id, $user->id, context_system::instance());

        // Subsystem not enabled
        $result = $this->resolve_graphql_query(self::QUERY, self::query_args_from_course($courses[0]));
        $this->assertCount(0, $result['items']);
        $this->assertEquals(0, $result['total']);

        // OK, enable the subsystem.
        $CFG->enable_ai = true;
        set_config('default_ai', 'noai', 'core_ai');

        // No AI plugin will return one random tag.
        $query = self::query_args_from_course($courses[0]);
        $result = $this->resolve_graphql_query(self::QUERY, $query);
        $this->assertCount(1, $result['items']);
        $this->assertEquals(1, $result['total']);
        foreach ($result['items'] as $id => $item) {
            $this->assertInstanceOf(\core_tag\model\tag::class, $item);
            $this->assertEquals($id, $item->id);
        }
    }
}
