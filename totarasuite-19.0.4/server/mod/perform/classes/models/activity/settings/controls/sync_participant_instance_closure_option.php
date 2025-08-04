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
 * Indicates how existing responding participant instances are treated when a
 * participant changes or leaves a relationship in an activity.
 */
enum sync_participant_instance_closure_option : int {
    // Participant instances are not closed when a user relationship changes.
    case CLOSURE_DISABLED = 0;

    // Participant instances are closed when a user relationship changes and the
    // participant instance has not been started.
    case CLOSE_NOT_STARTED_ONLY = 1;

    // Participant instances are closed when a user relationship changes whatever
    // the participant instance status.
    case CLOSE_ALL = 2;

    /**
     * Returns the enum from its name. This should really be part of the base
     * PHP enum abstraction but ....
     *
     * @return self the enum if it was found.
     */
    public static function from_name(string $name): self {
        foreach (static::cases() as $enum) {
            if ($enum->name === $name) {
                return $enum;
            }
        }

        throw new coding_exception(
            "no such enum: sync_participant_instance_closure_option::$name"
        );
    }

    /**
     * Returns a localized string describing this enum.
     *
     * @return string the description.
     */
    public function description(): string {
        $id = match ($this) {
            self::CLOSURE_DISABLED =>
                'perform_sync_participant_instance_closure_setting_disabled:desc',

            self::CLOSE_NOT_STARTED_ONLY =>
                'perform_sync_participant_instance_closure_setting_not_started_only:desc',

            self::CLOSE_ALL =>
                'perform_sync_participant_instance_closure_setting_all:desc',

            default => throw new coding_exception(
                'No description for sync_participant_instance_closure_option::'
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
            self::CLOSURE_DISABLED =>
                'perform_sync_participant_instance_closure_setting_disabled:label',

            self::CLOSE_NOT_STARTED_ONLY =>
                'perform_sync_participant_instance_closure_setting_not_started_only:label',

            self::CLOSE_ALL =>
                'perform_sync_participant_instance_closure_setting_all:label',

            default => throw new coding_exception(
                'No label for sync_participant_instance_closure_option::'
                    . $this->value
            )
        };

        return get_string($id, 'mod_perform');
    }
}
