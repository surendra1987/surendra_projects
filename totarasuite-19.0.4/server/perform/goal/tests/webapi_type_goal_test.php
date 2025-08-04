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
use perform_goal\formatter\goal as goal_formatter;
use perform_goal\model\goal;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform_goal
 */
class perform_goal_webapi_type_goal_test extends testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_goal';

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(goal::class);

        $this->resolve_graphql_type(self::TYPE, 'name', new \stdClass());
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
            self::TYPE, $field, generator::instance()->create_goal()
        );
    }

    /**
     * @covers ::resolve
     */
    public function test_resolve(): void {
        $this->setAdminUser();

        $description = document_helper::json_encode_document(
            document_helper::create_document_from_content_nodes([
                paragraph::create_json_node_with_content_nodes([
                    text::create_json_node_from_text(
                        '<h1>This is a <strong>test</strong> description</h1>'
                    )
                ])
            ])
        );

        $cfg = goal_generator_config::new(['description' => $description]);
        $goal = generator::instance()->create_goal($cfg);

        $date_fmt = new date_field_formatter(
            date_format::FORMAT_DATELONG, $goal->context
        );

        $goal_formatter = new goal_formatter($goal, $goal->context);

        $testcases = [
            'id' => ['id', $goal->id],
            'context_id' => ['context_id', $goal->context_id],
            'owner' => ['owner', $goal->owner],
            'user' => ['user', $goal->user],
            'name' => ['name', $goal->name],
            'id_number' => ['id_number', $goal->id_number],
            'assignment type' => ['assignment_type', $goal->assignment_type],
            'plugin name' => ['plugin_name', $goal->plugin_name],

            'description' => [
                'description',
                (new text_field_formatter(format::FORMAT_HTML, $goal->context))
                    ->set_pluginfile_url_options(
                        $goal->context, 'perform_goal', 'description', $goal->id
                    )
                    ->format($description)
            ],

            'start date' => ['start_date', $date_fmt->format($goal->start_date)],
            'target type' => ['target_type', $goal->target_type],
            'target date' => ['target_date', $date_fmt->format($goal->target_date)],
            'target_value' => ['target_value', $goal_formatter->format('target_value')],
            'current_value' => ['current_value', $goal_formatter->format('current_value'), null],

            'current_value_updated_at ' => [
                'current_value_updated_at',
                $date_fmt->format($goal->current_value_updated_at),
            ],

            'status' => ['status', $goal->status],

            'status_updated_at ' => [
                'status_updated_at',
                $date_fmt->format($goal->status_updated_at),
            ],

            'closed_at' => ['closed_at', $date_fmt->format($goal->closed_at)],
            'created_at' => ['created_at', $date_fmt->format($goal->created_at)],
            'updated_at' => ['updated_at', $date_fmt->format($goal->updated_at)]
        ];

        foreach ($testcases as $id => $testcase) {
            [$field, $expected] = $testcase;

            $value = $this->resolve_graphql_type(self::TYPE, $field, $goal, []);
            self::assertEquals($expected, $value, "[$id] wrong value");
        }
    }
}
