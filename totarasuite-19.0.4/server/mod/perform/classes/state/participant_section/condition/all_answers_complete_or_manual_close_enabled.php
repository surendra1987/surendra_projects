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
 * @author Scott Davies <scott.davies@totara.com>
 * @package mod_perform
 */

namespace mod_perform\state\participant_section\condition;

use mod_perform\models\response\participant_section;
use mod_perform\state\condition;
use mod_perform\state\participant_section\closed;
use mod_perform\entity\activity\activity_setting as activity_setting_entity;

defined('MOODLE_INTERNAL') || die();

class all_answers_complete_or_manual_close_enabled extends condition {
    public function pass(): bool {
        /** @var participant_section $participant_section */
        $participant_section = $this->object;

        if ($participant_section->availability === closed::get_code()) {
            // Allow a participant_section that has been manually closed to also be marked as complete.
            $activity_perform_setting_has_manual_close_enabled = activity_setting_entity::repository()
                ->where('activity_id', $participant_section->section->activity_id)
                ->where('name', 'manual_close')
                ->where('value', '1')
                ->exists();
            if ($activity_perform_setting_has_manual_close_enabled) {
                return true;
            }
        }

        return (new all_answers_complete($this->object))->pass();
    }
}
