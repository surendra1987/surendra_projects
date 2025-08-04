<?php
/*
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Maria Torres <maria.torres@totara.com>
 * @package totara
 * @subpackage certification
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds grademax and grademin column to course_completion_history DB table and
 * fills the new fields with the current course grade items grademax and grademin
 */
function totara_certification_upgrade_course_completion_history_grade_columns() {
    global $DB, $CFG;

    require_once($CFG->libdir . '/gradelib.php');

    $sql = "SELECT DISTINCT courseid
                FROM {course_completion_history}";
    $courses = $DB->get_fieldset_sql($sql);
    foreach ($courses as $courseid) {
        $course_item = grade_item::fetch_course_item($courseid);
        $params = [
            'grademax' => $course_item->grademax,
            'grademin' => $course_item->grademin,
            'courseid' => $courseid
        ];
        $sql = "UPDATE {course_completion_history}
                    SET grademax = :grademax,
                        grademin = :grademin
                  WHERE courseid = :courseid
                    AND (grademax IS NULL OR grademin IS NULL)";
        $DB->execute($sql, $params);
    }
}
