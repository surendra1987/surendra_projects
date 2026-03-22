<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\collection;
use core_phpunit\testcase;
use core\testing\generator as core_generator;
use core\webapi\execution_context;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core\orm\query\exceptions\record_not_found_exception;
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\goal;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\webapi\middleware\require_perform_goal;

/**
 * @group perform_goal
 */
class perform_goal_webapi_middleware_require_perform_goal_test extends testcase {
    /**
     * @return array[]
     */
    public static function td_load_goal(): array {
        return [
            'no id and no id number' => ['no_id_no_idnumber'],
            'valid id, no idnumber' => ['valid_id_no_idnumber'],
            'no id, valid id number' => ['no_id_valid_idnumber'],
            'valid id and valid id number from same goal' => ['valid_id_valid_idnumber_same_goal'],
            'valid id and valid id number from different goals' => ['valid_id_valid_idnumber_different_goals'],
            'invalid id, no id number' => ['invalid_id_no_idnumber'],
            'no id, invalid id number' => ['no_id_invalid_idnumber'],
            'invalid id and id number' => ['invalid_id_and_idnumber'],
            'invalid id and valid id number' => ['invalid_id_valid_idnumber'],
            'id and invalid id number' => ['id_and_invalid_idnumber'],
            'no id, duplicate id number' => ['no_id_duplicate_idnumber'],
            'valid id, duplicate id number' => ['valid_id_duplicate_idnumber']
        ];
    }

    /**
     * @dataProvider td_load_goal
     */
    public function test_load_goal(string $testMethod): void {
        $goals = self::create_test_goals();
        [$expected_goal, $payload_id, $payload_idn] = $this->$testMethod($goals);

        [$payload, $next] = self::create_payload([
            require_perform_goal::DEF_REF_KEY => [
                require_perform_goal::DEF_ID_KEY => $payload_id,
                require_perform_goal::DEF_IDN_KEY => $payload_idn
            ]
        ]);

        $middleware = require_perform_goal::create();

        if (is_null($expected_goal)) {
            $this->expectException(record_not_found_exception::class);
            $this->expectExceptionMessage(get_string('invalidrecordunknown', 'error'));
            $middleware->handle($payload, $next);
        } else {
            self::assert_payload_goal(
                $expected_goal, $middleware->handle($payload, $next)
            );
            self::assert_relevant_context(null, $payload);
        }
    }

    /**
     * @param collection $goals
     * @return null[]
     */
    protected function no_id_no_idnumber(collection $goals): array {
        return [null, null, null];
    }

    /**
     * @param collection $goals
     * @return array
     */
    protected function valid_id_no_idnumber(collection $goals): array {
        $goal = $goals->first();
        return [$goal, $goal->id, null];
    }

    /**
     * @param collection $goals
     * @return array
     */
    protected function no_id_valid_idnumber(collection $goals): array {
        $goal = $goals->first();
        return [$goal, null, $goal->id_number];
    }

    /**
     * @param collection $goals
     * @return array
     */
    protected function valid_id_valid_idnumber_same_goal(collection $goals): array {
        $goal = $goals->first();
        return [$goal, $goal->id, $goal->id_number];
    }

    /**
     * @param collection $goals
     * @return array
     */
    protected function valid_id_valid_idnumber_different_goals(collection $goals): array {
        return [null, $goals->first()->id, $goals->last()->id_number];
    }

    /**
     * @return array
     */
    protected function invalid_id_no_idnumber(): array {
        return [null, 0, null];
    }

    /**
     * @return array
     */
    protected function no_id_invalid_idnumber(): array {
        return [null, null, 'inv idn'];
    }

    /**
     * @return array
     */
    protected function invalid_id_and_idnumber(): array {
        return [null, 0, 'inv idn'];
    }

    /**
     * @param collection $goals
     * @return array
     */
    protected function invalid_id_valid_idnumber(collection $goals): array {
        return [null, 0, $goals->first()->id_number];
    }

    /**
     * @param collection $goals
     * @return array
     */
    protected function id_and_invalid_idnumber(collection $goals): array {
        return [null, $goals->first()->id, 'inv idn'];
    }

    /**
     * @return array
     */
    protected function no_id_duplicate_idnumber(): array {
        $idn = 'duplicate';
        goal_entity::repository()->update(['id_number' => $idn]);
        return [null, null, $idn];
    }

    /**
     * @param collection $goals
     * @return array
     */
    protected function valid_id_duplicate_idnumber(collection $goals): array {
        $idn = 'duplicate';
        goal_entity::repository()->update(['id_number' => $idn]);
        $goal = $goals->first()->refresh(true);
        return [$goal, $goal->id, $idn];
    }

