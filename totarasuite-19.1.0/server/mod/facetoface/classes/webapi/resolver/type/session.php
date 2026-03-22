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

use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_facetoface\formatter\session as session_formatter;
use mod_facetoface\seminar_session;

/**
 * Seminar session type resolver.
 */
class session extends type_resolver {

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function resolve(string $field, $session, array $args, execution_context $ec) {
        if (!$session instanceof seminar_session) {
            throw new \coding_exception('Only session records from the database are accepted. Instead, got: ' . gettype($session));
        }

        $session_record = $session->to_record();

        $session_record->$field = match ($field) {
            'rooms' => $session->get_rooms()->to_array(),
            default => $session_record->$field ?? null,
        };

        // The seminars in course query doesn't set an execution context since there are different contexts
        // based on which seminar we are solving fields for, so just get it from the manually set 'context' variable.
        $context = $ec->has_relevant_context() ? $ec->get_relevant_context() : $ec->get_variable('context');

        $formatter = new session_formatter($session_record, $context);
        $format = $args['format'] ?? format::FORMAT_PLAIN;

        return $formatter->format($field, $format);
    }
}
