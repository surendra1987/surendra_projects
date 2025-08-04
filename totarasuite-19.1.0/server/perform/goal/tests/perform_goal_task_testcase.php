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
use core\format;
use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core\json_editor\node\text;
use core_phpunit\testcase;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\text_field_formatter;

abstract class perform_goal_task_testcase extends testcase {
    /**
     * Checks if the goal tasks match.
     *
     * @param context $goal_context parent goal context.
     * @param object $expected expected goal task. This is either a 'real' goal
     *        task model or a stdClass with *identical* properties as a goal
     *        task.
     * @param object $actual actual goal task. This is either a 'real' goal task
     *        model or the stdClass created from a graphql return.
     */
    final protected static function assert_goal_task(
        context $goal_context,
        object $expected,
        object $actual
    ): void {
        $date_fmt = new date_field_formatter(
            date_format::FORMAT_DATELONG, $goal_context
        );

        $html_fmt = (new text_field_formatter(format::FORMAT_HTML, $goal_context))
            ->set_pluginfile_url_options(
                $goal_context, 'perform_goal_task', 'description', $expected->id
            );

        $from_gql = $actual instanceof stdClass;

        self::assertEquals($expected->id, $actual->id);
        self::assertEquals($expected->goal_id, $actual->goal_id);

        self::assertEquals(
            $html_fmt->format($expected->description),
            $from_gql
                ? $actual->description
                : $html_fmt->format($actual->description),
            'wrong description'
        );

        self::assertEquals(
            $html_fmt->format($expected->description),
            $from_gql
                ? $actual->description
                : $html_fmt->format($actual->description)
        );

        self::assertEquals(
            $date_fmt->format($expected->completed_at),
            $from_gql
                ? $actual->completed_at
                : $date_fmt->format($actual->completed_at)
        );

        self::assertEquals($expected->resource_exists, $actual->resource_exists);
        self::assertEquals($expected->resource_can_view, $actual->resource_can_view);

        $expected_resource = $expected->resource;
        if (is_null($expected_resource)) {
            self::assertEmpty($actual->resource);
        } else {
            self::assert_goal_task_resource(
                $expected->resource,
                $from_gql ? (object)$actual->resource : $actual->resource
            );
        }
    }

    /**
     * Checks if the goal task resource match.
     *
     * @param object $expected expected goal task resource. This is either a
     *        'real' goal task resourcemodel or a stdClass with *identical*
     *        properties as a goal task resource
     * @param object $actual actual goal task resource This is either a 'real'
     *        goal task resourcemodel or the stdClass created from a graphql
     *        return.
     */
    final protected static function assert_goal_task_resource(
        object $expected,
        object $actual
    ): void {
        $from_gql = $actual instanceof stdClass;

        self::assertEquals($expected->id, $actual->id);
        self::assertEquals($expected->task_id, $actual->task_id);

        self::assertEquals(
            $expected->type->code(),
            $from_gql ? $actual->resource_type : $actual->type->code()
        );

        self::assertEquals(
            $expected->type->resource_id(),
            $from_gql ? $actual->resource_id : $actual->type->resource_id()
        );

        self::assert_resource_custom_data(
            $expected->resource_custom_data, $actual->resource_custom_data
        );
    }

    /**
     * Checks if the goal task's resource custom data is valid.
     *
     * @param null|string|array $expected expected goal task resource custom data.
     * @param null|string|array $actual expected goal task resource custom data.
     *
     * @return void
     */
    final protected static function assert_resource_custom_data(
        $expected,
        $actual
    ): void {
        $expected_data = is_string($expected)
            ? document_helper::parse_document($expected)
            : $expected;

        $actual_data = is_string($actual)
            ? document_helper::parse_document($actual)
            : $actual;

        self::assertEquals($expected_data, $actual_data);
    }

    /**
     * Generates a jsondoc goal task description.
     *
     * @param string $descr the 'goal' task description
     *
     * @return string the jsondoc.
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
