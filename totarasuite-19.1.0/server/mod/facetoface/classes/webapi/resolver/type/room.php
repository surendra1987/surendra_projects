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
 * @author Mark Metcalfe <mark.metcalfe@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\webapi\resolver\type;

use coding_exception;
use core\entity\user;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_facetoface\formatter\room as room_formatter;
use mod_facetoface\room as room_model;

/**
 * Seminar room type resolver.
 */
class room extends type_resolver {

    /**
     * @param string $field
     * @param room_model $room
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed|null
     * @throws coding_exception
     */
    public static function resolve(string $field, $room, array $args, execution_context $ec) {
        if (!$room instanceof room_model) {
            throw new coding_exception('Only room instances are accepted. Instead, got: ' . gettype($room));
        }

        $room_record = $room->to_record();

        $room_record->$field = match ($field) {
            'custom_fields' => $ec->get_variable('custom_fields_facetofaceroom')[$room->get_id()] ?? null,
            'usercreated' => new user($room->get_usercreated()),
            'usermodified' => new user($room->get_usermodified()),
            default => $room_record->$field ?? null,
        };

        // Rooms are deliberately in the system context, not the module context.
        // This is because rooms can be reused across different seminars, and support for custom rooms is deprecated.
        $context = \context_system::instance();

        $formatter = new room_formatter($room_record, $context);

        return $formatter->format($field, $args['format'] ?? format::FORMAT_PLAIN);
    }
}
