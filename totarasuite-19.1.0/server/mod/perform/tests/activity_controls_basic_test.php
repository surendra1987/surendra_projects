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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use mod_perform\entity\activity\activity as activity_entity;
use mod_perform\models\activity\settings\controls\control_manager;
use mod_perform\testing\activity_controls_trait;

/**
 * @group perform
 */

class mod_perform_activity_controls_basic_test extends testcase {

    use activity_controls_trait;

    public function test_basic_control_structure(): void {
        $activity = $this->create_activity();
        builder::table(activity_entity::TABLE)->update([
            'name' => 'Test activity name',
            'type_id' => 2,
        ]);

        $controls = (new control_manager($activity->id))->get_controls(['basic']);
        $basic_control = $controls['basic'];

        self::assertEquals(
            [
                "name" => [
                    "display_value" => "Test activity name",
                    "edit_value" => "Test activity name",
                    "max_length" => 1024,
                ],
                "description" => [
                    "display_value" => "test description",
                    "edit_value" => "test description",
                ],
                "type" => [
                    "id" => "2",
                    "display_name" => "Check-in"
                ],
                "state_details" => [
                    "name" => "ACTIVE",
                    "display_name" => "Active"
                ],
                "types" => [
                    [
                        "id" => "1",
                        "label" => "Appraisal"
                    ],
                    [
                        "id" => "2",
                        "label" => "Check-in"
                    ],
                    [
                        "id" => "3",
                        "label" => "Feedback"
                    ]
                ]
            ],
            $basic_control
        );
    }

    public function test_basic_control_multilang_formatting(): void {
        // Enable the multilang filter and set it to apply to headings and content.
        filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', true);
        filter_manager::reset_caches();

        $activity = $this->create_activity();

        $multilang_name = '<span lang="en" class="multilang">English name</span><span lang="de" class="multilang">German name</span>';
        $multilang_description = '<span lang="en" class="multilang">English description</span><span lang="de" class="multilang">German description</span>';

        builder::table(activity_entity::TABLE)->update([
            'name' => $multilang_name,
            'description' => $multilang_description,
        ]);

        $controls = (new control_manager($activity->id))->get_controls(['basic']);
        $basic_control = $controls['basic'];

        self::assertEquals('English name', $basic_control['name']['display_value']);
        self::assertEquals($multilang_name, $basic_control['name']['edit_value']);

        self::assertEquals('English description', $basic_control['description']['display_value']);
        self::assertEquals($multilang_description, $basic_control['description']['edit_value']);
    }
}
