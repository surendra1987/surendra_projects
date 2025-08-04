<?php
/**
 * This file is part of Totara LMS
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package
 */

namespace antivirus_testable_hvp;

defined('MOODLE_INTERNAL') || die();

class scanner extends \core\antivirus\scanner {
    protected string $fail_on_filename;

    /**
     * @inheritDoc
     */
    public function is_configured() {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function scan_file($file, $filename) {
        switch ($filename) {
            case 'OK':
                return self::SCAN_RESULT_OK;
            case 'FOUND':
                return self::SCAN_RESULT_FOUND;
            case 'ERROR':
                return self::SCAN_RESULT_ERROR;
            default:
                debugging('$filename should be either OK, FOUND or ERROR.');
                break;
        }
    }
}