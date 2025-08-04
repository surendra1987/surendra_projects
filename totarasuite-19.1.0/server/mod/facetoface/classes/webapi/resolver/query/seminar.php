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

namespace mod_facetoface\webapi\resolver\query;

use core\exception\unresolved_record_reference;
use core\webapi\execution_context;
use core\webapi\query_resolver;
use mod_facetoface\seminar as seminar_model;
use mod_facetoface\webapi\reference\seminar_record_reference;
use require_login_exception;
use required_capability_exception;
use totara_webapi\exception\record_not_found;

/**
 * Query for getting a single seminar.
 */
class seminar extends query_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec) {
        try {
            $seminar_record = seminar_record_reference::load_for_viewer($args['seminar']);
            $seminar = (new seminar_model())->map_instance($seminar_record);
            $activity_context = $seminar->get_context();

            require_login($seminar->get_course(), null, $seminar->get_coursemodule(), false, true);
        } catch (unresolved_record_reference | require_login_exception | required_capability_exception $ex) {
            throw record_not_found::make("Seminar record could not be found or you do not have permissions.");
        }

        $ec->set_relevant_context($activity_context);
        $ec->set_variable('context', $activity_context);

        // Get an event list and separate it into an array
        $event_ids = array_keys($seminar->get_events()->to_records());

        // Load event custom fields
        $event_custom_fields = customfield_get_custom_fields_and_data_for_items('facetofacesession', $event_ids);
        $ec->set_variable('custom_fields_facetofacesession', $event_custom_fields);

        // Load room custom fields
        $room_ids = array_keys($seminar->get_rooms()->to_array());
        $room_custom_fields = customfield_get_custom_fields_and_data_for_items('facetofaceroom', $room_ids);
        $ec->set_variable('custom_fields_facetofaceroom', $room_custom_fields);

        return [
            'seminar' => $seminar,
        ];
    }
}
