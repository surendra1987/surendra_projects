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

namespace mod_facetoface\webapi\resolver\type;

use coding_exception;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_facetoface\formatter\event as event_formatter;
use mod_facetoface\seminar_event;

/**
 * Seminar event type resolver.
 */
class event extends type_resolver {

    /**
     * @param string $field
     * @param seminar_event $event
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed|null
     * @throws coding_exception
     */
    public static function resolve(string $field, $event, array $args, execution_context $ec) {
        if (!$event instanceof seminar_event) {
            throw new coding_exception('Only seminar_event instances are accepted. Instead, got: ' . gettype($event));
        }

        $event_record = $event->to_record();

        $event_record->$field = match ($field) {
            'seats_booked' => $event->get_attendees_count(),
            'seats_available' => $event->get_free_capacity(),
            'sessions' => $event->get_sessions()?->to_array() ?? [],
            'start_date' => $event->get_mintimestart(),
            'finish_date' => $event->get_maxtimefinish(),
            'custom_fields' => $ec->get_variable('custom_fields_facetofacesession')[$event->get_id()] ?? null,
            'seminar' => $event->get_seminar(),
            default => $event_record->$field ?? null,
        };

        // The seminars in course query doesn't set an execution context since there are different contexts
        // based on which seminar we are solving fields for, so just get it from the manually set 'context' variable.
        $context = $ec->has_relevant_context() ? $ec->get_relevant_context() : $ec->get_variable('context');

        $formatter = new event_formatter($event_record, $context);

        return $formatter->format($field, $args['format'] ?? format::FORMAT_PLAIN);
    }
}
