<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use core\webapi\execution_context;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use mod_perform\constants;
use mod_perform\models\activity\participant_instance;
use mod_perform\testing\generator;
use mod_perform\webapi\middleware\require_participant_instance;

/**
 * @group perform
 */
class mod_perform_webapi_middleware_require_participant_instance_test extends testcase {
    /**
     * @return array[]
     */
    public static function td_load_participant_instance(): array {
        return [
            'no id' => ['get_no_id'],
            'id = 0' => ['get_zero_id'],
            'invalid id' => ['get_invalid_id'],
            'valid id' => ['get_valid_id']
        ];
    }

    /**
     * @return null[]
     */
    private static function get_no_id(): array {
        return [null, null];
    }

    /**
     * @return array
     */
    private static function get_zero_id(): array {
        return [null, 0];
    }

    /**
     * @return array
     */
    private static function get_invalid_id(): array {
        return [null, 132];
    }

    /**
     * @param participant_instance $it
     * @return array
     */
    private static function get_valid_id(participant_instance $it): array {
        return [$it, $it->id];
    }

    /**
     * @dataProvider td_load_participant_instance
     */
    public function test_load_participant_instance(string $test_data_method): void {
        $pi = self::setup_env();
        [$expected_pi, $payload_id] = static::$test_data_method($pi);

        $key = require_participant_instance::DEF_ID_KEY;
        [$payload, $next] = is_null($payload_id)
            ? self::create_payload([])
            : self::create_payload([$key => $payload_id]);

        $middleware = require_participant_instance::create(
            set_relevant_context: false
        );

        if (is_null($expected_pi)) {
            $this->expectException(coding_exception::class);
            $this->expectExceptionMessage("payload has invalid '$key' value");
            $middleware->handle($payload, $next);
        } else {
            self::assert_payload_participant_instance(
                $expected_pi, $middleware->handle($payload, $next)
            );

            self::assert_relevant_context(null, $payload);
        }
    }

    /**
     * @return array[]
     */
    public static function td_throw_exception_disabled(): array {
        return [
            'no id' => ['get_no_id_disabled'],
            'unknown id' => ['get_unknown_id']
        ];
    }

    /**
     * @return int|null
     */
    private static function get_no_id_disabled(): ?int {
        return null;
    }

    /**
     * @return int
     */
    private static function get_unknown_id(): int {
        return 123;
    }

    /**
     * @dataProvider td_throw_exception_disabled
     */
    public function test_throw_exception_disabled(string $test_data_method): void {
        self::setup_env();

        $id = static::$test_data_method();

        [$payload, $next] = self::create_payload([
            require_participant_instance::DEF_ID_KEY => $id
        ]);

        $result = require_participant_instance::create()
            ->disable_throw_exception_on_missing()
            ->handle($payload, $next);

        self::assert_payload_participant_instance(null, $result);
        self::assert_relevant_context(null, $payload);
    }

    /**
     * @return array
     */
    public static function td_payload_context(): array {
        return [
            'set payload context = false' => [false],
            'set payload context = true' => [true]
        ];
    }

    /**
     * @dataProvider td_payload_context
     */
    public function test_payload_context(
        bool $set_context
    ): void {
        $expected_pi = self::setup_env();

        $base = 'input';
        [$payload, $next] = self::create_payload([
            $base => [
                require_participant_instance::DEF_ID_KEY => $expected_pi->id
            ]
        ]);

        $middleware = require_participant_instance::create(
            "$base." . require_participant_instance::DEF_ID_KEY, $set_context
        );

        self::assert_payload_participant_instance(
            $expected_pi, $middleware->handle($payload, $next)
        );

        self::assert_relevant_context(
            $set_context ? $expected_pi->context : null, $payload
        );
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_payload_keys(): void {
        $level_1 = 'level_1';
        $level_2 = 'level_2';
        $id = 'id';

        $expected_pi = $this->setup_env();
        [$payload, $next] = self::create_payload([
            $level_1 => [
                $level_2 => [$id => $expected_pi->id]
            ]
        ]);

        $result = require_participant_instance::create("$level_1.$level_2.$id")
            ->handle($payload, $next);

        self::assert_payload_participant_instance($expected_pi, $result);
        self::assert_relevant_context($expected_pi->context, $payload);
    }

    /**
     * Validates the participant_instance stored in the payload.
     *
     * @param null|participant_instance $expected expected participant_instance.
     * @param result $result result from running the middleware.
     */
    private static function assert_payload_participant_instance(
        ?participant_instance $expected,
        result $result
    ): void {
        $actual_participant_instance = $result->get_data();

        if ($expected) {
            self::assertNotNull(
                $actual_participant_instance, 'no payload participant_instance'
            );

            self::assertEquals(
                $expected->id,
                $actual_participant_instance->id,
                'wrong payload participant_instance'
            );
        } else {
            self::assertNull(
                $actual_participant_instance, 'payload has participant_instance'
            );
        }
    }

    /**
     * Validates the relevant context stored in the payload.
     *
     * @param ?context $expected_context expected context to be stored in the
     *        payload's relevant context field. If this is null, checks there is
     *        no context stored.
     * @param payload $payload payload to check.
     */
    private static function assert_relevant_context(
        ?context $expected_context,
        payload $payload
    ): void {
        $exec_context = $payload->get_execution_context();

        if ($expected_context) {
            self::assertTrue(
                $exec_context->has_relevant_context(),'relevant context not set'
            );

            self::assertEquals(
                $expected_context->id,
                $exec_context->get_relevant_context()->id,
                'wrong relevant context'
            );
        } else {
            self::assertFalse(
                $exec_context->has_relevant_context(),'relevant context set'
            );
        }
    }

    /**
     * Generates test data.
     *
     * @return participant_instance created participant_instance.
     */
    private static function setup_env(): participant_instance {
        self::setAdminUser();

        $core_generator = self::getDataGenerator();
        $subject = $core_generator->create_user();

        $generator = generator::instance();
        $activity = $generator->create_activity_in_container();
        $si = $generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_user_id' => $subject->id,
            'include_questions' => false,
        ]);

        $section = $activity->get_sections()->first();
        $generator->create_participant_instance_and_section(
            $activity,
            $subject,
            $si->id,
            $section,
            $generator->get_core_relationship(constants::RELATIONSHIP_SUBJECT)->id
        );

        $participant_section = $generator->create_participant_instance_and_section(
            $activity,
            $core_generator->create_user(),
            $si->id,
            $section,
            $generator->get_core_relationship(constants::RELATIONSHIP_PEER)->id
        );

        return participant_instance::load_by_entity(
            $participant_section->participant_instance
        );
    }

    /**
     * Creates a payload from the given data.
     *
     * @param array<string,mixed> $data payload data.
     *
     * @return mixed[] [created payload, 'next' middleware function to execute]
     *         tuple.
     */
    private static function create_payload(array $data): array {
        $payload = payload::create($data, execution_context::create('dev'));

        $next = fn(payload $payload): result => new result(
            $payload->get_variable(require_participant_instance::PI_KEY)
        );

        return [$payload, $next];
    }
}
