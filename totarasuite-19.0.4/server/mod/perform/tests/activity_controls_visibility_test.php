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
use mod_perform\models\activity\activity_setting;
use mod_perform\state\activity\draft;
use mod_perform\models\activity\settings\controls\control_manager;
use mod_perform\testing\activity_controls_trait;

/**
 * @group perform
 */

class mod_perform_activity_controls_visibility_test extends testcase {

    use activity_controls_trait;

    public function test_visibility_control_structure(): void {
        $activity = $this->create_activity();

        $controls = (new control_manager($activity->id))->get_controls(['visibility']);
        $visibility_control = $controls['visibility'];

        self::assertEquals(
            [
                'anonymous_responses' => [
                    'value' => false,
                    'mutable' => false,
                ],
                'valid_closure_state' => true,
                'visibility_condition' => [
                    'value' => 0,
                    'anonymous_value' => 2,
                    'options' => [
                        [
                            'name' => 'Show responses when the section is submitted',
                            'value' => 0,
                        ],
                        [
                            'name' => 'Show responses when the section is submitted and closed',
                            'value' => 1,
                        ],
                        [
                            'name' => 'Show responses when all participants have submitted and closed the activity',
                            'value' => 2,
                        ],
                    ]
                ],
            ],
            $visibility_control
        );
    }

    public function test_visibility_control_anonymous_responses(): void {
        $activity = $this->create_activity();

        $controls = (new control_manager($activity->id))->get_controls(['visibility']);
        $visibility_control = $controls['visibility'];

        self::assertEquals(
            [
                'value' => false,
                'mutable' => false,
            ],
            $visibility_control['anonymous_responses']
        );

        builder::table(activity_entity::TABLE)
            ->where('id', $activity->id)
            ->update(['status' => draft::get_code()]);
        $activity->refresh();

        $controls = (new control_manager($activity->id))->get_controls(['visibility']);
        $visibility_control = $controls['visibility'];
        self::assertEquals(
            [
                'value' => false,
                'mutable' => true,
            ],
            $visibility_control['anonymous_responses']
        );

        $activity->set_anonymous_setting(true)->update();

        $controls = (new control_manager($activity->id))->get_controls(['visibility']);
        $visibility_control = $controls['visibility'];
        self::assertEquals(
            [
                'value' => true,
                'mutable' => true,
            ],
            $visibility_control['anonymous_responses']
        );
    }

    /**
     * @dataProvider visibility_control_valid_closure_state_data_provider
     */
    public function test_visibility_control_valid_closure_state(
        int $visibility_condition,
        bool $close_on_completion,
        bool $close_on_due_date,
        bool $expected_result
    ): void {
        $activity = $this->create_activity();
        $activity->settings->update([activity_setting::VISIBILITY_CONDITION => $visibility_condition]);
        $activity->settings->update([activity_setting::CLOSE_ON_COMPLETION => $close_on_completion]);
        $activity->settings->update([activity_setting::CLOSE_ON_DUE_DATE => $close_on_due_date]);

        $controls = (new control_manager($activity->id))->get_controls(['visibility']);
        $visibility_control = $controls['visibility'];
        self::assertSame($expected_result, $visibility_control['valid_closure_state']);
    }
}
