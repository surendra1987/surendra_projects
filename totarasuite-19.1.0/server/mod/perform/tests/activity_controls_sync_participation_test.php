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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\settings\controls\control_manager;
use mod_perform\models\activity\settings\controls\sync_participant_instance_closure_option;
use mod_perform\models\activity\settings\controls\sync_participant_instance_creation_option;
use mod_perform\testing\activity_controls_trait;

/**
 * @group perform
 */
class mod_perform_activity_controls_sync_participation_test extends testcase {

    use activity_controls_trait;

    public function test_sync_participation_control_structure(): void {
        $activity = $this->create_activity();
        $controls = (new control_manager($activity->id))->get_controls(['sync_participation']);
        $control = $controls['sync_participation'];

        $expected = array_merge(
            self::sync_participant_instance_options(),
            [
                'override_global_participation_settings' => false,
                'sync_participant_instance_creation_type' => 0,
                'sync_participant_instance_closure_type' =>
                    sync_participant_instance_closure_option::CLOSURE_DISABLED->name
            ]
        );

        static::assertEquals($expected, $control);
    }

    public function test_sync_participation_control_update(): void {
        $activity = $this->create_activity();

        $pi_closure = sync_participant_instance_closure_option::CLOSE_ALL;
        $updates = [
            activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS => true,
            activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION => true,
            activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE => $pi_closure->value
        ];
        $activity->settings->update($updates);

        $controls = (new control_manager($activity->id))->get_controls(['sync_participation']);
        $control = $controls['sync_participation'];

        $expected = array_merge(
            self::sync_participant_instance_options(),
            [
                'override_global_participation_settings' => true,
                'sync_participant_instance_creation_type' => 1,
                'sync_participant_instance_closure_type' => $pi_closure->name
            ]
        );

        static::assertEquals($expected, $control);
    }

    /**
     * Returns the sync participant instance creation and closure display options.
     *
     * @return array<string,array<string,mixed>> the options
     */
    private static function sync_participant_instance_options(): array {
        $default_suffix = ' (global default)';
        $close_options = array_map(
            fn(sync_participant_instance_closure_option $it): array => [
                'id' => $it->name,
                'desc' => $it->description(),
                'label' => ($it->value === 0 ? $it->label() . $default_suffix : $it->label())
            ],
            sync_participant_instance_closure_option::cases()
        );

        $create_options = array_map(
            fn(sync_participant_instance_creation_option $it): array => [
                'id' => $it->value,
                'desc' => $it->description(),
                'label' => ($it->value === 0 ? $it->label() . $default_suffix : $it->label())
            ],
            sync_participant_instance_creation_option::cases()
        );

        return [
            'sync_participant_instance_closure_options' => $close_options,
            'sync_participant_instance_creation_options' => $create_options
        ];
    }
}
