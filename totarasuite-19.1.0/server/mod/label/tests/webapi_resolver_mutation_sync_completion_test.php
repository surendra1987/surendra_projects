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
 * @author Katherine Galano <katherine.galano@totara.com>
 * @package mod_label
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_label_webapi_resolver_mutation_sync_completion_test extends testcase {
    private const MUTATION = 'mod_label_sync_completion';
    private const MOD_NAME = 'label';
    use webapi_phpunit_helper;

    public function test_must_view_completion_tracking_not_allowed(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);
        $gen->enrol_user(get_admin()->id, $course->id);

        $cm = $gen->create_module(self::MOD_NAME, ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                // Incorrect input value
                'completed' => null,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);
        self::assertFalse($result->success);
        self::assertEquals('Activity completion tracking can not be manual', $result->error);
    }

    public function test_completion_not_enabled(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);
        $gen->enrol_user(get_admin()->id, $course->id);
        // Course does not enable completion.
        $cm = $gen->create_module(self::MOD_NAME, ['course' => $course->id]);
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => true,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);
        self::assertFalse($result->success);
        self::assertEquals('Activity completion not enabled', $result->error);
    }

    public function test_activity_not_mobile_friendly(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $gen->enrol_user(get_admin()->id, $course->id);
        $cm = $gen->create_module('quiz', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        try {
            $this->resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'cm_id' => $cm->cmid,
                    'completed' => true,
                    'completion_tracking' => 'TRACKING_MANUAL',
                ]
            ]);
        } catch (moodle_exception $e) {
            self::assertEquals('This course is not compatible with this mobile friendly course.', $e->getMessage());
        }
    }

    public function test_user_not_logged_in(): void {
        self::setGuestUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $gen->enrol_user(get_admin()->id, $course->id);
        $cm = $gen->create_module('quiz', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        try {
            $this->resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'cm_id' => $cm->cmid,
                    'completed' => true,
                    'completion_tracking' => 'TRACKING_MANUAL',
                ]
            ]);
        } catch (moodle_exception $e) {
            self::assertEquals('Course or activity not accessible. (Not enrolled)', $e->getMessage());
        }
    }

    public function test_success(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);
        $cm = $gen->create_module(self::MOD_NAME, ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $gen->enrol_user(get_admin()->id, $course->id);
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => false,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);
        self::assertTrue($result->success);
    }

    public function test_fail_scorm(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);
        $cm = $gen->create_module('scorm', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $gen->enrol_user(get_admin()->id, $course->id);
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => true,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);
        self::assertFalse($result->success);
        self::assertEquals('Endpoint activity type mismatch', $result->error);
    }

    public function test_cmid_invalid(): void {
        self::setAdminUser();
        try {
            $this->resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'cm_id' => 0,
                    'completed' => true,
                    'completion_tracking' => 'TRACKING_MANUAL',
                ]
            ]);
        } catch (moodle_exception $e) {
            self::assertEquals('Invalid course module ID', $e->getMessage());
        }
    }
}
