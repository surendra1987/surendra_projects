<?php
/**
 * This file is part of Totara LMS
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
 * @author Timothy Liew <timothy.liew@totara.com>
 * @package totara_program
 */

require_once $CFG->libdir . '/formslib.php';

if (!defined('MOODLE_INTERNAL')) {
    //  It must be included from a Moodle page.
    die('Direct access to this script is forbidden.');
}

class edit_group_form extends moodleform {

    public function definition() {
        $mform = &$this->_form;

        $program_id = $this->_customdata['program_id'];
        $assignment_id = $this->_customdata['assignment_id'];

        $mform->addElement('hidden', 'program_id', $program_id);
        $mform->setType('program_id', PARAM_INT);
        if ($assignment_id) {
            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);
        }

        $mform->addElement('text', 'name', get_string('group_name', 'totara_program'), ['size' => '40', 'maxlength' => '1333', 'class' => 'vertical-stack']);
        $mform->addRule('name', get_string('error:missingproggroupname', 'totara_program'), 'required');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('textarea', 'description', get_string('group_description', 'totara_program'), ['class' => 'vertical-stack', 'rows' => 5]);
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('html', html_writer::tag('p', get_string('groupmanagement', 'totara_program'), ['class' => 'fgroup-header']));
        $mform->addElement('checkbox', 'can_self_enrol', get_string('allowlearnerstoenrol', 'totara_program'), null, ['class' => 'checkbox-wrapper']);
        $mform->addElement('checkbox', 'can_self_unenrol', get_string('allowlearnerstowithdraw', 'totara_program'), null, ['class' => 'checkbox-wrapper']);
    }

    /**
     * Carries out validation of submitted form values
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $name = $data['name'] ? trim($data['name']) : null;
        if (!$name) {
            $errors['name'] = get_string('error:missingproggroupname', 'totara_program');
        }

        return $errors;
    }
}
