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
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package mod_facetoface
 */

use core\webapi\formatter\field\string_field_formatter;
use core_phpunit\testcase;
use mod_facetoface\signup;
use mod_facetoface\signup\state\booked;
use mod_facetoface\signup\state\declined;
use mod_facetoface\signup\state\event_cancelled;
use mod_facetoface\signup\state\fully_attended;
use mod_facetoface\signup\state\no_show;
use mod_facetoface\signup\state\not_set;
use mod_facetoface\signup\state\partially_attended;
use mod_facetoface\signup\state\requested;
use mod_facetoface\signup\state\requestedadmin;
use mod_facetoface\signup\state\requestedrole;
use mod_facetoface\signup\state\unable_to_attend;
use mod_facetoface\signup\state\user_cancelled;
use mod_facetoface\signup\state\waitlisted;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_type_booking_state_test extends testcase {
    use webapi_phpunit_helper;

    const TYPE = 'mod_facetoface_booking_state';

    /**
     * Data provider providing all the different states.
     * @return class-string[][]
     */
    public static function state_provider(): array {
        return [
            [
                booked::class,
            ],
            [
                declined::class,
            ],
            [
                event_cancelled::class,
            ],
            [
                fully_attended::class,
            ],
            [
                no_show::class,
            ],
            [
                partially_attended::class,
            ],
            [
                requested::class,
            ],
            [
                requestedadmin::class,
            ],
            [
                requestedrole::class,
            ],
            [
                unable_to_attend::class,
            ],
            [
                user_cancelled::class,
            ],
            [
                waitlisted::class,
            ],
            [
                not_set::class,
            ],
        ];
    }

    public function test_resolve_with_incorrect_class_string(): void {
        global $USER;

        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("State must be a subclass of mod_facetoface\signup\state\state");

        $this->resolve_graphql_type(
            self::TYPE,
            'title',
            'notaclassstring',
            [],
            context_user::instance($USER->id),
        );
    }

    public function test_resolve_with_incorrect_class(): void {
        global $USER;

        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("State must be a subclass of mod_facetoface\signup\state\state");

        $this->resolve_graphql_type(
            self::TYPE,
            'title',
            $this,
            [],
            context_user::instance($USER->id),
        );
    }

    /**
     * @dataProvider state_provider
     */
    public function test_resolve_title_with_class_string(string $state): void {
        global $USER;
        $this->setAdminUser();

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'title',
            $state,
            [],
            context_user::instance($USER->id),
        );

        $this->assertEquals($state::get_string(), $result);
    }

    /**
     * @dataProvider state_provider
     */
    public function test_resolve_title_with_signup_state(string $state): void {
        global $USER;
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $user = $core_generator->create_user();
        $course = $core_generator->create_course();
        $core_generator->enrol_user(
            $user->id,
            $course->id,
        );

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $seminar_event = $facetoface_generator->create_session_for_course($course);

        $signup_record = $facetoface_generator->create_signup((object)['id' => $user->id], $seminar_event);
        $signup = new signup($signup_record->id);

        $facetoface_generator->switch_signup_state($signup, new $state($signup));
        $signup->load();

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'title',
            $signup->get_state(),
            [],
            context_user::instance($USER->id),
        );

        $this->assertEquals($state::get_string(), $result);
    }

    /**
     * @dataProvider state_provider
     */
    public function test_resolve_enter_state_message_with_class_string(string $state): void {
        global $USER;
        $this->setAdminUser();

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'enter_state_message',
            $state,
            [],
            context_user::instance($USER->id),
        );

        $this->assertNull($result);
    }

    /**
     * @dataProvider state_provider
     */
    public function test_resolve_enter_state_message_with_signup_state(string $state): void {
        global $USER;
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $user = $core_generator->create_user();
        $course = $core_generator->create_course();
        $core_generator->enrol_user(
            $user->id,
            $course->id,
        );

        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $core_generator->get_plugin_generator('mod_facetoface');

        $seminar_event = $facetoface_generator->create_session_for_course($course);

        $signup_record = $facetoface_generator->create_signup((object)['id' => $user->id], $seminar_event);
        $signup = new signup($signup_record->id);

        $facetoface_generator->switch_signup_state($signup, new $state($signup));
        $signup->load();

        $context = context_user::instance($USER->id);

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'enter_state_message',
            $signup->get_state(),
            [],
            $context,
        );

        $format = new string_field_formatter(\core\format::FORMAT_PLAIN, $context);

        $this->assertEquals($format->format($signup->get_state()->get_message()), $result);
    }

    /**
     * @dataProvider state_provider
     */
    public function test_resolve_key_with_class_string(string $state): void {
        global $USER;
        $this->setAdminUser();

        $key = match ($state::get_code()) {
            booked::get_code() => "BOOKED",
            declined::get_code() => "DECLINED",
            event_cancelled::get_code() => "EVENT_CANCELLED",
            fully_attended::get_code() => "FULLY_ATTENDED",
            partially_attended::get_code() => "PARTIALLY_ATTENDED",
            unable_to_attend::get_code() => "UNABLE_TO_ATTEND",
            no_show::get_code() => "NO_SHOW",
            requested::get_code() => "REQUESTED",
            requestedadmin::get_code() => "REQUESTED_ADMIN",
            requestedrole::get_code() => "REQUESTED_ROLE",
            user_cancelled::get_code() => "USER_CANCELLED",
            waitlisted::get_code() => "WAITLISTED",
            not_set::get_code() => "NOT_SET",
        };

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'key',
            $state,
            [],
            context_user::instance($USER->id),
        );

        $this->assertEquals($key, $result);
    }

    public function test_resolve_key_with_unknown_state(): void {
        global $USER;
        $this->setAdminUser();

        $unknown_state = new class extends mod_facetoface\signup\state\state {

            public function __construct() {
                // Override constructor to avoid needing a signup
            }

            public function get_message(): string {
                return '';
            }

            public function get_map(): array {
               return [];
            }

            public static function get_code(): int {
                return 0011101000101001;
            }
        };

        $result = $this->resolve_graphql_type(
            self::TYPE,
            'key',
            $unknown_state::class,
            [],
            context_user::instance($USER->id),
        );

        $this->assertEquals('UNKNOWN', $result);
    }
}
