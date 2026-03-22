<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for grade/report/lib.php.
 *
 * @package  core_grades
 * @category phpunit
 * @author   Andrew Davis
 * @license  http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

global $CFG;
require_once($CFG->dirroot . '/grade/report/lib.php');

/**
 * A test class used to test grade_report, the abstract grade report parent class
 */
class core_grades_test_grade_report extends grade_report {
    public function __construct($courseid, $gpr, $context, $user) {
        parent::__construct($courseid, $gpr, $context);
        $this->user = $user;
    }

    /**
     * A wrapper around blank_hidden_total_and_adjust_bounds() to allow test code to call it directly
     */
    public function blank_hidden_total_and_adjust_bounds($courseid, $courseitem, $finalgrade) {
        return parent::blank_hidden_total_and_adjust_bounds($courseid, $courseitem, $finalgrade);
    }

    /**
     * Implementation of the abstract method process_data()
     */
    public function process_data($data) {
    }

    /**
     * Implementation of the abstract method process_action()
     */
    public function process_action($target, $action) {
    }
}