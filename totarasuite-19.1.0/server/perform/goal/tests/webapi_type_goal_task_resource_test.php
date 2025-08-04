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
use perform_goal\formatter\goal_task_resource_formatter as formatter;
use perform_goal\model\goal;
use perform_goal\model\goal_task_resource;
use perform_goal\model\goal_task_type\type;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_webapi_type_goal_task_resource_test extends testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_goal_task_resource';

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
            self::TYPE, $field, $this->create_test_goal_task_resource()
        );
    }

    /**
     * Test data for test_valid
     */
    public static function td_valid(): array {
        return [
            'resource id' => [
                'resource_id_formatter',
                formatter::RESOURCE_ID,
            ],
            'resource type' => [
                'resource_type_formatter',
                formatter::RESOURCE_TYPE
            ],
            'created date (long format)' => [
                'created_date_formatter',
                formatter::CREATION_DATE,
                date_format::FORMAT_DATELONG
            ],
            'created date (short format)' => [
                'created_date_formatter',
                formatter::CREATION_DATE,
                date_format::FORMAT_DATESHORT
            ],
            'id' => [
                'id_formatter',
                formatter::ID
            ],
            'task id' => [
                'task_id_formatter',
                formatter::TASK_ID
            ],
        ];
    }

    /**
     * @dataProvider td_valid
     */
    public function test_valid(
        string $formatterMethod,
        string $field,
        ?string $format = null
    ): void {
        $args = $format ? ['format' => $format] : [];
        $task = $this->create_test_goal_task_resource();

        $this->assertEquals(
            $this->$formatterMethod($task, $format),
            $this->resolve_graphql_type(self::TYPE, $field, $task, $args),
            'wrong value'
        );
    }

    // Formatter methods

    /**
     * @param goal_task_resource $resource
     * @return int|null
     */
    protected function resource_id_formatter(goal_task_resource $resource): ?int {
        return $resource->type->resource_id();
    }

    /**
     * @param goal_task_resource $resource
     * @return int|null
     */
    protected function resource_type_formatter(goal_task_resource $resource): ?int {
        return $resource->type->code();
    }

    /**
     * @param goal_task_resource $resource
     * @param string $format
     * @return string
     * @throws coding_exception
     */
    protected function created_date_formatter(goal_task_resource $resource, string $format): string {
        return $this->format_date($resource->{formatter::CREATION_DATE}, $format);
    }

    /**
     * @param goal_task_resource $resource
     * @return int
     */
    protected function id_formatter(goal_task_resource $resource): int {
        return $resource->{formatter::ID};
    }

    /**
     * @param goal_task_resource $resource
     * @return int
     */
    protected function task_id_formatter(goal_task_resource $resource): int {
        return $resource->{formatter::TASK_ID};
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
     * Creates a test goal task resource.
     *
     * @return goal_task_resource the goal task resource.
     */
    private function create_test_goal_task_resource(): goal_task_resource {
        $this->setAdminUser();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course();
        $this->executeAdhocTasks();

        $cfg = goal_generator_config::new(['number_of_tasks' => 1]);

        return goal_task_resource::create(
            generator::instance()->create_goal($cfg)->tasks->first(),
            type::by_code(course_type::TYPECODE, $course->id)
        );
    }
}
