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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\webapi\resolver\query;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core\exception\unresolved_record_reference;
use core\webapi\execution_context;
use core\webapi\query_resolver;
use dml_exception;
use mod_facetoface\seminar_event;
use mod_facetoface\webapi\reference\event_record_reference;
use moodle_exception;
use require_login_exception;
use totara_webapi\client_aware_exception;
use totara_webapi\exception\record_not_found;

/**
 * GraphQL query resolver for fetching a single seminar event.
 */
class event extends query_resolver {

    /**
     * @throws coding_exception
     * @throws client_aware_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        try {
            $event_record = event_record_reference::load_for_viewer($args['event']);

            $seminar_event = (new seminar_event())->from_record_with_dates_and_rooms($event_record, false);
            $seminar = $seminar_event->get_seminar();

            require_login($seminar->get_course(), false, $seminar->get_coursemodule(), false, true);
        } catch (unresolved_record_reference | require_login_exception) {
            throw record_not_found::make('Event record could not be found or you do not have permissions.');
        }

        $activity_context = $seminar->get_context();
        $ec->set_relevant_context($activity_context);
        $ec->set_variable('context', $activity_context);

        // Load event custom fields
        $event_custom_fields = customfield_get_custom_fields_and_data_for_items('facetofacesession', [$event_record->id]);
        $ec->set_variable('custom_fields_facetofacesession', $event_custom_fields);

        // Load room custom fields
        $room_ids = array_keys($seminar_event->get_rooms());
        $room_custom_fields = customfield_get_custom_fields_and_data_for_items('facetofaceroom', $room_ids);
        $ec->set_variable('custom_fields_facetofaceroom', $room_custom_fields);

        return [
            'event' => $seminar_event,
        ];
    }
}
