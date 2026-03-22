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
 * Adds new instance of enrol_plugin to specified course or edits current instance.
 *
 * @package    core_enrol
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_enrol\hook\enrol_instance_extra_settings_definition;
use core_enrol\hook\enrol_instance_extra_settings_display;
use core_enrol\hook\enrol_instance_extra_settings_validation;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Standard edit form shared by all enrol plugins.
 *
 * @package    core_enrol
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_instance_edit_form extends moodleform {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        list($instance, $plugin, $context, $type, $returnurl) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_' . $type));

        $plugin->edit_instance_form($instance, $mform, $context);

        // This is for the editinstance endpoint.
        $instance->type = $type;

        // This is for the hook.
        $instance->enrol = $type;

        // Allow form injection above the save buttons.
        // This form is passed to the hook by reference.
        $hook = new enrol_instance_extra_settings_definition($this, $instance, $plugin, $context);
        $hook->execute();
        $this->_customdata[0] = $hook->get_enrolment_instance();

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_COMPONENT);

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    /**
     * Validate this form. Calls plugin validation method.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        list($instance, $plugin, $context, $type) = $this->_customdata;

        $pluginerrors = $plugin->edit_instance_validation($data, $files, $instance, $context);

        $errors = array_merge($errors, $pluginerrors);

        $hook = new enrol_instance_extra_settings_validation($data, $files, $instance, $plugin, $context);
        $hook->execute();
        $errors = array_merge($errors, $hook->get_errors());

        return $errors;
    }

    /**
     * Generate the HTML that the user will see. This method overrides
     * the parent method in order to call a custom hook with any extra
     * settings the instance may have
     */
    public function display() {
        list($instance, $plugin, $context) = $this->_customdata;

        $hook = new enrol_instance_extra_settings_display($this, $instance, $plugin, $context);
        $hook->execute();
        $this->_customdata[0] = $hook->get_enrolment_instance();

        parent::display();
    }
}
