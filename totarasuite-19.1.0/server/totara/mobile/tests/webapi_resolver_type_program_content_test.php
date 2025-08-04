<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package totara_mobile
 */

defined('MOODLE_INTERNAL') || die();

use core\webapi\execution_context;
use totara_webapi\phpunit\webapi_phpunit_helper;
use core_course\user_learning\item as course_item;

/**
 * Tests the totara core program type resolver.
 */
class totara_mobile_webapi_resolver_type_program_content_test extends \core_phpunit\testcase {

    use webapi_phpunit_helper;

    /**
     * @param string $field - the field we want to check on the course item
     * @param course_item|object $item - the course_item (or pseudo-object) that will be passed to program_content
     * @param array $args - any additional arguments that could be passed to program_content
     * @param execution_context|null $ec - any special execution context that we couldn't get from get_execution_context
     * @return mixed
     */
    private function resolve(string $field, $item, array $args = [], execution_context $ec = null) {
        return \totara_mobile\webapi\resolver\type\program_content::resolve(
            $field,
            $item,
            $args,
            $ec ?? $this->get_execution_context()
        );
    }

    private function get_execution_context(string $type = 'dev', ?string $operation = null) {
        return execution_context::create($type, $operation);
    }

    /**
     * Ensure that the program_content resolver works only for objects of the designated class
     */
    public function test_resolve_stdclass_only() {
        try {
            $this->resolve('id', 7);
            $this->fail('Only program content learning_item records should be accepted');
        } catch (\coding_exception $ex) {
            $this->assertSame(
                'Coding error detected, it must be fixed by a programmer: Only program content learning_item objects are accepted: integer',
                $ex->getMessage()
            );
        }

        try {
            $this->resolve('id', ['id' => 7]);
            $this->fail('Only program content learning_item records should be accepted');
        } catch (\coding_exception $ex) {
            $this->assertSame(
                'Coding error detected, it must be fixed by a programmer: Only program content learning_item objects are accepted: array',
                $ex->getMessage()
            );
        }

        try {
            $user = $this->getDataGenerator()->create_user();
            $course = $this->getDataGenerator()->create_course();
            $item = course_item::one($user, $course);
            $value = $this->resolve('id', $item);
            $this->assertEquals('course_' . $item->id, $value);
        } catch (\coding_exception $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Test that the user can view the item when the visibility is set to "enrolled users"
     */
    public function test_set_item_to_viewable_when_user_is_enrolled() {
        $result = $this->set_up_audiencevisibility_tests(COHORT_VISIBLE_ENROLLED);
        $this->assertTrue($result);
    }

    /**
     * Test that the user is blocked from viewing when the visibility is "no users"
     */
    public function test_disallow_viewing_when_visibility_is_off() {
        $result = $this->set_up_audiencevisibility_tests(COHORT_VISIBLE_NOUSERS);
        $this->assertFalse($result);
    }

    /**
     * Test that the user is allowed to view when the visibility is "everyone"
     */
    public function test_allow_viewing_when_visibility_is_allowed_for_everyone() {
        $result = $this->set_up_audiencevisibility_tests(COHORT_VISIBLE_ALL);
        $this->assertTrue($result);
    }

    /**
     * Test that the user is allowed to view when the visibility is "audience"
     */
    public function test_allow_viewing_when_visibility_is_audience_only() {
        $result = $this->set_up_audiencevisibility_tests(COHORT_VISIBLE_AUDIENCE);
        $this->assertTrue($result);
    }

    /**
     * Ensure that we're only testing the audience visibility by always setting the "viewable" field on
     * the item, and the config audience visibility.
     * @param int $visibility - the enum visibility value set in `totara.php`
     * @returns bool - the value of the `viewable` field on the course item after evaluation
     */
    private function set_up_audiencevisibility_tests(int $visibility): bool {
        global $CFG;

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['audiencevisible' => $visibility], ['createsections' => true]);
        $course->audiencevisible = $visibility;
        $item = course_item::one($user, $course);
        // Set this to false so that we're checking whether the audience visibility can affect the viewability
        $item->viewable = false;
        // This field needs to be set for the visibility to matter at all
        $CFG->audiencevisibility = 1;
        return $this->resolve('viewable', $item);
    }
}