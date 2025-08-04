<?php
/**
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package core_enrol
 */

use core\entity\user;
use core\event\base;
use core\model\enrol;
use core\model\user_enrolment;
use core\orm\query\builder;
use core_enrol\event\user_enrolment_application_created;
use core_enrol\model\user_enrolment_application;
use core_enrol\testing\enrolment_approval_testcase_trait;
use core_phpunit\testcase;
use mod_approval\model\application\application;

defined('MOODLE_INTERNAL') || die();

/**
 * Class core_user_enrolment_application_model_test
 *
 * @coversDefaultClass \core_enrol\model\user_enrolment_application
 */
class core_enrol_user_enrolment_application_model_test extends testcase {

    use enrolment_approval_testcase_trait;

    /**
     * @return application
     */
    private function create_application(): application {
        $workflow = $this->create_workflow();

        $user = self::getDataGenerator()->create_user();
        return application::create($workflow->get_active_version(), $workflow->default_assignment, $user->id);
    }

    /**
     * @covers ::create_from_user_enrolment
     * @covers ::trigger_created_event
     */
    public function test_create_from_user_enrolment_triggers_event(): void {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();

        $user_enrolment = $this->enrol_user_on_instance($instance, $user->id);

        // Delete the user_enrolment_application record
        $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);
        builder::table(\core_enrol\entity\user_enrolment_application::TABLE)
            ->where('id', '=', $user_enrolment_application->id)
            ->delete();

        // Enable the event sink.
        $sink = $this->redirectEvents();

        // Create a user enrolment application
        user_enrolment_application::create_from_user_enrolment($user_enrolment->id);

        // Collect any events triggered.
        $events = $sink->get_events();
        $sink->close();

        // Check that we get the expected number of events.
        $has_event_triggered = array_filter($events, function (base $event) {
            return $event instanceof user_enrolment_application_created;
        });
        $this->assertCount(1, $has_event_triggered);
    }

    /**
     * @covers ::find_with_user_enrolment_id
     */
    public function test_find_with_user_enrolment_id() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $user_enrolment1 = $this->enrol_user_on_instance($instance, $user1->id);
        $user_enrolment2 = $this->enrol_user_on_instance($instance, $user2->id);

        // Fictional id.
        $this->assertNull(user_enrolment_application::find_with_user_enrolment_id(42));

        // Real id.
        $found_uea = user_enrolment_application::find_with_user_enrolment_id($user_enrolment2->id);
        $this->assertEquals($user_enrolment2->id, $found_uea->user_enrolments_id);
    }

    /**
     * @covers ::find_with_application_id
     */
    public function test_find_with_application_id() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $user_enrolment1 = $this->enrol_user_on_instance($instance, $user1->id);
        $user_enrolment2 = $this->enrol_user_on_instance($instance, $user2->id);

        // Fictional id.
        $this->assertNull(user_enrolment_application::find_with_application_id(42));

        // Real id.
        // First we need to use find_with_user_enrolment_id, because a second application has been created.
        $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment1->id);
        $found_uea = user_enrolment_application::find_with_application_id($user_enrolment_application->approval_application_id);
        $this->assertEquals($user_enrolment1->id, $found_uea->user_enrolments_id);
    }

    /**
     * @covers ::replace_application_with_new
     * @covers ::set_approval_application_id
     */
    public function test_replace_application_with_new():void {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();

        $user_enrolment = $this->enrol_user_on_instance($instance, $user->id);
        $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);
        $failed_application = $user_enrolment_application->approval_application;

        try {
            $user_enrolment_application->replace_application_with_new();
            $this->fail('Expected exception');
        } catch (coding_exception $exception) {
            self::assertStringContainsString('Existing application does not need to be replaced', $exception->getMessage());
        }

        $this->complete_application($failed_application);

        $fresh_user_enrolment_application = user_enrolment_application::load_by_id($user_enrolment_application->id);

        $new_application = $fresh_user_enrolment_application->replace_application_with_new();
        self::assertGreaterThan($failed_application->id, $new_application->id);
        self::assertEquals((int)$failed_application->id + 1, $new_application->id);
    }

    /**
     * @covers ::get_user
     * @covers ::get_approval_application
     * @covers ::get_enrolment  ????
     * @covers ::get_user_enrolment
     * @covers ::get_course
     */
    public function test_model_properties() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user = self::getDataGenerator()->create_user();

        $user_enrolment = $this->enrol_user_on_instance($instance, $user->id);
        $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);

        $this->assertInstanceOf(user::class, $user_enrolment_application->get_user());
        $this->assertInstanceOf(user::class, $user_enrolment_application->user);
        $this->assertEquals($user->id, $user_enrolment_application->user->id);

        $this->assertInstanceOf(application::class, $user_enrolment_application->get_approval_application());
        $this->assertInstanceOf(application::class, $user_enrolment_application->approval_application);

        $this->assertInstanceOf(enrol::class, $user_enrolment_application->get_enrolment_instance());
        $this->assertInstanceOf(enrol::class, $user_enrolment_application->enrolment_instance);
        $this->assertEquals($instance->id, $user_enrolment_application->enrolment_instance->id);

        $this->assertInstanceOf(user_enrolment::class, $user_enrolment_application->get_user_enrolment());
        $this->assertInstanceOf(user_enrolment::class, $user_enrolment_application->user_enrolment);

        // Annoyingly, the enrol model returns a course entity rather than a course model.
        $this->assertInstanceOf(\core\entity\course::class, $user_enrolment_application->get_course());
        $this->assertInstanceOf(\core\entity\course::class, $user_enrolment_application->course);
    }
    
    /**
     * @covers ::find_with_instance_and_user_id
     */
    public function test_find_with_instance_and_user_id() {
        $instance = $this->generate_self_enrolment_instance_with_approval();
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $user_enrolment1 = $this->enrol_user_on_instance($instance, $user1->id);
        $user_enrolment2 = $this->enrol_user_on_instance($instance, $user2->id);

        // Fictional id.
        $this->assertNull(user_enrolment_application::find_with_instance_and_user_id(42, $user2->id));

        // Real id.
        $found_uea = user_enrolment_application::find_with_instance_and_user_id($instance->id, $user2->id);
        $this->assertEquals($user_enrolment2->id, $found_uea->user_enrolments_id);
    }
}
