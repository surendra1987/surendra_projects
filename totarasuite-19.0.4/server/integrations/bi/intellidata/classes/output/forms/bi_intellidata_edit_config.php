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
 * @package    local
 * @subpackage intellidata
 * @copyright  2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace bi_intellidata\output\forms;

use bi_intellidata\helpers\DBManagerHelper;
use bi_intellidata\persistent\datatypeconfig;
use bi_intellidata\services\datatypes_service;

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Edit config form.
 */
class bi_intellidata_edit_config extends \moodleform {

    /**
     * @param string $datatype_for_edit
     * @return string
     */
    public function get_datatype_schema_description(string $datatype_for_edit): string {
        global $DB;
        $table_comment_description = '';
        if ($DB->get_manager()->table_exists($datatype_for_edit)) {
            $xmldb_files = DBManagerHelper::get_install_xml_files(true);

            $table_comment_description = DBManagerHelper::get_field_for_table_from_xml_info($datatype_for_edit, $xmldb_files, 'comment_attribute');
        }
        return $table_comment_description;
    }

    /**
     * @throws \coding_exception
     */
    public function definition() {
        $mform = $this->_form;
        $data = $this->_customdata['data'];

        if (isset($this->_customdata['is_required']) && $this->_customdata['is_required']) {
            $this->required_form();
        } else {
            $this->optional_form();
        }

        $mform->addElement('hidden', 'datatype');
        $mform->setType('datatype', PARAM_ALPHA);

        $mform->addElement('hidden', 'tableindex');
        $mform->setType('tableindex', PARAM_TEXT);

        $this->add_action_buttons();
        $this->set_data($data);
    }

    protected function required_form() {
        $mform = $this->_form;

        $options = [
            datatypeconfig::TABLETYPE_REQUIRED => get_string('required', 'bi_intellidata'),
            datatypeconfig::TABLETYPE_OPTIONAL => get_string('optional', 'bi_intellidata')
        ];
        $mform->addElement('select', 'tabletype', get_string('tabletype', 'bi_intellidata'), $options);
        $mform->setType('tabletype', PARAM_INT);
    }

    protected function optional_form() {
        $mform = $this->_form;
        $data = $this->_customdata['data'];
        $config = $this->_customdata['config'];
        $exportlog = $this->_customdata['exportlog'];

        $data->enableexport = (!empty($exportlog)) ? 1 : 0;

        $options = [
            datatypeconfig::STATUS_ENABLED => get_string('enabled', 'bi_intellidata'),
            datatypeconfig::STATUS_DISABLED => get_string('disabled', 'bi_intellidata'),
        ];
        $mform->addElement('select', 'status', get_string('status', 'bi_intellidata'), $options);
        $mform->setType('status', PARAM_INT);

        $mform->addElement('advcheckbox', 'enableexport', get_string('enableexport', 'bi_intellidata'));
        $mform->setType('enableexport', PARAM_INT);
        $mform->disabledIf('enableexport', 'status', 'neq', datatypeconfig::STATUS_ENABLED);

        $mform->addElement('select', 'timemodified_field',
            get_string('timemodified_field', 'bi_intellidata'), ['' => ''] + $config->timemodifiedfields);
        $mform->setType('timemodified_field', PARAM_ALPHA);

        if (!empty($config->observer)) {
            $mform->addElement('advcheckbox', 'events_tracking', get_string('events_tracking', 'bi_intellidata'));
            $mform->setType('events_tracking', PARAM_INT);
        }

        $mform->addElement('advcheckbox', 'filterbyid', get_string('filterbyid', 'bi_intellidata'));
        $mform->setType('filterbyid', PARAM_INT);
        $mform->disabledIf('filterbyid', 'timemodified_field', 'neq', '');

        $mform->addElement('advcheckbox', 'rewritable', get_string('rewritable', 'bi_intellidata'));
        $mform->setType('rewritable', PARAM_INT);
        $mform->disabledIf('rewritable', 'timemodified_field', 'neq', '');
        $mform->disabledIf('rewritable', 'filterbyid', 'checked');
    }

    /**
     * @param bool $cancel
     * @param null $submitlabel
     * @throws \coding_exception
     */
    public function add_action_buttons($cancel = true, $submitlabel = null) {
        if (is_null($submitlabel)) {
            $submitlabel = get_string('savechanges');
        }
        $mform =& $this->_form;

        // When two elements we need a group.
        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);

        // Show a confirmation dialog when the Reset button is clicked.
        $question = get_string('resetdatatype', 'bi_intellidata');
        $aurl = '';
        $form_id = 'mform1';
        $buttonarray[] = &$mform->createElement('submit', 'reset', get_string('resettodefault', 'bi_intellidata'), [
            'onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
        ]);

        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}