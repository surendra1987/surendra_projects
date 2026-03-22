<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package mod_perform
 */

namespace mod_perform\rb\display;

use mod_perform\models\activity\settings\participant_instances\participant_instance_access_removed_option;

/**
 * Formats a report cell to show a text value (e.g participant name) with a conditional access_removed icon afterwards.
 */
class participant_link_with_icon extends participant_link {
    /**
     * @param string $value
     * @param string $format
     * @param \stdClass $row
     * @param \rb_column $column
     * @param \reportbuilder $report
     * @return string
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        global $OUTPUT;
        $cell_content = parent::display($value, $format, $row, $column, $report);

        if (!$report->embedded) {
            // This is a report builder report, which will not allow SVG icons to go into a CSV export file, etc.
            // So, don't put the icon into the cell.
            return $cell_content;
        }

        $extrafields = static::get_extrafields_row($row, $column);
        if ($extrafields->access_removed) {
            $cell_content .= '&nbsp;' . $OUTPUT->flex_icon('ban', [
                'classes' => 'ft-size-200 tw-perform-manage-access-removed-icon',
                'alt' => get_string('remove_participant_instance_access_icon_tooltip', 'mod_perform'),
                'title' => get_string('remove_participant_instance_access_icon_tooltip', 'mod_perform')
            ]);
        }

        return $cell_content;
    }
}