    public static function td_load_goal_throw_exception_disabled(): array {
        return [
            'no id and no id number' => [[null, null]],
            'unknown id' => [[123, null]],
            'unknown idn' => [[null, 'does not exist']],
            'unknown id, unknown idn' => [[123, 'does not exist']]
        ];
    }

    /**
     * @dataProvider td_load_goal_throw_exception_disabled
     */
    public function test_load_goal_throw_exception_disabled(
        array $goal_reference
    ): void {
        self::create_test_goals();
        [$payload_id, $payload_idn] = $goal_reference;

        [$payload, $next] = self::create_payload([
            require_perform_goal::DEF_REF_KEY => [
                require_perform_goal::DEF_ID_KEY => $payload_id,
                require_perform_goal::DEF_IDN_KEY => $payload_idn
            ]
        ]);

        $result = require_perform_goal::create()
            ->disable_throw_exception_on_missing_goal()
            ->handle($payload, $next);

        self::assert_payload_goal(null, $result);
        self::assert_relevant_context(null, $payload);
    }

    public static function td_payload_context(): array {
        return [
            'no goal context, payload context = false' => [false, false],
            'no goal context, payload context = true' => [false, true],
            'custom goal context, payload context = false' => [true, false],
            'custom goal context, payload context = true' => [true, true]
        ];
    }

    /**
     * @dataProvider td_payload_context
     */
    public function test_payload_context(
        bool $set_custom_goal_context,
        bool $set_context_in_payload
    ): void {
        $custom_context = $set_custom_goal_context
            ? context_user::instance(core_generator::instance()->create_user()->id)
            : null;

        $expected_goal = self::create_test_goals(1, $custom_context)->first();
        if ($set_custom_goal_context) {
            self::assertEquals(
                $custom_context->id,
                $expected_goal->context_id,
                'wrong goal context'
            );
        }

        [$payload, $next] = self::create_payload([
            require_perform_goal::DEF_REF_KEY => [
                require_perform_goal::DEF_ID_KEY => $expected_goal->id
            ]
        ]);

        $result = require_perform_goal
            ::create(
                require_perform_goal::DEF_REF_KEY,
                require_perform_goal::DEF_ID_KEY,
                require_perform_goal::DEF_IDN_KEY,
                $set_context_in_payload
            )
            ->handle($payload, $next);

        self::assert_payload_goal($expected_goal, $result);
        self::assert_relevant_context(
            $set_context_in_payload ? $custom_context : null,
            $payload
        );
    }

    public function test_payload_keys(): void {
        $base_k = 'input';
        $ref_k = 'ref';
        $id_k = 'identifier';
        $idn_k = 'domain-specific-key';

        $expected_goal = $this->create_test_goals()->last();

        [$payload, $next] = self::create_payload([
            $base_k => [
                $ref_k => [
                    $id_k => $expected_goal->id,
                    $idn_k => $expected_goal->id_number,
                ]
            ]
        ]);

        $result = require_perform_goal::create("$base_k.$ref_k", $id_k, $idn_k)
            ->handle($payload, $next);

        self::assert_payload_goal($expected_goal, $result);
        self::assert_relevant_context(null, $payload);
    }

    /**
     * Validates the goal stored in the payload.
     *
     * @param null|goal $expected expected goal.
     * @param result $result result from running the middleware.
     */
    private static function assert_payload_goal(
        ?goal $expected,
        result $result
    ): void {
        $actual_goal = $result->get_data();

        if ($expected) {
            self::assertNotNull($actual_goal, 'no payload goal');
            self::assertEquals(
                $expected->id, $actual_goal->id, 'wrong payload goal'
            );
        } else {
            self::assertNull($actual_goal, 'payload has goal');
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
     * @param int $goal_count id number of goals to create. Each of these will
     *        have id numbers like 'idn_0' for the first goal, 'idn_1' for the
     *        second and so on.
     * @param null|context $context goal context if any. If unspecified defaults
     *        to the system context.
     *
     * @return collection<goal> the created goals.
     */
    private static function create_test_goals(
        int $goal_count = 5,
        ?\context $context = null
    ): collection {
        self::setAdminUser();

        $generator = generator::instance();
        return collection::new(range(0, $goal_count - 1))
            ->map(
                function (int $i) use ($context): goal_generator_config {
                    $values = ['id_number' => "idn_$i"];
                    if ($context) {
                        $values['context'] = $context;
                    }

                    return goal_generator_config::new($values);
                }
            )
            ->map(
                fn(goal_generator_config $cfg): goal => $generator->create_goal(
                    $cfg
                )
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
            $payload->get_variable(require_perform_goal::GOAL_KEY)
        );

        return [$payload, $next];
    }
}
