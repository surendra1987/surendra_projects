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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\webapi\resolver;

use coding_exception;
use core\webapi\execution_context;
use dml_exception;
use mod_facetoface\seminar_event;
use mod_facetoface\signup;
use totara_customfield\exceptions\customfield_validation_exception;
use totara_customfield\webapi\customfield_resolver_helper;
use totara_webapi\client_aware_exception;
use totara_webapi\exception\validation;

/**
 * Handles custom fields shared logic for bookings resolvers
 */
trait booking_custom_fields {

    /**
     * Saves custom fields for a given booking ($signup)
     * @param string $type - The type of custom fields to save
     * @param signup $signup
     * @param array $custom_fields_to_save
     * @throws client_aware_exception
     * @throws dml_exception
     */
    private static function save_custom_fields(string $type, signup $signup, array $custom_fields_to_save): void {
        $item = $signup->to_record();
        $customfield_helper = new customfield_resolver_helper($type);

        try {
            $customfield_helper->save_customfields_for_item(
                $item,
                $custom_fields_to_save,
                [],
                [$type . 'id' => $item->id],
            );
        } catch (customfield_validation_exception) {
            $error_message = implode(', ', $customfield_helper->get_validation_errors());
            throw validation::make('Custom field validation errors: ' . $error_message);
        }
    }

    /**
     * Load custom fields and saves them on the execution context for a booking
     * @param execution_context $ec
     * @param signup $signup
     * @param seminar_event $seminar_event
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function load_custom_fields(execution_context $ec, signup $signup, seminar_event $seminar_event): void {
        $ec->set_variable(
            'custom_fields_facetofacesession',
            customfield_get_custom_fields_and_data_for_items('facetofacesession', [$signup->get_sessionid()]),
        );
        $ec->set_variable(
            'custom_fields_facetofacesignup',
            customfield_get_custom_fields_and_data_for_items('facetofacesignup', [$signup->get_id()]),
        );
        $ec->set_variable(
            'custom_fields_facetofaceroom',
            customfield_get_custom_fields_and_data_for_items('facetofaceroom', array_keys($seminar_event->get_rooms())),
        );
        $ec->set_variable(
            'custom_fields_facetofacecancellation',
            customfield_get_custom_fields_and_data_for_items('facetofacecancellation', [$signup->get_id()])
        );
    }
}
