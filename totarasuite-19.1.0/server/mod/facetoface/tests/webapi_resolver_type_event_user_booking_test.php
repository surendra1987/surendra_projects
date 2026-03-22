<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

defined('MOODLE_INTERNAL') || die();

use core\webapi\execution_context;
use core_phpunit\testcase;
use mod_facetoface\signup;
use totara_webapi\graphql;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_type_event_user_booking_test extends testcase {
    use webapi_phpunit_helper;

    const TYPE = 'mod_facetoface_event_user_booking';

    public function test_resolve_not_instance_of_signup() {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Only signup instances are accepted. Instead, got: array');

        $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            ['array_is_incorrect'],
        );
    }

    public function test_resolve_signup_has_not_been_saved() {
        $signup = new mod_facetoface\signup();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Signup provided does not exist.');

        $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );
    }

    public function test_resolve_field_booked_with_state_booked() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\booked($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_booked_with_state_declined() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\declined($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertFalse($result);
    }

    public function test_resolve_field_booked_with_state_event_cancelled() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\event_cancelled($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertFalse($result);
    }

    public function test_resolve_field_booked_with_state_fully_attended() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\fully_attended($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_booked_with_state_no_show() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\no_show($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_booked_with_state_partially_attended() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\partially_attended($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_booked_with_state_requested() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\requested($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_booked_with_state_requestedadmin() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\requestedadmin($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_booked_with_state_requestedrole() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\requestedrole($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_booked_with_state_unable_to_attended() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\unable_to_attend($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_booked_with_state_user_cancelled() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\user_cancelled($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertFalse($result);
    }

    public function test_resolve_field_booked_with_state_waitlisted() {
        $this->setAdminUser();
        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);
        $facetoface_generator->switch_signup_state($signup, new signup\state\waitlisted($signup));

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'booked',
            $signup,
        );

        $this->assertTrue($result);
    }

    public function test_resolve_field_user_exists() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'user',
            $signup,
        );

        $id = $this->resolve_graphql_type(
            'core_user',
            'id',
            $result,
        );

        $this->assertEquals($user->id, $id);
    }

    public function test_resolve_field_user_deleted() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $user = new \core\entity\user($user->id);
        $user->deleted = true;
        $user->update();

        $signup = (new signup())->from_record($signup);

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'user',
            $signup,
        );

        $this->assertNull($result);
    }

    public function test_resolve_field_event() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'event',
            $signup,
        );

        $execution_context = execution_context::create(graphql::TYPE_EXTERNAL);
        $execution_context->set_relevant_context($seminar_event->get_seminar()->get_context());

        $id = $this->resolve_graphql_type(
            'mod_facetoface_event',
            'id',
            $result,
            [],
            null,
            $execution_context,
        );

        $this->assertEquals($seminar_event->get_id(), $id);
    }

    public function test_resolve_field_custom_fields_fields_set() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);

        // create custom fields
        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('facetoface_signup', ['text_one']);
        $custom_field_generator->set_text($signup->to_record(), $custom_fields['text_one'], 'custom field value', 'facetofacesignup', 'facetoface_signup');

        $context = $seminar_event->get_seminar()->get_context();

        $execution_context = execution_context::create(graphql::TYPE_EXTERNAL);
        $execution_context->set_relevant_context($context);
        $execution_context->set_variable('context', $context);
        $execution_context->set_variable(
            'custom_fields_facetofacesignup',
            customfield_get_custom_fields_and_data_for_items('facetofacesignup', [$signup->get_id()])
        );
        $execution_context->set_variable(
            'custom_fields_facetofacecancellation',
            customfield_get_custom_fields_and_data_for_items('facetofacecancellation', [$signup->get_id()])
        );

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'custom_fields',
            $signup,
            [],
            null,
            $execution_context,
        );

        // Filter to only custom fields we created for this test (there are default custom fields set on signups)
        $result = array_filter($result, function ($field) use ($custom_fields) {
            return in_array($field['definition']['id'], $custom_fields);
        });

        $raw_value = $this->resolve_graphql_type(
            'totara_customfield_field',
            'raw_value',
            $result[1],
            [],
            null,
            $execution_context,
        );

        $this->assertEquals('custom field value', $raw_value);
    }

    public function test_resolve_field_custom_fields_fields_not_set() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'custom_fields',
            $signup,
        );

        $this->assertNull($result);
    }

    public function test_resolve_field_cancellation_custom_fields_fields_set() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);

        // create custom fields
        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('facetoface_cancellation', ['text_one']);
        $custom_field_generator->set_text($signup->to_record(), $custom_fields['text_one'], 'custom field value', 'facetofacecancellation', 'facetoface_cancellation');

        $context = $seminar_event->get_seminar()->get_context();

        $execution_context = execution_context::create(graphql::TYPE_EXTERNAL);
        $execution_context->set_relevant_context($context);
        $execution_context->set_variable('context', $context);
        $execution_context->set_variable(
            'custom_fields_facetofacesignup',
            customfield_get_custom_fields_and_data_for_items('facetofacesignup', [$signup->get_id()])
        );
        $execution_context->set_variable(
            'custom_fields_facetofacecancellation',
            customfield_get_custom_fields_and_data_for_items('facetofacecancellation', [$signup->get_id()])
        );

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'cancellation_custom_fields',
            $signup,
            [],
            null,
            $execution_context,
        );

        // Filter to only custom fields we created for this test (there are default custom fields set on signups)
        $result = array_filter($result, function ($field) use ($custom_fields) {
            return in_array($field['definition']['id'], $custom_fields);
        });

        $raw_value = $this->resolve_graphql_type(
            'totara_customfield_field',
            'raw_value',
            $result[1],
            [],
            null,
            $execution_context,
        );

        $this->assertEquals('custom field value', $raw_value);
    }

    public function test_resolve_field_cancellation_custom_fields_fields_not_set() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $user = $core_generator->create_user();

        $course = $core_generator->create_course();
        $core_generator->enrol_user($user->id, $course->id);
        $seminar_event = $facetoface_generator->create_session_for_course($course);
        $signup = $facetoface_generator->create_signup($user, $seminar_event);

        $signup = (new signup())->from_record($signup);

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'cancellation_custom_fields',
            $signup,
        );

        $this->assertNull($result);
    }
}
