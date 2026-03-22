<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\formatter;

use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\formatter;
use mod_facetoface\signup\state\booked;
use mod_facetoface\signup\state\declined;
use mod_facetoface\signup\state\event_cancelled;
use mod_facetoface\signup\state\fully_attended;
use mod_facetoface\signup\state\no_show;
use mod_facetoface\signup\state\not_set;
use mod_facetoface\signup\state\partially_attended;
use mod_facetoface\signup\state\requested;
use mod_facetoface\signup\state\requestedadmin;
use mod_facetoface\signup\state\requestedrole;
use mod_facetoface\signup\state\state;
use mod_facetoface\signup\state\unable_to_attend;
use mod_facetoface\signup\state\user_cancelled;
use mod_facetoface\signup\state\waitlisted;

/**
 * Booking state formatter.
 */
class booking_state extends formatter {

    /** @var class-string|state */
    protected $object;

    /**
     * @inheritDoc
     */
    protected function get_map(): array {
        return [
            'title' => string_field_formatter::class,
            'key' => function ($value) {
                return match ($value) {
                    booked::get_code() => 'BOOKED',
                    declined::get_code() => 'DECLINED',
                    event_cancelled::get_code() => 'EVENT_CANCELLED',
                    fully_attended::get_code() => 'FULLY_ATTENDED',
                    partially_attended::get_code() => 'PARTIALLY_ATTENDED',
                    unable_to_attend::get_code() => 'UNABLE_TO_ATTEND',
                    no_show::get_code() => 'NO_SHOW',
                    requested::get_code() => 'REQUESTED',
                    requestedadmin::get_code() => 'REQUESTED_ADMIN',
                    requestedrole::get_code() => 'REQUESTED_ROLE',
                    user_cancelled::get_code() => 'USER_CANCELLED',
                    waitlisted::get_code() => 'WAITLISTED',
                    not_set::get_code() => 'NOT_SET',
                    default => 'UNKNOWN',
                };
            },
            'enter_state_message' => string_field_formatter::class,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function get_field(string $field) {
        return match($field) {
            'key' => $this->object::get_code(),
            'title' => $this->object::get_string(),
            'enter_state_message' => is_string($this->object) ? null : $this->object->get_message(),
        };
    }

    /**
     * @inheritDoc
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }
}
