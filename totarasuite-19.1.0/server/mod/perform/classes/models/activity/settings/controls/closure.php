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

namespace mod_perform\models\activity\settings\controls;

use mod_perform\models\activity\activity_setting;
use mod_perform\state\activity\draft;

/**
 * Class closure control
 *
 * @package mod_perform\models\activity\settings\control
 */
class closure extends control {

    /**
     * @inheritDoc
     */
    public function get(): array {

        $close_on_due_date_setting = (bool)$this->activity->settings->lookup(activity_setting::CLOSE_ON_DUE_DATE);
        $close_on_completion_setting = (bool)$this->activity->settings->lookup(activity_setting::CLOSE_ON_COMPLETION);
        $close_on_section_submission = (bool)$this->activity->settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION);
        $manual_close_setting = (bool)$this->activity->settings->lookup(activity_setting::MANUAL_CLOSE);

        $due_date_is_enabled = $this->activity->get_default_track()->due_date_is_enabled;

        return [
            'checkbox_options' => [
                [
                    'id' => activity_setting::CLOSE_ON_COMPLETION,
                    'label' => get_string('activity_control_automatic_closure_on_all_sections_complete', 'mod_perform'),
                    'desc' => get_string('activity_control_automatic_closure_on_all_sections_complete_help', 'mod_perform'),
                ],
                [
                    'id' => activity_setting::CLOSE_ON_DUE_DATE,
                    'label' => $due_date_is_enabled
                        ? get_string('activity_control_automatic_closure_on_due_date', 'mod_perform')
                        : get_string('activity_control_automatic_closure_on_due_date_no_due_date', 'mod_perform'),
                    'desc' => get_string('activity_control_automatic_closure_on_due_date_help', 'mod_perform')
                ],
                [
                    'id' => activity_setting::MANUAL_CLOSE,
                    'label' => get_string('activity_control_allow_manual_close_participant_instance_label', 'mod_perform'),
                    'desc' => get_string('activity_control_allow_manual_close_participant_instance_help', 'mod_perform')
                ],
            ],
            'draft' => $this->activity->get_status_state()::get_code() === draft::get_code(),
            'mutable' => [
                activity_setting::CLOSE_ON_DUE_DATE => $due_date_is_enabled,
            ],
            'valid_closure_state' => $this->is_valid_closure_state(),
            'value' => [
                activity_setting::CLOSE_ON_COMPLETION => $close_on_completion_setting,
                activity_setting::CLOSE_ON_DUE_DATE => $close_on_due_date_setting,
                activity_setting::CLOSE_ON_SECTION_SUBMISSION => $close_on_section_submission,
                activity_setting::MANUAL_CLOSE => $manual_close_setting
            ],
        ];
    }
}
