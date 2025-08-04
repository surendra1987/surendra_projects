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

use core_phpunit\testcase;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\settings\controls\control_manager;
use mod_perform\models\activity\track;
use mod_perform\state\activity\draft;
use mod_perform\testing\activity_controls_trait;
use mod_perform\testing\activity_generator_configuration;
use totara_core\dates\date_time_setting;

/**
 * @group perform
 */

class mod_perform_activity_controls_closure_test extends testcase {

    use activity_controls_trait;

    public function test_closure_control_structure(): void {
        $activity = $this->create_activity();

        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control = $controls['closure'];

        self::assertEquals(
            [
                'checkbox_options' => [
                    [
                        'id' => 'close_on_completion',
                        'label' => 'Auto-close when all sections completed',
                        'desc' => 'The participant\'s own activity will be closed once they have completed and submitted all sections.',
                    ],
                    [
                        'id' => 'close_on_due_date',
                        'label' => 'Close on due date (no due date set)',
                        'desc' => 'The activity will be closed on the set due date.',
                    ],
                    [
                        'id' => 'manual_close',
                        'label' => 'Manual close',
                        'desc' => 'Allow responding participants to manually close their own activity.',
                    ],
                ],
                'draft' => false,
                'mutable' => [
                    'close_on_due_date' => false,
                ],
                'valid_closure_state' => true,
                'value' => [
                    'close_on_completion' => false,
                    'close_on_due_date' => false,
                    'close_on_section_submission' => false,
                    'manual_close' => false
                ],
            ],
            $closure_control
        );
    }

    public function test_closure_control_due_date(): void {
        $activity = $this->create_activity();

        /** @var track $track */
        $track = $activity->get_default_track();
        $track->set_schedule_closed_fixed(new date_time_setting(111), new date_time_setting(222))
            ->set_due_date_fixed(new date_time_setting(333))
            ->update();

        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control = $controls['closure'];
        self::assertEquals('Close on due date', $closure_control['checkbox_options'][1]['label']);
        self::assertTrue($closure_control['mutable']['close_on_due_date']);

        $track->set_due_date_disabled()
            ->update();

        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control = $controls['closure'];
        self::assertEquals('Close on due date (no due date set)', $closure_control['checkbox_options'][1]['label']);
        self::assertFalse($closure_control['mutable']['close_on_due_date']);
    }

    public function test_closure_control_draft(): void {
        $configuration = activity_generator_configuration::new()
            ->set_activity_status(draft::get_code())
            ->set_number_of_elements_per_section(1);
        $activity = $this->create_activity($configuration);

        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control = $controls['closure'];
        self::assertTrue($closure_control['draft']);

        $activity->activate();
        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control = $controls['closure'];
        self::assertFalse($closure_control['draft']);
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

        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control = $controls['closure'];
        self::assertSame($expected_result, $closure_control['valid_closure_state']);
    }

    public function test_visibility_control_main_values(): void {
        $activity = $this->create_activity();

        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control_values = $controls['closure']['value'];
        self::assertFalse($closure_control_values['close_on_completion']);
        self::assertFalse($closure_control_values['close_on_due_date']);
        self::assertFalse($closure_control_values['close_on_section_submission']);

        $activity->settings->update([activity_setting::CLOSE_ON_DUE_DATE => true]);

        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control_values = $controls['closure']['value'];
        self::assertFalse($closure_control_values['close_on_completion']);
        self::assertTrue($closure_control_values['close_on_due_date']);
        self::assertFalse($closure_control_values['close_on_section_submission']);

        // 'section_closure' value is adopted from 'close_on_completion' for now.
        $activity->settings->update([activity_setting::CLOSE_ON_COMPLETION => true,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => true]
        );

        $controls = (new control_manager($activity->id))->get_controls(['closure']);
        $closure_control_values = $controls['closure']['value'];
        self::assertTrue($closure_control_values['close_on_completion']);
        self::assertTrue($closure_control_values['close_on_due_date']);
        self::assertTrue($closure_control_values['close_on_section_submission']);
    }

    public function test_manual_close_values(): void {
        $activity = $this->create_activity();
        // Operate #1.
        $cm = new control_manager($activity->id);
        $controls = $cm->get_controls(['closure']);
        // Assert.
        $checkbox_manual_control_item_found_in_response = array_filter($controls['closure']['checkbox_options'],
            fn($item) => ($item['id'] === 'manual_close' && $item['label'] === 'Manual close'
                && $item['desc'] === 'Allow responding participants to manually close their own activity.')
        );
        self::assertNotEmpty($checkbox_manual_control_item_found_in_response);
        self::assertFalse($controls['closure']['value']['manual_close']);

        // Operate #2.
        $activity->settings->update([activity_setting::MANUAL_CLOSE => true]);
        // Assert.
        $controls = $cm->get_controls(['closure']);
        self::assertTrue($controls['closure']['value']['manual_close']);
    }
}
