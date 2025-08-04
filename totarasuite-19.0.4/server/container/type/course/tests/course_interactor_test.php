<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author  Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package container_course
 */

use container_course\interactor\course_interactor;
use core\entity\enrol;
use core\orm\query\builder;
use core_phpunit\testcase;
use container_course\course;

/**
 * Class container_course_course_interactor_test
 */
class container_course_course_interactor_test extends testcase {
    /**
     * @return void
     */
    public function test_can_view_course_as_guest_user(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();

        $guest_user = guest_user();
        $course = course::from_record($course_record);

        $interactor = new course_interactor($course, $guest_user->id);
        self::assertTrue($interactor->can_view());

        // Remove the visibility of the course the guest will not be
        // able to view the course.
        $new_data = new stdClass();
        $new_data->visible = 0;
        $new_data->visibleold = 1;

        $course->update($new_data);
        self::assertFalse($interactor->can_view());
    }

    /**
     * @return void
     */
    public function test_can_access_course_as_guest_user(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/enrollib.php");

        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();

        $course = course::from_record($course_record);

        self::setGuestUser();
        $interactor = new course_interactor($course);

        // User is not able to access the course.
        self::assertFalse($interactor->can_access());

        // But if we enable the guest enrolment, then guest user should be able to access it.
        $db = builder::get_db();
        $enrol_instance = $db->get_record(
            'enrol',
            ['enrol' => 'guest', 'courseid' => $course->id],
            '*',
            MUST_EXIST
        );

        // Call to require login because it will allow the auto enrol guest.

        try {
            // However since guest enrol is not enabled, therefore guest user is not enrolled.
            require_login($course_record->id, true, null, false, true);
            self::fail("Expect the require login to throw exception since the guest enrol is not enabled");
        } catch (require_login_exception $e) {
            self::assertStringContainsString(
                get_string('requireloginerror', 'error'),
                $e->getMessage()
            );
        }

        self::assertFalse($interactor->can_access());

        $plugin = enrol_get_plugin('guest');
        $new_enrol_instance = new stdClass();
        $new_enrol_instance->status = ENROL_INSTANCE_ENABLED;

        $plugin->update_instance($enrol_instance, $new_enrol_instance);
        $course->rebuild_cache();

        require_login($course_record->id, true, null, false, true);
        self::assertTrue($interactor->can_access());
    }

    /**
     * @return void
     */
    public function test_can_view_course_as_authenticated_user(): void {
        $generator = self::getDataGenerator();

        $course_record = $generator->create_course();
        $user = $generator->create_user();

        $course = course::from_record($course_record);
        $interactor = new course_interactor($course, $user->id);

        // This is happening because the course is publicly viewable.
        self::assertTrue($interactor->can_view());

        // Remove the visibility of the course the guest will not be
        // able to view the course.
        $new_data = new stdClass();
        $new_data->visible = 0;
        $new_data->visibleold = 1;

        $course->update($new_data);
        self::assertFalse($interactor->can_view());
    }

    /**
     * @return void
     */
    public function test_can_access_course_as_authenticated_user(): void {
        $generator = self::getDataGenerator();

        $course_record = $generator->create_course();
        $user = $generator->create_user();

        $course = course::from_record($course_record);
        $interactor = new course_interactor($course, $user->id);
        self::assertFalse($interactor->can_access());

        $generator->enrol_user($user->id, $course->id);
        self::assertTrue($interactor->can_access());
    }

    /**
     * @return void
     */
    public function test_require_view_for_authenticated_user(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();
        $user = $generator->create_user();

        $course = course::from_record($course_record);
        $interactor = new course_interactor($course, $user->id);

        $interactor->require_view();

        // Make the course invisible.
        $new_data = new stdClass();
        $new_data->visible = 0;
        $new_data->visibleold = 1;
        $course->update($new_data);

        try {
            $interactor->require_view();
            self::fail("Expect the require view should yield error");
        } catch (moodle_exception $e) {
            self::assertEquals(
                get_string('error:course_hidden', 'container_course'),
                $e->getMessage()
            );
        }
    }

