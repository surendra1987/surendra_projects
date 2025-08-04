<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package totara_program
 */

use totara_notification\placeholder\option;
use totara_program\totara_notification\placeholder\extension_request;

require_once(__DIR__ . '/totara_notification_base.php');

class totara_program_totara_notification_extension_request_placeholder_test extends totara_program_totara_notification_base {

    public function test_extension_request_placeholder_has_all_options(): void {
        $expected_options = [
            'extension_reason',
            'extension_date',
            'status',
            'reason_for_decision',
            'manage_extensions_url'
        ];
        $option_keys = array_map(static function (option $option) {
            return $option->get_key();
        }, extension_request::get_options());
        self::assertSame($expected_options, $option_keys);
    }

    public function test_extension_request_placeholder_when_manager_has_not_yet_responded(): void {
        global $DB;

        $requested_extension_date_epoch_time = time();
        $extension_reason = 'blahblahblah';
        $extension_request_row_id = $DB->insert_record(
            'prog_extension',
            array(
                'programid' => 1,
                'userid' => 1999,
                'extensiondate' => $requested_extension_date_epoch_time,
                'extensionreason' => $extension_reason,
                'status' => 0,
            )
        );

        $extension_request_instance = extension_request::from_id($extension_request_row_id);
        $formatted_datetime_string = date('d/m/Y H:i T', $requested_extension_date_epoch_time);
        self::assertEquals($formatted_datetime_string, $extension_request_instance->do_get('extension_date'));
        self::assertEquals($extension_reason, $extension_request_instance->do_get('extension_reason'));
        self::assertEquals('Request Pending', $extension_request_instance->do_get('status'));
        self::assertEquals(
            'Your manager has not provided a reason for their decision.',
            $extension_request_instance->do_get('reason_for_decision')
        );
    }

    public function test_extension_request_placeholder_when_manager_approves_request(): void {
        global $DB;

        $reason_for_decision = 'abracadabra';
        $extension_request_row_id = $DB->insert_record(
            'prog_extension',
            array(
                'programid' => 1,
                'userid' => 1999,
                'extensiondate' => 0,
                'extensionreason' => 'n/a',
                'status' => 1,
                'reasonfordecision' => $reason_for_decision
            )
        );

        $extension_request_instance = extension_request::from_id($extension_request_row_id);
        self::assertEquals($reason_for_decision, $extension_request_instance->do_get('reason_for_decision'));
        self::assertEquals('Request Granted', $extension_request_instance->do_get('status'));
    }

    public function test_extension_request_placeholder_for_invalid_values(): void {
        global $DB;

        $extension_request_row_id = $DB->insert_record(
            'prog_extension',
            array(
                'programid' => 1,
                'userid' => 1999,
                'extensiondate' => 0,
                'extensionreason' => null,
                'status' => 36,
            )
        );

        $extension_request_instance = extension_request::from_id($extension_request_row_id);
        self::assertEquals('Error: unrecognised status', $extension_request_instance->do_get('status'));
        self::assertEquals(
            'No reason has been given for this extension request',
            $extension_request_instance->do_get('extension_reason')
        );
    }
}
