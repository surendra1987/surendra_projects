<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @package totara_engage
 */
namespace totara_engage\rb\display;

use core\entity\user;
use html_writer;
use rb_column;
use reportbuilder;
use stdClass;
use totara_reportbuilder\rb\display\base;

/**
 * Displays workspace owners as a single delimited string.
 */
final class workspace_owners_link extends base {
    /**
     * Handles the display
     *
     * @param string $value
     * @param string $format
     * @param \stdClass $row
     * @param \rb_column $column
     * @param \reportbuilder $report
     * @return string
     */
    public static function display(
        $value,
        $format,
        stdClass $row,
        rb_column $column,
        reportbuilder $report
    ): string {
        $for_export = ($format !== 'html');
        $delimiter = $for_export ? ',' : '<br/>';

        $csv = $value ?? '';
        $names = self::names($csv, $for_export);

        return $names
            ? implode($delimiter, $names)
            : get_string('unassigned', 'totara_engage');
    }

    /**
     * Is this column graphable?
     *
     * @param \rb_column $column
     * @param \rb_column_option $option
     * @param \reportbuilder $report
     * @return bool
     */
    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }

    /**
     * Returns the workspace owner names corresponding to the user ids in the
     * specified csv string.
     *
     * @param string $csv comma delimited list of workspace owner ids whose
     *        names are to be looked up.
     * @param bool $for_export indicates whether to format the names for html
     *        links or as text for export.
     *
     * @return string[] the list of owner names.
     */
    private static function names(
        string $csv,
        bool $for_export
    ): array {
        // Have to do this filter here because
        // 1) the incoming csv may contain delimited blanks eg " , ,"
        // 2) incredibly, explode() _returns a single item array_ for blank csv
        //    strings instead what you would expect: an empty array.
        $user_ids = array_filter(
            explode(',', $csv),
            fn(string $id): bool => !empty($id)
        );

        if (!$user_ids) {
            return [];
        }

        // Unfortunately, cannot use totara_reportbuilder\rb\display\user_link
        // here because user_link assumes a report row also has a set of names
        // for _a single user_. These name fields are given by the 'extrafields'
        // declaration in the reportbuilder column definition and obviously will
        // not work in this class when it is trying to format a list of users as
        // a single report field.
        global $PAGE;
        $course = (CLI_SCRIPT && !PHPUNIT_TEST) ? null : $PAGE->course;

        return user::repository()
            ->where_in('id', explode(',', $csv))
            ->where('deleted', 0)
            ->get()
            ->map(
                function (user $it) use ($course, $for_export): string {
                    $owner = $it->to_record();
                    $name = fullname($owner);

                    $label = match (true) {
                        !$name => '',

                        $for_export => $name,

                        default => html_writer::link(
                            user_get_profile_url($owner, $course),
                            $name
                        )
                    };

                    return trim($label);
                }
            )
            ->filter(fn (string $it): bool => !empty($it))
            ->all();
    }
}