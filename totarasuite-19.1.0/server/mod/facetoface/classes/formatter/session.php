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

namespace mod_facetoface\formatter;

use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\formatter;

defined('MOODLE_INTERNAL') || die();

/**
 * Seminar session formatter.
 */
class session extends formatter {

    /**
     * @inheritDoc
     */
    protected function get_map(): array {
        return [
            'id' => null,
            'rooms' => null,
            'timestart' => date_field_formatter::class,
            'timefinish' => date_field_formatter::class,
        ];
    }
}
