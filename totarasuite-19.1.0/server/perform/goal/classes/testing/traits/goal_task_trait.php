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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\testing\traits;

use context_system;
use core\date_format;
use core\format;
use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core\json_editor\node\text;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;

use perform_goal\model\goal;
use perform_goal\model\goal_task;
use perform_goal\settings_helper;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;

trait goal_task_trait {

    /**
     * Checks if the tasks match.
     *
     * @param object $expected expected task.This is either a 'real' goal_task model
     *        or a stdClass with *identical* properties as a task.
     * @param object $actual actual task. This is either a 'real' goal_task model or
     *        the stdClass created from a graphql return.
     */
    final protected static function assert_goal_task(
        object $expected,
        object $actual
    ): void {

        self::assertEquals($expected->id, $actual->id, 'wrong goal task id');
        self::assertEquals(
            $expected->goal_id, $actual->goal_id, 'wrong goal id'
        );

        $context = context_system::instance();
        $from_gql = $actual instanceof \stdClass;
        $string_fmt = (new string_field_formatter(format::FORMAT_PLAIN, $context));

        self::assertEquals(
            $string_fmt->format($expected->description),
            $from_gql
                ? $actual->description
                : $string_fmt->format($actual->description),
            'wrong description'
        );

        $date_fmt = new date_field_formatter(
            date_format::FORMAT_DATELONG, $context
        );

        $date_fields = [
            'completed_at',
            'created_at',
            'updated_at'
        ];

        foreach ($date_fields as $field) {
            $exp_field = $expected->$field;
            $act_field = $actual->$field;

            self::assertEquals(
                $date_fmt->format($exp_field),
                $from_gql ? $act_field : $date_fmt->format($act_field),
                "wrong $field value"
            );
        }

        if ($expected->resource) {
            $expected->resource->__typename = 'perform_goal_goal_task_resource';
            $expected->resource->created_at = $date_fmt->format($expected->resource->created_at);
        }

        self::assertEquals(
            (object)$expected->resource,
            (object)$actual->resource
        );
    }

    /**
     * Computes the expected results after a upsert perform goal task operation.
     *
     * @param int $goal_task_id created goal task id. Unfortunately, there is no
     *        real way to compute the database timestamps; so this method has to
     *        get them from the database via the given id.
     * @param stdClass $create_data the goal task values being upserted. This comes
     *        from the 'input' section of the args returned from self::setup_env().
     *
     * @return array[] (expected goal task) tuple.
     */
    final protected static function expected_results(
        int $goal_task_id,
        \stdClass $create_data
    ): array {

        $goal_task = goal_task::load_by_id($goal_task_id);
        $goal_task_resource = $goal_task->get_resource();

        $exp_goal_task = (object)[
            'id' => $goal_task->id,
            'goal_id' => $create_data->goal_id,
            'description' => $create_data->description,
            'completed_at' => $goal_task->completed_at,
            'created_at' => $goal_task->created_at,
            'updated_at' => $goal_task->updated_at,
        ];

        $resource = null;
        if (!is_null($goal_task_resource)) {
            $resource = (object)[
                'id' => (string)$goal_task_resource->id,
                'task_id' => $goal_task_resource->task_id,
                'resource_id' => $create_data->resource_id ?? $goal_task_resource->resource_id,
                'resource_type' => $create_data->resource_type ?? $goal_task_resource->resource_type,
                'created_at' => $goal_task_resource->created_at
            ];
        }
        $exp_goal_task->resource = $resource;

        return [
            $exp_goal_task
        ];
    }

    /**
     * Generates a goal data
     * @return mixed
     */
    final protected function setup_env() {

        $this->setAdminUser();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course();
        $this->executeAdhocTasks();

        settings_helper::enable_perform_goals();

        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $data = goal_generator_config::new(['user_id' => $user->id]);
        /** @var goal $goal */
        $goal = generator::instance()->create_goal($data);
        return [$goal, $course];
    }


    /**
     * Generates a jsondoc goal description.
     *
     * @param string $descr the 'goal' description
     *
     * @return string the jsondoc
     */
    final protected static function jsondoc_description(string $description): string {
        return document_helper::json_encode_document(
            document_helper::create_document_from_content_nodes([
                paragraph::create_json_node_with_content_nodes([
                    text::create_json_node_from_text($description)
                ])
            ])
        );
    }
}