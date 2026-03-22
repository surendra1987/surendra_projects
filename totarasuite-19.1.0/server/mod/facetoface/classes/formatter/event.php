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

use coding_exception;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\text_field_formatter;
use core\webapi\formatter\formatter;
use mod_facetoface\seminar_event;
use mod_facetoface\webapi\field_mapping\event as event_field_mapping;

defined('MOODLE_INTERNAL') || die();

/**
 * Seminar event formatter.
 */
class event extends formatter {

    /**
     * @inheritDoc
     */
    protected function get_map(): array {
        return [
            'id' => null,
            'timecreated' => date_field_formatter::class,
            'timemodified' => date_field_formatter::class,
            'seminar_id' => null,
            'allow_cancellations' => function ($value) {
                switch ($value) {
                    case seminar_event::ALLOW_CANCELLATION_NEVER:
                        return "NEVER";
                    case seminar_event::ALLOW_CANCELLATION_ANY_TIME:
                        return "ANY_TIME";
                    case seminar_event::ALLOW_CANCELLATION_CUT_OFF:
                        return "CUT_OFF";
                    default:
                        throw new coding_exception("Invalid allow cancellations option, `$value` given. Only values of 0, 1 or 2 are accepted.");
                }
            },
            'cancellation_cutoff' => null,
            'details' => function ($value, text_field_formatter $formatter) {
                $component = 'mod_facetoface';
                $filearea = 'session';

                return $formatter
                    ->set_pluginfile_url_options($this->context, $component, $filearea, $this->object->id)
                    ->format($value);
            },
            'cost_normal' => null,
            'cost_discount' => null,
            'registration_time_start' => date_field_formatter::class,
            'registration_time_end' => date_field_formatter::class,
            'seats_total' => null,
            'seats_booked' => null,
            'seats_available' => null,
            'sessions' => null,
            'custom_fields' => null,
            'start_date' => date_field_formatter::class,
            'finish_date' => date_field_formatter::class,
            'seminar' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function get_field(string $field) {
        // Map the GraphQL schema to the database fields
        $field = event_field_mapping::get_graphql_to_internal_column_mapping()[$field] ?? $field;

        return $this->object->$field;
    }

    /**
     * @inheritDoc
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }
}