    /**
     * @return void
     */
    public function test_require_access_for_autneticated_user(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();
        $user = $generator->create_user();

        $course = course::from_record($course_record);
        $interactor = new course_interactor($course, $user->id);

        try {
            $interactor->require_access();
            self::fail("Expect the require access should yield error");
        } catch (moodle_exception $e) {
            self::assertEquals(
                get_string('error:course_access', 'container_course'),
                $e->getMessage()
            );
        }

        $generator->enrol_user($user->id, $course->id);
        $interactor->require_access();
    }

    /**
     * @return void
     */
    public function test_is_siteadmin(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();

        self::setAdminUser();
        $course = course::from_record($course_record);
        $interactor = new course_interactor($course);

        self::assertTrue($interactor->is_siteadmin());
        $user = $generator->create_user();

        self::setUser($user);
        $interactor = new course_interactor($course);
        self::assertFalse($interactor->is_siteadmin());

    }

    /**
     * @return void
     */
    public function test_is_enrolled(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();

        self::setAdminUser();
        $course = course::from_record($course_record);
        $interactor = new course_interactor($course);

        self::assertFalse($interactor->is_enrolled());

        $generator->enrol_user(get_admin()->id, $course->id);
        self::assertTrue($interactor->is_enrolled());
    }

    /**
     * @return void
     */
    public function test_can_mark_self_complete(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course(['enablecompletion' => COMPLETION_ENABLED]);

        self::setAdminUser();
        $course = course::from_record($course_record);
        $interactor = new course_interactor($course);

        self::assertFalse($interactor->is_enrolled());
        self::assertFalse($interactor->can_mark_self_complete());

        $completion = new completion_info($course_record);
        self::assertEquals(COMPLETION_ENABLED, $completion->is_enabled());

        $generator->enrol_user(get_admin()->id, $course->id);
        self::assertTrue($interactor->is_enrolled());
        self::assertNotEquals(COMPLETION_CRITERIA_TYPE_SELF, $completion->get_criteria());
        self::assertFalse($interactor->can_mark_self_complete());

        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course_record);
        $completion_generator->set_completion_criteria($course_record, [COMPLETION_CRITERIA_TYPE_SELF => 1]);
        self::assertTrue($interactor->can_mark_self_complete());
    }

    /**
     * @return void
     */
    public function test_guest_enrol_enabled(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();

        self::setAdminUser();
        $course = course::from_record($course_record);
        $interactor = new course_interactor($course);

        self::assertFalse($interactor->guest_enrol_enabled());

        // Enable the guest enrol.
        $guest_enrol = enrol_get_plugin("guest");
        $guest_enrol_instance = enrol::repository()->find_enrol("guest", $course_record->id);
        $update_guest_instance = $guest_enrol_instance->to_record(true);
        $update_guest_instance->status = ENROL_INSTANCE_ENABLED;
        $guest_enrol->update_instance($guest_enrol_instance->to_record(true), $update_guest_instance);

        self::assertTrue($interactor->guest_enrol_enabled());
    }

    /**
     * @return void
     */
    public function test_can_view_hidden_activities(): void {
        global $DB;

        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();

        $course = course::from_record($course_record);
        $trainer = $generator->create_user();
        $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $generator->role_assign($role->id, $trainer->id, (context_course::instance($course->id))->id);

        $interactor = new course_interactor($course, $trainer->id);
        self::assertTrue($interactor->can_view_hidden_activities());

        $user = $generator->create_user();
        $interactor = new course_interactor($course, $user->id);
        self::assertFalse($interactor->can_view_hidden_activities());
    }

    /**
     * @return void
     */
    public function test_can_update_course(): void {
        $generator = self::getDataGenerator();
        self::setAdminUser();

        $course_record = $generator->create_course();
        $course = course::from_record($course_record);

        $interactor = new course_interactor($course);
        self::assertTrue($interactor->can_update_course());

        $user = $generator->create_user();
        $interactor = new course_interactor($course, $user->id);
        self::assertFalse($interactor->can_update_course());
    }
}