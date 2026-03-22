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
use mod_facetoface\formatter\seminar as seminar_formatter;
use mod_facetoface\seminar as seminar_model;

/**
 * Seminar type resolver.
 */
class seminar extends type_resolver {

    /**
     * @throws coding_exception
     */
    public static function resolve(string $field, $seminar, array $args, execution_context $ec) {
        if (!$seminar instanceof seminar_model) {
            throw new coding_exception('Only seminar instances are accepted. Instead, got: ' . gettype($seminar));
        }

        $seminar_record = $seminar->get_properties();

        $seminar_record->$field = match ($field) {
            'id' => $seminar->get_id(),
            'shortname' => $seminar->get_shortname(),
            'name' => $seminar->get_name(),
            'description' => $seminar->get_intro(),
            // If there is no timecreated value it gets stored as 0, so just return null so the formatter doesn't format it as 1970-01-01.
            'timecreated' => $seminar->get_timecreated() != 0 ? $seminar->get_timecreated() : null,
            'timemodified' => $seminar->get_timemodified(),
            'course_id' => $seminar->get_course(),
            'events' => $seminar->get_events(),
        };

        // The seminars in course query doesn't set an execution context since there
        // are different contexts based on which seminar we are solving fields for.
        $context = $ec->has_relevant_context() ? $ec->get_relevant_context() : $seminar->get_context();
        $ec->set_variable('context', $context);

        $formatter = new seminar_formatter($seminar_record, $context);

        return $formatter->format($field, $args['format'] ?? format::FORMAT_PLAIN);
    }
}
