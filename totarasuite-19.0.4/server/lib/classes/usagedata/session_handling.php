<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @package core
 */

namespace core\usagedata;

use tool_usagedata\export;

class session_handling implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('session_handling_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    public function export(): array {
        global $CFG;

        return [
            // Config.php only configuration
            'session_handler_class' => !empty($CFG->session_handler_class) ? $CFG->session_handler_class : self::NOTSET,

            // Session handling
            'dbsessions' => $CFG->dbsessions ?? self::NOTSET,
            'sessiontimeout' => $CFG->sessiontimeout ?? self::NOTSET,
            'uses_sessioncookie' => !empty($CFG->sessioncookie),
            'uses_sessioncookiepath' => !empty($CFG->sessioncookiepath),
            'uses_sessioncookiedomain' => !empty($CFG->sessioncookiedomain),

            // HTTP security
            'cookiesecure' => $CFG->cookiesecure ?? self::NOTSET,
            'cookiehttponly' => $CFG->cookiehttponly ?? self::NOTSET,
        ];
    }
}
