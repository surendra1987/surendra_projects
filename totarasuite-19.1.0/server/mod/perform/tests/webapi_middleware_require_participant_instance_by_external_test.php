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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use core\webapi\execution_context;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use mod_perform\constants;
use mod_perform\models\activity\participant_instance;
use mod_perform\testing\generator;
use mod_perform\webapi\middleware\require_participant_instance_by_external;
use mod_perform\entity\activity\external_participant;
use mod_perform\models\activity\activity_setting;
use mod_perform\testing\activity_generator_configuration;

/**
 * @group perform
 */
class mod_perform_webapi_middleware_require_participant_instance_by_external_test extends testcase {

    /**
     * @return mixed
     */
    public static function td_load_participant_instance(): array {
        return [
            'no token' => ['get_no_token_data'],
            'invalid token' => ['get_invalid_token_data'],
            'valid token' => ['get_valid_token_data'],
        ];
    }

    /**
     * @return array
     */
    private static function get_no_token_data(): array {
        return [null, ''];
    }

    /**
     * @return array
     */
    private static function get_invalid_token_data(): array {
        return [null, '$2y$10$/unc09hGNue1CEGF25zVMOjknvxlSUhGnVddT/5.zXsWEsI5085cK'];
    }

    /**
     * @param external_participant $ep
     * @return array
     */
    private static function get_valid_token_data(external_participant $ep): array {
        return [$ep, $ep->token];
    }

    /**
     * @dataProvider td_load_participant_instance
     * @return void
     * @throws coding_exception
     */
    public function test_load_participant_instance(string $test_data_method): void {
        /** @var external_participant $external_participant */
        /** @var participant_instance $pi */
        [$external_participant, $pi] = $this->setup_external_data();

        [$expected_external_participant, $payload_id] = static::$test_data_method($external_participant);

        $key = require_participant_instance_by_external::DEF_TOKEN_KEY;
        /** @var payload $payload */
        /** @var Closure $next */
        [$payload, $next] = is_null($payload_id)
            ? static::create_payload([])
            : static::create_payload([$key => $payload_id]);

        $middleware = require_participant_instance_by_external::create(
            $key,
            false
        );

        if (is_null($expected_external_participant)) {
            $this->expectException(coding_exception::class);
            $this->expectExceptionMessage("payload has invalid '$key' value");
            $middleware->handle($payload, $next);
        } else {
            static::assert_payload_participant_instance(
                $pi,
                $middleware->handle($payload, $next)
            );

            static::assert_relevant_context(null, $payload);
        }
    }

    /**
     * @return array
     */
    public static function td_throw_exception_disabled(): array {
        return [
            'no token' => ['get_no_token'],
            'invalid token' => ['get_invalid_token']
        ];
    }

    /**
     * @return string|null
     */
    private static function get_no_token(): ?string {
        return null;
    }

    /**
     * @return string|null
     */
    private static function get_invalid_token(): ?string {
        return '$2y$10$/unc09hGNue1CEGF25zVMOjknvxlSUhGnVddT/5.zXsWEsI5085cK';
    }

    /**
     * @dataProvider td_throw_exception_disabled
     * @return void
     * @throws coding_exception
     */
    public function test_throw_exception_disabled(string $test_data_method): void {
        $this->setup_external_data();
        $token = static::$test_data_method();

        /** @var payload $payload */
        /** @var Closure $next */
        [$payload, $next] = static::create_payload([
            require_participant_instance_by_external::DEF_TOKEN_KEY => $token
        ]);

        $result = require_participant_instance_by_external::create()
            ->disable_throw_exception_on_missing()
            ->handle($payload, $next);

        static::assert_payload_participant_instance(null, $result);
        static::assert_relevant_context(null, $payload);
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
     * @return void
     * @throws coding_exception
     */
    public function test_payload_context(bool $set_context): void {
        /** @var external_participant $external_participant */
        /** @var participant_instance $pi */
        [$external_participant, $pi] = $this->setup_external_data();

        $base = 'input';
        /** @var payload $payload */
        /** @var Closure $next */
        [$payload, $next] = static::create_payload([
            $base => [
                require_participant_instance_by_external::DEF_TOKEN_KEY => $external_participant->token
            ]
        ]);

        $middleware = require_participant_instance_by_external::create(
            "$base." . require_participant_instance_by_external::DEF_TOKEN_KEY,
            $set_context
        );

        static::assert_payload_participant_instance(
            $pi,
            $middleware->handle($payload, $next)
        );

        static::assert_relevant_context($set_context ? $pi->context : null, $payload);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_payload_keys(): void {
        $level_1 = 'level_1';
        $level_2 = 'level_2';
        $token = 'token';

        /** @var external_participant $external_participant */
        /** @var participant_instance $pi */
        [$external_participant, $pi] = $this->setup_external_data();
        /** @var payload $payload */
        /** @var Closure $next */
        [$payload, $next] = static::create_payload([
            $level_1 => [
                $level_2 => [$token => $external_participant->token]
            ]
        ]);

        $result = require_participant_instance_by_external::create("$level_1.$level_2.$token")
            ->handle($payload, $next);

        static::assert_payload_participant_instance($pi, $result);
        static::assert_relevant_context($pi->context, $payload);
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
            static::assertNotNull(
                $actual_participant_instance,
                'no payload participant_instance'
            );

            static::assertEquals(
                $expected->id,
                $actual_participant_instance->id,
                'wrong payload participant_instance'
            );
        } else {
            static::assertNull(
                $actual_participant_instance,
                'payload has participant_instance'
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
            static::assertTrue(
                $exec_context->has_relevant_context(),
                'relevant context not set'
            );

            static::assertEquals(
                $expected_context->id,
                $exec_context->get_relevant_context()->id,
                'wrong relevant context'
            );
        } else {
            static::assertFalse(
                $exec_context->has_relevant_context(),
                'relevant context set'
            );
        }
    }

    /**
     * Generates test data.
     *
     * @return array
     */
    private function setup_external_data(): array {
        static::setAdminUser();

        $generator = generator::instance();

        $configuration = activity_generator_configuration::new()
            ->enable_creation_of_manual_participants()
            ->set_relationships_per_section(
                [
                    constants::RELATIONSHIP_EXTERNAL,
                    constants::RELATIONSHIP_SUBJECT,
                    constants::RELATIONSHIP_MANAGER
                ]
            );

        $activities = $generator->create_full_activities($configuration);
        foreach ($activities as $activity) {
            $activity->settings->update([
                    activity_setting::MANUAL_CLOSE => true
                ]
            );
        }

        /** @var external_participant $external_participant */
        $external_participant = external_participant::repository()->get()->first();

        $participant_instance = participant_instance::load_by_entity(
            $external_participant->participant_instance
        );

        return [$external_participant, $participant_instance];
    }

    /**
     * Creates a payload from the given data.
     *
     * @param array<string,mixed> $data payload data.
     *
     * @return array[] [created payload, 'next' middleware function to execute]
     *         tuple.
     */
    private static function create_payload(array $data): array {
        $payload = payload::create($data, execution_context::create('dev'));

        $next = static fn(payload $payload): result => new result(
            $payload->get_variable(require_participant_instance_by_external::PI_KEY)
        );

        return [$payload, $next];
    }
}
