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
 * @author Gregory Yee <gregory.yee@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\webapi\resolver\mutation;

use coding_exception;
use context;
use core\exception\unresolved_record_reference;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use dml_exception;
use dml_transaction_exception;
use mod_facetoface\exception\signup_exception;
use mod_facetoface\seminar;
use mod_facetoface\seminar_event;
use mod_facetoface\signup;
use mod_facetoface\signup_helper;
use mod_facetoface\webapi\reference\event_record_reference;
use mod_facetoface\webapi\resolver\booking_custom_fields;
use moodle_exception;
use require_login_exception;
use required_capability_exception;
use totara_webapi\client_aware_exception;
use totara_webapi\exception\record_not_found;

/**
 * Mutation to create a seminar event booking
 */
class event_create_user_booking extends mutation_resolver {
    use booking_custom_fields;

    /**
     * Creates a booking/signup for a seminar event.
     *
     * @param array $args
     * @param execution_context $ec
     * @return array
     * @throws client_aware_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws moodle_exception
     * @throws require_login_exception
     * @throws required_capability_exception
     * @throws signup_exception
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $DB;
        $input = $args['input'];

        try {
            $event_record = event_record_reference::load_for_viewer($input['event'] ?? []);
            $seminar_event = (new seminar_event())->from_record_with_dates_and_rooms($event_record);

            $seminar = $seminar_event->get_seminar();
            $module_context = $seminar->get_context();
            static::check_capabilities($seminar, $module_context);
        } catch (unresolved_record_reference | required_capability_exception | require_login_exception) {
            // We must resolve the record before checking permissions,
            // so we can't rely on middleware (e.g., require_login) for access control here.
            // Therefore we catch all record & capability related exceptions and throw a single exception here.
            throw record_not_found::make('Event record could not be found or you do not have permissions.');
        }

        $ec->set_relevant_context($module_context);
        // Set the context for the custom fields type resolver.
        $ec->set_variable('context', $module_context);

        try {
            $user_record = user_record_reference::load_for_viewer($input['user'] ?? []);
        } catch (unresolved_record_reference) {
            throw record_not_found::make('User record could not be found or you do not have permissions.');
        }

        $signup = signup::create($user_record->id, $seminar_event);

        $transaction = $DB->start_delegated_transaction();
        try {
            signup_helper::signup($signup);

            if (isset($input['custom_fields'])) {
                static::save_custom_fields('facetofacesignup', $signup, $input['custom_fields']);
            }
        } catch (signup_exception) {
            if ($signup->exists()) {
                static::load_custom_fields($ec, $signup, $seminar_event);
            }

            return [
                'created' => false,
                'booking_errors' => static::get_failures($signup),
                'booking' => $signup->exists() ? $signup : null,
            ];
        }
        $transaction->allow_commit();

        static::load_custom_fields($ec, $signup, $seminar_event);
        return [
            'created' => true,
            'booking_errors' => null,
            'booking' => $signup,
        ];
    }

    /**
     * @throws coding_exception
     */
    private static function get_failures(signup $signup): array {
        $states = signup_helper::get_signup_states($signup);

        $errors = [];
        foreach ($states as $state) {
            $failures = $signup->get_failures($state);
            $errors = [...$errors, ...array_map(
                function ($code, $message) use ($state) {
                    return [
                        'state' => $state,
                        'code' => $code,
                        'message' => $message,
                    ];
                },
                array_keys($failures),
                $failures,
            )];
        }

        return $errors;
    }

    /**
     * @param seminar $seminar
     * @param context $context
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
     * @throws require_login_exception
     * @throws required_capability_exception
     */
    private static function check_capabilities(seminar $seminar, context $context): void {
        require_login(
            $seminar->get_course(),
            false,
            $seminar->get_coursemodule(),
            false,
            true,
        );

        // Capability to view an event has already been checked by event_record_reference::load_for_viewer

        require_capability(
            'mod/facetoface:signup',
            $context,
        );
    }
}
