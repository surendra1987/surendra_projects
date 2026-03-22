<?php
/*
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

namespace mod_perform\models\activity\settings\controls;

use coding_exception;

/**
 * Indicates whether participant instances are created when a new user enters a
 * relationship an that activity.
 */
enum sync_participant_instance_creation_option : int {
    // Participant instances are not created when a new user enters the activity.
    case CREATION_DISABLED = 0;

    // Participant instances are created when a new user enters the activity.
    case CREATION_ENABLED = 1;

    /**
     * Returns a localized string describing this enum.
     *
     * @return string the description.
     */
    public function description(): string {
        $id = match ($this) {
            self::CREATION_DISABLED =>
                'perform_sync_participant_instance_creation_setting_disabled:desc',

            self::CREATION_ENABLED =>
                'perform_sync_participant_instance_creation_setting_enabled:desc',

            default => throw new coding_exception(
                'No description for sync_participant_instance_creation_options::'
                    . $this->value
            )
        };

        return get_string($id, 'mod_perform');
    }

    /**
     * Returns a localized display label for this enum.
     *
     * @return string the description.
     */
    public function label(): string {
        $id = match ($this) {
            self::CREATION_DISABLED =>
                'perform_sync_participant_instance_creation_setting_disabled:label',

            self::CREATION_ENABLED =>
                'perform_sync_participant_instance_creation_setting_enabled:label',

            default => throw new coding_exception(
                'No description for sync_participant_instance_creation_options::'
                    . $this->value
            )
        };

        return get_string($id, 'mod_perform');
    }
}
