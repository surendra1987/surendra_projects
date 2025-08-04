<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
use perform_goal\interactor\goal_interactor;
use perform_goal\model\status\status;
use perform_goal\model\goal_raw_data;
use totara_core\dates\date_time_setting;

abstract class perform_goal_webapi_resolver_test extends testcase {
    /**
     * Checks if the goals match.
     *
     * @param object $expected expected goal.This is either a 'real' goal model
     *        or a stdClass with _identical properties as a goal.
     * @param object $actual actual goal. This is either a 'real' goal model or
     *        the stdClass created from a graphql return.
     */
    final protected static function assert_goal(
        object $expected,
        object $actual
    ): void {
        $user = $expected->user;
        if (is_null($user)) {
            self::assertNull($actual->user);
        } else {
            self::assertEquals($expected->user_id, ((object)$actual->user)->id);
        }

        self::assertEquals($expected->id, $actual->id);
        self::assertEquals($expected->context_id, $actual->context_id);
        self::assertEquals($expected->owner_id, ((object)$actual->owner)->id);
        self::assertEquals($expected->name, $actual->name);
        self::assertEquals($expected->id_number, $actual->id_number);
        self::assertEquals($expected->target_type, $actual->target_type);
        self::assertEquals($expected->assignment_type, $actual->assignment_type);
        self::assertEquals($expected->plugin_name, $actual->plugin_name);

        self::assertEqualsWithDelta(
            $expected->target_value, $actual->target_value, 0.0001
        );

        self::assertEqualsWithDelta(
            $expected->current_value, $actual->current_value, 0.0001
        );

        $from_gql = $actual instanceof stdClass;
        $unpack_status = fn(status $it): array => [
            'id' => $it::get_code(), 'label' => $it->get_label()
        ];

        self::assertEquals(
            $unpack_status($expected->status),
            $from_gql ? $actual->status : $unpack_status($actual->status)
        );

        $html_fmt = (new text_field_formatter(format::FORMAT_HTML, $expected->context))
            ->set_pluginfile_url_options(
                $expected->context, 'perform_goal', 'description', $expected->id
            );

        self::assertEquals(
            $html_fmt->format($expected->description),
            $from_gql
                ? $actual->description
                : $html_fmt->format($actual->description)
        );

        $date_fmt = new date_field_formatter(
            date_format::FORMAT_DATELONG, $expected->context
        );

        $date_fields = [
            'closed_at',
            'created_at',
            'current_value_updated_at',
            'status_updated_at',
            'start_date',
            'target_date',
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
    }

    /**
     * Checks if the goal raw data match.
     *
     * @param goal_raw_data $expected expected goal raw data.
     * @param object $actual actual goal raw data. This is either a 'real' goal
     *        raw data object or a stdClass created from the graphql return.
     */
    final protected static function assert_goal_raw_data(
        goal_raw_data $expected,
        object $actual
    ): void {
        $exp_statuses = $expected->available_statuses;
        $act_statuses = $actual->available_statuses;
        self::assertCount(count($exp_statuses), $act_statuses);

        $from_gql = $actual instanceof stdClass;
        if ($from_gql) {
            foreach ($act_statuses as $status) {
                ['id' => $id, 'label' => $label] = $status;

                $exp_label = $exp_statuses[$id] ?? null;
                self::assertNotNull($exp_label);
                self::assertEquals($exp_label, $label);
            }
        } else {
            self::assertEquals($exp_statuses, $act_statuses);
        }

        self::assertEquals(
            (new text_field_formatter(format::FORMAT_RAW, $expected->context))
                ->set_pluginfile_url_options(
                    $expected->context,
                    'perform_goal',
                    'description',
                    $expected->id
                )
                ->format($expected->description),
            $actual->description
        );

        $date_fmt = function (int $timestamp): array {
            $dt = new date_time_setting($timestamp);
            return [
                'iso' => $dt->get_iso()
            ];
        };

        $date_fields = ['start_date', 'target_date'];
        foreach ($date_fields as $field) {
            $exp_iso = $date_fmt($expected->$field);
            $act_field = $actual->$field;

            self::assertEquals(
                $exp_iso,
                $from_gql ? $act_field : $date_fmt($act_field),
                "wrong $field value"
            );
        }
    }

    /**
     * Checks if the goal permissions match.
     *
     * @param goal_interactor $expected expected goal permissions.
     * @param object $actual actual goal permissions. This is either a 'real'
     *        goal interactor object or a stdClass created from the graphql
     *        return.
     */
    final protected static function assert_goal_permissions(
        goal_interactor $expected,
        object $actual
    ): void {
        $from_gql = $actual instanceof stdClass;
        self::assertEquals(
            $expected->can_manage(),
            $from_gql ? $actual->can_manage : $actual->can_manage()
        );

        self::assertEquals(
            $expected->can_set_progress(),
            $from_gql ? $actual->can_update_status : $actual->can_set_progress()
        );
    }

    /**
     * Generates a jsondoc goal description.
     *
     * @param string $descr the 'goal' description
     *
     * @return string the jsondoc
     */
    final protected function jsondoc_description(string $description): string {
        return document_helper::json_encode_document(
            document_helper::create_document_from_content_nodes([
                paragraph::create_json_node_with_content_nodes([
                    text::create_json_node_from_text($description)
                ])
            ])
        );
    }
}
