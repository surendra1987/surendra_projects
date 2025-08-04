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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use core\webapi\execution_context;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use perform_goal\model\goal;
use perform_goal\model\goal_task;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\webapi\middleware\require_perform_goal_task;

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_webapi_middleware_require_perform_goal_task_test extends testcase {
    /**
     * @return array[]
     */
    public static function td_load_task(): array {
        return [
            'no task id, no goal id, no ordinal' => [
                'no_task_no_goal_no_ordinal'
            ],
            'no task id, valid goal id, valid ordinal' => [
                'no_task_valid_goal_valid_ordinal'
            ],
            'no task id, invalid goal id, valid ordinal' => [
                'no_task_invalid_goal_valid_ordinal'
            ],
            'no task id, valid goal id, invalid ordinal' => [
                'no_task_valid_goal_invalid_ordinal'
            ],
            'valid task id, no goal id, no ordinal' => [
                'valid_task_no_goal_no_ordinal'
            ],
            'valid task id, valid goal_id, valid non corresponding ordinal' => [
                'valid_task_valid_goal_non_corresponding_ordinal'
            ],
            'valid task id, valid goal_id, corresponding ordinal' => [
                'valid_task_valid_goal_corresponding_ordinal'
            ],
            'valid task id, invalid goal_id, corresponding ordinal' => [
                'valid_task_invalid_goal_corresponding_ordinal'
            ],
            'valid task id, valid goal_id, invalid ordinal' => [
                'valid_task_valid_goal_invalid_ordinal'
            ],
            'invalid task id, no goal id, no ordinal' => [
                'invalid_task_no_goal_no_ordinal'
            ],
            'invalid task id, valid goal id, valid ordinal' => [
                'invalid_task_valid_goal_valid_ordinal'
            ],
            'invalid task id, invalid goal id, valid ordinal' => [
                'invalid_task_invalid_goal_valid_ordinal'
            ]
        ];
    }

    /**
     * @dataProvider td_load_task
     */
    public function test_load_task(string $testMethod): void {
        $goal = self::create_test_goal();
        [$expected_task, $payload_id, $payload_goal_id, $payload_ord] =
            $this->$testMethod($goal);

        [$payload, $next] = self::create_payload([
            require_perform_goal_task::DEF_REF_KEY => [
                require_perform_goal_task::DEF_TASK_ID_KEY => $payload_id,
                require_perform_goal_task::DEF_GOAL_ID_KEY => $payload_goal_id,
                require_perform_goal_task::DEF_ORDINAL_KEY => $payload_ord
            ]
        ]);

        $middleware = require_perform_goal_task::create();

        if (is_null($expected_task)) {
            $this->expectException(coding_exception::class);
            $this->expectExceptionMessage('Unknown task');
            $middleware->handle($payload, $next);
        } else {
            self::assert_payload_task(
                $expected_task, $middleware->handle($payload, $next)
            );
        }
    }

    /**
     * @param goal $goal
     * @return null[]
     */
    protected function no_task_no_goal_no_ordinal(goal $goal): array {
        return [null, null, null, null];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function no_task_valid_goal_valid_ordinal(goal $goal): array {
        $tasks = $goal->tasks;
        return [$tasks->last(), null, $goal->id, $tasks->count() - 1];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function no_task_invalid_goal_valid_ordinal(goal $goal): array {
        return [null, null, 0, 0];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function no_task_valid_goal_invalid_ordinal(goal $goal): array {
        return [null, null, $goal->id, -1];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function valid_task_no_goal_no_ordinal(goal $goal): array {
        $task = $goal->tasks->first();
        return [$task, $task->id, null, null];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function valid_task_valid_goal_non_corresponding_ordinal(goal $goal): array {
        $tasks = $goal->tasks;
        $task = $goal->tasks->first();
        return [$task, $task->id, $goal->id, $tasks->count() - 1];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function valid_task_valid_goal_corresponding_ordinal(goal $goal): array {
        $tasks = $goal->tasks;
        $task = $tasks->first();

        return [$task, $task->id, $goal->id, 1];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function valid_task_invalid_goal_corresponding_ordinal(goal $goal): array {
        $tasks = $goal->tasks;
        $task = $tasks->first();

        return [$task, $task->id, 0, 1];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function valid_task_valid_goal_invalid_ordinal(goal $goal): array {
        $tasks = $goal->tasks;
        $task = $tasks->first();

        return [$task, $task->id, $goal->id, $tasks->count() + 1];
    }

    /**
     * @return array
     */
    protected function invalid_task_no_goal_no_ordinal(): array {
        return [null, 0, null, null];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function invalid_task_valid_goal_valid_ordinal(goal $goal): array {
        return [null, 0, $goal->id, 1];
    }

    /**
     * @return array
     */
    protected function invalid_task_invalid_goal_valid_ordinal(): array {
        return [null, 0, 0, 1];
    }

    /**
     * @return array[]
     */
    public static function td_load_task_throw_exception_disabled(): array {
        return [
            'no task id, no goal id, no ordinal' => ['no_task_no_goal_no_ordinal_disabled'],
            'no task id, invalid goal id, valid ordinal' => ['no_task_invalid_goal_valid_ordinal_disabled'],
            'no task id, invalid goal id, valid ordinal' => ['no_task_invalid_goal_valid_ordinal_disabled_alt'],
            'invalid task id, no goal id, no ordinal' => ['invalid_task_no_goal_no_ordinal_disabled'],
            'invalid task id, valid goal id, valid ordinal' => ['invalid_task_valid_goal_valid_ordinal_disabled'],
            'invalid task id, invalid goal id, valid ordinal' => ['invalid_task_invalid_goal_valid_ordinal_disabled']
        ];
    }

    /**
     * @dataProvider td_load_task_throw_exception_disabled
     */
    public function test_load_task_throw_exception_disabled(string $testMethod): void {
        $goal = self::create_test_goal();
        [$payload_id, $payload_goal_id, $payload_ord] = $this->$testMethod($goal);

        [$payload, $next] = self::create_payload([
            require_perform_goal_task::DEF_REF_KEY => [
                require_perform_goal_task::DEF_TASK_ID_KEY => $payload_id,
                require_perform_goal_task::DEF_GOAL_ID_KEY => $payload_goal_id,
                require_perform_goal_task::DEF_ORDINAL_KEY => $payload_ord
            ]
        ]);

        $result = require_perform_goal_task::create()
            ->disable_throw_exception_on_missing_task()
            ->handle($payload, $next);

        self::assert_payload_task(null, $result);
    }

    /**
     * @return null[]
     */
    protected function no_task_no_goal_no_ordinal_disabled(): array {
        return [null, null, null];
    }

    /**
     * @return array
     */
    protected function no_task_invalid_goal_valid_ordinal_disabled(): array {
        return [null, 0, 0];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function no_task_invalid_goal_valid_ordinal_disabled_alt(goal $goal): array {
        return [null, $goal->id, -1];
    }

    /**
     * @return array
     */
    protected function invalid_task_no_goal_no_ordinal_disabled(): array {
        return [0, null, null];
    }

    /**
     * @param goal $goal
     * @return array
     */
    protected function invalid_task_valid_goal_valid_ordinal_disabled(goal $goal): array {
        return [0, $goal->id, 1];
    }

    /**
     * @return int[]
     */
    protected function invalid_task_invalid_goal_valid_ordinal_disabled(): array {
        return [0, 0, 1];
    }

    /**
     * @return void
     */
    public function test_payload_keys(): void {
        $base_k = 'input';
        $ref_k = 'ref';
        $id_k = 'identifier';
        $goal_id_k = 'goal-identifier';
        $ordinal_k = 'ordinal-key';

        $middleware = require_perform_goal_task::create(
            "$base_k.$ref_k", $id_k, $goal_id_k, $ordinal_k
        );

        $goal = self::create_test_goal();
        $tasks = $goal->tasks;
        $expected_task = $tasks->last();

        // No task id
        [$payload, $next] = self::create_payload([
            $base_k => [
                $ref_k => [
                    $goal_id_k => $goal->id,
                    $ordinal_k => $tasks->count() - 1
                ]
            ]
        ]);

        $result = $middleware->handle($payload, $next);
        self::assert_payload_task($expected_task, $result);

        // With task id
        [$payload, $next] = self::create_payload([
            $base_k => [
                $ref_k => [
                    $id_k => $expected_task->id,
                    $goal_id_k => $goal->id,
                    $ordinal_k => -1
                ]
            ]
        ]);

        $result = $middleware->handle($payload, $next);
        self::assert_payload_task($expected_task, $result);
    }

    /**
     * Validates the goal task stored in the payload.
     *
     * @param null|goal_task $expected expected goal task.
     * @param result $result result from running the middleware.
     */
    private static function assert_payload_task(
        ?goal_task $expected,
        result $result
    ): void {
        $actual_task = $result->get_data();

        if ($expected) {
            self::assertNotNull($actual_task, 'no payload goal task');
            self::assertEquals(
                $expected->id, $actual_task->id, 'wrong payload goal task'
            );
        } else {
            self::assertNull($actual_task, 'payload has goal task');
        }
    }

    /**
     * Generates test data.
     *
     * @param int $task_count id number of goal tasks to create.
     *
     * @return goal the created parent goal.
     */
    private static function create_test_goal(
        int $task_count = 5
    ): goal {
        self::setAdminUser();

        $cfg = goal_generator_config::new(['number_of_tasks' => $task_count]);
        return generator::instance()->create_goal($cfg);

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
            $payload->get_variable(require_perform_goal_task::TASK_KEY)
        );

        return [$payload, $next];
    }
}
