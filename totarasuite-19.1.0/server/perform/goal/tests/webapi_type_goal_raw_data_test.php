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

use core\format;
use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core\json_editor\node\text;
use core_phpunit\testcase;
use core\webapi\formatter\field\text_field_formatter;
use perform_goal\model\goal_raw_data;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_core\dates\date_time_setting;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform_goal
 */
class perform_goal_webapi_type_goal_raw_data_test extends testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_goal_raw_data';

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(goal_raw_data::class);

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
            self::TYPE,
            $field,
            new goal_raw_data(generator::instance()->create_goal())
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
        $raw = new goal_raw_data(generator::instance()->create_goal($cfg));

        $testcases = [
            'available statuses' => [
                'available_statuses', $raw->available_statuses
            ],
            'description' => [
                'description',
                (new text_field_formatter(format::FORMAT_RAW, $raw->context))
                    ->set_pluginfile_url_options(
                        $raw->context, 'perform_goal', 'description', $raw->id
                    )
                    ->format($raw->description)
            ],
            'start date' => [
                'start_date', new date_time_setting($raw->start_date)
            ],
            'target date' => [
                'target_date', new date_time_setting($raw->target_date)
            ]
        ];

        foreach ($testcases as $id => $testcase) {
            [$field, $expected] = $testcase;

            $value = $this->resolve_graphql_type(self::TYPE, $field, $raw, []);
            self::assertEquals($expected, $value, "[$id] wrong value");
        }
    }
}
