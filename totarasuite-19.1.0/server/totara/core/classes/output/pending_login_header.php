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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package totara_core
 */

namespace totara_core\output;

use core\output\template;

/**
 *  Header for the pending login screen layout.
 */
class pending_login_header extends template {

    public static function create(): pending_login_header {
        global $OUTPUT;

        return new static([
            'masthead_logo' => (new masthead_logo())->export_for_template($OUTPUT),
            'masthead_lang' => $OUTPUT->language_select(),
        ]);
    }
}
