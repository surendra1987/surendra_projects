<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2016 onwards Totara Learning Solutions LTD
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
 * @author Alastair Munro <alastair.munro@totaralearning.com>
 * @package totara_program
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use \Behat\Gherkin\Node\TableNode as TableNode;
use totara_program\program;
use totara_program\content\program_content;
use totara_program\content\course_set;
use totara_program\progress\program_progress_cache;

class behat_totara_program extends behat_base {

    /**
     * Goes directly to the create program form
     *
     * TOTARA: No longer rely on admin navigation, instead to directly to URL
     * When we are testing functionality of page, not how to get there.
     *
     * @Given /^I go to the program creation form$/
     * @return void
     */
    public function i_go_to_the_program_creation_form(): void {
        \behat_hooks::set_step_readonly(false);

        $args = [];

        // The category the program is going into, we could make this an arg.
        $args['category'] = 1;

        $url = new moodle_url('/totara/program/add.php', $args);

        // Redirect.
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
        $this->wait_for_pending_js();
    }

    /**
     * Goes directly to the edit program/certification form, the program/cert needs to exist and its shortname be unique
     *
     * @Given /^I go to edit the (certification|program) "([^"]*)"$/
     * @param string $type
     * @param string $shortname
     */
    public function i_go_to_edit_the_program($type, $shortname) {
        global $DB;
        \behat_hooks::set_step_readonly(false);

        // Could separate out prog/cert query if there's value?
        $program_record = $DB->get_record('prog', array('shortname' => $shortname), 'id', MUST_EXIST);

        $args = [];
        $args['id'] = $program_record->id;

        $url = new moodle_url('/totara/program/edit.php', $args);
        // Redirect.
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
        $this->wait_for_pending_js();
    }

   /**
     * Adds a courseset to a program with the given courses as content.
     *
     * This definition requires the specified program and courses to exist.
     *
     * @Given /^I add a courseset with courses "([^"]*)" to "([^"]*)":$/
     * @param String $courses A comma separated list of courses
     * @param String $programname
     * @param TableNode $data
     */
    public function i_add_a_courseset_with_the_following_courses_to_program($courses, $programname, TableNode $data) {
        \behat_hooks::set_step_readonly(false);

        global $CFG, $DB;

        // Need certification lib for constants, for now.
        require_once($CFG->dirroot . '/totara/certification/lib.php');

        // Get program record
        $program_record = $DB->get_record('prog', array('shortname' => $programname));
        $program = new program($program_record->id);

        $coursenames = explode(',', $courses);
        list($insql, $inparams) = $DB->get_in_or_equal($coursenames);
        $sql = "SELECT * FROM {course} WHERE shortname {$insql}";
        $courses = $DB->get_records_sql($sql, $inparams);

        foreach ($coursenames as $coursename) {
            // Check each course exists.
            $found = false;

            foreach ($courses as $course) {
                if ($course->shortname == $coursename) {
                    $found = true;
                }
            }

            if (!$found) {
                throw new Exception('Course with shortname "' . $coursename . '" does not exist.');
            }
        }

        $progcontent = new program_content($program->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $datahash = $data->getRowsHash();
        foreach ($datahash as $option => $value) {
            switch ($option) {
            case "Set name":
                $coursesets[0]->label = $value;
                break;
            case "Learner must complete":
                if ($value == "One course") {
                    $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ANY;
                } else if ($value == "All courses") {
                    $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;
                } else if ($value == "Some courses") {
                    $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_SOME;
                } else if ($value == "All courses are optional") {
                    $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_OPTIONAL;
                } else {
                    throw new Exception('Invalid completion type "' . $value . '" given for course set');
                }
                break;
            case "Minimum time required":
                $coursesets[0]->timeallowed = $value * DAYSECS; // Number of days allowed.
                break;
            }
        }

        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;

        foreach ($courses as $course) {
            $coursedata = new stdClass();
            $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $course->id;
            $progcontent->add_course($coursesets[0]->sortorder, $coursedata);
        }

        $progcontent->save_content();

        // Purge the completion caches
        program_progress_cache::purge_progressinfo_caches();
        completion_info::purge_progress_caches();
    }

    /**
     * Checks if the given value is set for the given input on the program overview screen.
     *
     * @Given /^I should see "([^"]*)" for "([^"]*)" in the program overview$/
     * @param string $value
     * @param string $field
     */
    public function i_should_see_for_in_the_program_overview($value, $field) {
        \behat_hooks::set_step_readonly(true);

        $value_literal = behat_context_helper::escape($value);
        $field_literal = behat_context_helper::escape($field);
        $xpath =  "//div[contains(concat(' ', @class, ' '), ' fstatic ') + contains(text(),{$field_literal})]";
        $xpath .= "/ancestor::div[contains(concat(' ', @class, ' '), ' fitem ')]";
        $xpath .= "//div[contains(concat(' ', @class, ' '), ' felement ') + contains(., {$value_literal})]";
        $this->find('xpath', $xpath);
    }
}
