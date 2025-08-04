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
 * @author  Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package totara_program
 */
namespace totara_program\totara_notification\placeholder;

use coding_exception;
use core\orm\query\builder;
use prog_assignment_category;
use html_writer;
use moodle_url;
use stdClass;
use totara_notification\placeholder\abstraction\placeholder_instance_cache;
use totara_notification\placeholder\abstraction\single_emptiable_placeholder;
use totara_notification\placeholder\option;
use totara_program\assignments\assignments;
use totara_program\assignments\category;
use function PHPUnit\Framework\isEmpty;

class extension_request extends single_emptiable_placeholder {
    use placeholder_instance_cache;

    /**
     * @var ?stdClass
     */
    private ?stdClass $record;

    /**
     * @var int
     */
    private int $user_id;

    /**
     * @param stdClass|null $record
     * @param int $user_id
     */
    public function __construct(?stdClass $record, int $user_id) {
        $this->record = $record;
        $this->user_id = $user_id;
    }

    /**
     * @return extension_request
     */
    public static function from_id(int $extension_request_id): extension_request {
        global $DB;

        $instance = self::get_cached_instance($extension_request_id);
        if (!$instance) {
            $extension_request = $DB->get_record(
                'prog_extension',
                ['id' => $extension_request_id]
            );
            $instance = new static(
                $extension_request, $extension_request->userid
            );
            self::add_instance_to_cache($extension_request_id, $instance);
        }
        return $instance;
    }

    /**
     * @return option[]
     */
    public static function get_options(): array {
        return [
            option::create(
                'extension_reason', get_string('reasonforextension', 'totara_program')
            ),
            option::create(
                'extension_date', get_string('requested_extension_date', 'totara_program')
            ),
            option::create(
                'status', get_string('requested_extension_status', 'totara_program')
            ),
            option::create(
                'reason_for_decision', get_string('requested_extension_manager_reason', 'totara_program')
            ),
            option::create(
                'manage_extensions_url', get_string('extensionrequestmanagementurl', 'totara_program')
            )
        ];
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function is_available(string $key): bool {
        return null !== $this->record;
    }

    /**
     * @param string $key
     * @return string|null When the result expects an empty string, this should return "",
     * while null should return when the data is not available and will result in "<no data available for $key>".
     */
    public function do_get(string $key): ?string {
        if (null === $this->record) {
            throw new coding_exception("The program assignment record is empty");
        }

        switch ($key) {
            case 'extension_reason':
                return $this->get_sanitized_extension_reason_string();
            case 'extension_date':
                return $this->get_formatted_extension_date();
            case 'status':
                return $this->get_status_string();
            case 'reason_for_decision':
                return $this->get_sanitized_decision_reason_string();
            case 'manage_extensions_url':
                return $this->get_management_url();
        }

        throw new coding_exception("Invalid key '{$key}'");
    }

    /**
     * The 'extensionreason' field is required on the UI, but it is nullable in the database,
     * so we need to sanitize it just in case.
     *
     * @return string
     */
    private function get_sanitized_extension_reason_string(): string {
        return $this->record->extensionreason ?? 'No reason has been given for this extension request';
    }

    /**
     * Format extension date from epoch time to a human-readable date format
     *
     * @return string
     */
    private function get_formatted_extension_date(): string {
        return date('d/m/Y H:i T', $this->record->extensiondate);
    }

    /**
     * Convert status integer to corresponding status strings
     *
     * @return string
     */
    private function get_status_string(): string {
        $status_matrix = [
            0 => 'Request Pending',
            1 => 'Request Granted',
            2 => 'Request Denied'
        ];
        return $status_matrix[$this->record->status] ?? 'Error: unrecognised status';
    }

    /**
     * If the manager's response field is empty, we want to provide a generic response
     *
     * @return string
     */
    private function get_sanitized_decision_reason_string(): string {
        return $this->record->reasonfordecision ?? 'Your manager has not provided a reason for their decision.';
    }

    /**
     * @return string
     */
    private function get_management_url(): string {
        $url = new moodle_url('/totara/program/manageextensions.php');
        return html_writer::link($url, format_string('Manage Extensions'));
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function is_safe_html(string $key): bool {
        if ('manage_extensions_url' === $key) {
            return true;
        }

        return parent::is_safe_html($key);
    }

    /**
     * Publicly accessible way to get an attribute on the record
     *
     * @return int
     */
    public function get_status_int(): int {
        return $this->record->status;
    }
}
