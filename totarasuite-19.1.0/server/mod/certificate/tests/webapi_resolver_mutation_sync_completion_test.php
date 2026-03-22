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

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_certificate_webapi_resolver_mutation_sync_completion_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'mod_certificate_sync_completion';

    /**
     * @return void
     */
    public function test_manual_completion(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
        $cm = $gen->create_module('certificate', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);

        self::assertFalse($DB->record_exists('course_modules_completion', ['coursemoduleid' => $cm->cmid]));
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => true,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);
        self::assertTrue($result->success);
        self::assertEmpty($result->error);

        // Completed
        $record = $DB->get_record('course_modules_completion', ['coursemoduleid' => $cm->cmid]);
        self::assertTrue((bool)$record->completionstate);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => false,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);

        self::assertTrue($result->success);
        self::assertEmpty($result->error);

        // Not completed
        $record = $DB->get_record('course_modules_completion', ['coursemoduleid' => $cm->cmid]);
        self::assertFalse((bool)$record->completionstate);
    }

    /**
     * @return void
     */
    public function test_automatic_completion(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
        $cm = $gen->create_module('certificate', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => COMPLETION_VIEW_REQUIRED]);

        self::assertFalse($DB->record_exists('course_modules_completion', ['coursemoduleid' => $cm->cmid]));
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => null,
                'completion_tracking' => 'TRACKING_AUTOMATIC',
            ]
        ]);
        self::assertTrue($result->success);
        self::assertEmpty($result->error);

        // Completed
        $record = $DB->get_record('course_modules_completion', ['coursemoduleid' => $cm->cmid]);
        self::assertTrue((bool)$record->completionstate);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function test_user_not_logged_in(): void {
        self::setGuestUser();
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
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

    /**
     * @return void
     */
    public function test_endpoint_exception(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
        $cm = $gen->create_module('scorm', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);

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

    /**
     * @return void
     */
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

    /**
     * @return stdClass
     */
    private function create_mobile_friendly_course() {
        global $DB;
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);

        return $course;
    }

    /**
     * @return void
     */
    public function test_completion_mismatch(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
        $cm = $gen->create_module('certificate', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => COMPLETION_VIEW_REQUIRED]);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => true,
                'completion_tracking' => 'TRACKING_AUTOMATIC',
            ]
        ]);
        self::assertFalse($result->success);
        self::assertEquals('Activity completion tracking can not be automatic', $result->error);
    }

    /**
     * @return void
     */
    public function test_activity_disabled(): void {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
        $cm = $gen->create_module('certificate', ['course' => $course->id]);
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => false,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);
        self::assertEquals('Activity completion not enabled', $result->error);
    }

    /**
     * @return void
     */
    public function test_input_exception(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
        $cm = $gen->create_module('certificate', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => null,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);
        self::assertFalse($result->success);
        self::assertEquals('Activity completion tracking can not be manual', $result->error);

        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => false,
                'completion_tracking' => 'TRACKING_AUTOMATIC',
            ]
        ]);

        self::assertFalse($result->success);
        self::assertEquals('Activity completion tracking can not be automatic', $result->error);
    }

    /**
     * @return void
     */
    public function test_manual_sync_by_learner(): void {
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
        $user = $gen->create_user();
        $gen->enrol_user($user->id, $course->id);

        self::setUser($user);

        $cm = $gen->create_module('certificate', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => true,
                'completion_tracking' => 'TRACKING_MANUAL',
            ]
        ]);
        self::assertTrue($result->success);
        self::assertEmpty($result->error);
    }

    /**
     * @return void
     */
    public function test_automatic_sync_by_learner(): void {
        $gen = self::getDataGenerator();
        $course = $this->create_mobile_friendly_course();
        $user = $gen->create_user();
        $gen->enrol_user($user->id, $course->id);

        self::setUser($user);

        $cm = $gen->create_module('certificate', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => COMPLETION_VIEW_REQUIRED]);
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'cm_id' => $cm->cmid,
                'completed' => null,
                'completion_tracking' => 'TRACKING_AUTOMATIC',
            ]
        ]);
        self::assertTrue($result->success);
        self::assertEmpty($result->error);
    }
}