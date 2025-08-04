<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Gihan Hewaralalage <gihan.hewaralalage@totaralearning.com>
 * @package totara_notification
 */

namespace totara_notification\rb\display;

use core\orm\query\exceptions\record_not_found_exception;
use rb_column;
use reportbuilder;
use stdClass;
use totara_notification\model\notification_preference;
use totara_reportbuilder\rb\display\base;
use totara_reportbuilder\rb\display\format_string;

class notification_log_preference_title extends base {

    /**
     * @param int $preference_id
     * @param string $format
     * @param stdClass $row
     * @param rb_column $column
     * @param reportbuilder $report
     * @return string
     */
    public static function display($preference_id, $format, stdClass $row, rb_column $column, reportbuilder $report): string {
        try {
            $preference = notification_preference::from_id($preference_id);
            $preference_title = $preference->get_title();
        } catch (record_not_found_exception $e) {
            $preference_title = get_string('deleted_notification_preferences_title', 'totara_notification');
        }
        return format_string::display($preference_title, $format, $row, $column, $report);
    }
}
