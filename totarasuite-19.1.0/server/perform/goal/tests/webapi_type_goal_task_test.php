<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\date_format;
use core_phpunit\testcase;
use perform_goal\formatter\goal_task_formatter as formatter;
use perform_goal\model\goal;
use perform_goal\model\goal_task;
use perform_goal\model\goal_task_resource;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_webapi_type_goal_task_test extends testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_goal_task';

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(goal::class);

        $this->resolve_graphql_type(self::TYPE, 'id', new \stdClass());
    }

    /**
     * @covers ::resolve
     */
    public function test_invalid_field(): void {
        $this->setAdminUser();

        $field = 'unknown';

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage($field);
        $this->resolve_graphql_type(
            self::TYPE, $field, $this->create_test_goal_task()
        );
    }

    /**
     * Test data for test_valid
     */
    public static function td_valid(): array {
        return [
            'completion date' => [
                'completion_date_formatter',
                formatter::COMPLETION_DATE,
                date_format::FORMAT_DATESHORT
            ],
            'created date' => [
                'created_date_formatter',
                formatter::CREATION_DATE,
                date_format::FORMAT_DATELONG
            ],
            'description' => [
                'description_formatter',
                formatter::DESC
            ],
            'goal id' => [
                'goal_id_formatter',
                formatter::GOAL_ID
            ],
            'resource' => [
                'resource_formatter',
                formatter::RESOURCE
            ],
            'task id' => [
                'task_id_formatter',
                formatter::ID
            ],
            'updated date' => [
                'updated_date_formatter',
                formatter::UPDATED_DATE,
                date_format::FORMAT_DATELONG
            ]
        ];
    }

    /**
     * @dataProvider td_valid
     */
    public function test_valid(
        string $formatterMethod,
        string $field,
        ?string $format=null
    ): void {
        $args = $format ? ['format' => $format] : [];
        $task = $this->create_test_goal_task();

        $this->assertEquals(
            $this->$formatterMethod($task, $format),
            $this->resolve_graphql_type(self::TYPE, $field, $task, $args),
            'wrong value'
        );
    }

    // Formatter methods

    /**
     * @param goal_task $task
     * @param string $format
     * @return string
     */
    protected function completion_date_formatter(goal_task $task, string $format): string {
        return $this->format_date($task->{formatter::COMPLETION_DATE}, $format);
    }

    /**
     * @param goal_task $task
     * @param string $format
     * @return string
     */
    protected function created_date_formatter(goal_task $task, string $format): string {
        return $this->format_date($task->{formatter::CREATION_DATE}, $format);
    }

    /**
     * @param goal_task $task
     * @return string
     */
    protected function description_formatter(goal_task $task): string {
        return format_text($task->{formatter::DESC}, FORMAT_HTML);
    }

    /**
     * @param goal_task $task
     * @return int
     */
    protected function goal_id_formatter(goal_task $task): int {
        return $task->{formatter::GOAL_ID};
    }

    /**
     * @param goal_task $task
     * @return goal_task_resource
     */
    protected function resource_formatter(goal_task $task): goal_task_resource {
        return $task->resource;
    }

    /**
     * @param goal_task $task
     * @return int
     */
    protected function task_id_formatter(goal_task $task): int {
        return $task->{formatter::ID};
    }

    /**
     * @param goal_task $task
     * @param string $format
     * @return string
     */
    protected function updated_date_formatter(goal_task $task, string $format): string {
        return $this->format_date($task->{formatter::UPDATED_DATE}, $format);
    }

    /**
     * @param int|null $date
     * @param string $format
     * @return string
     */
    protected function format_date(?int $date, string $format): string {
        if (is_null($date)) {
            return '';
        }
        return userdate(
            $date,
            get_string(date_format::get_lang_string($format), 'langconfig')
        );
    }

    /**
     * Creates a test goal task.
     *
     * @return goal_task the goal task.
     */
    private function create_test_goal_task(): goal_task {
        $this->setAdminUser();

        $cfg = goal_generator_config::new(['number_of_tasks' => 1]);
        $task = generator::instance()->create_goal($cfg)->tasks->first();

        goal_task_resource::create($task, course_type::create());
        $task->refresh(true);

        return $task;
    }
}
