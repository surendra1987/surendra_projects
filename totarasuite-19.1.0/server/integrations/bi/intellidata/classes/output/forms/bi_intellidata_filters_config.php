<?php
/*
 * This file is part of Totara Learn
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
 * @author Arshad Anwer <arshad.anwer@totaralearning.com>
 * @package local_intellidata
 */

namespace bi_intellidata\output\forms;


use bi_intellidata\persistent\datatypeconfig;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Fields to filter configuration records
 */
class bi_intellidata_filters_config extends \moodleform {

    /**
     * @throws \coding_exception
     */
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('header', 'general', get_string('search_by', 'bi_intellidata'));
        $mform->addElement('text', 'datatype', get_string('datatype', 'bi_intellidata'));
        $mform->setType('datatype', PARAM_ALPHANUMEXT);
        $mform->setDefault('datatype', !empty($customdata['datatype']) ? $customdata['datatype'] : '');

        $options = [
            datatypeconfig::STATUS_ENABLED => get_string('enabled', 'bi_intellidata'),
            datatypeconfig::STATUS_DISABLED => get_string('disabled', 'bi_intellidata'),
        ];

        $mform->addElement('select', 'status', get_string('status', 'bi_intellidata'),
            array('' => get_string('option_all', 'bi_intellidata')) + $options);
        $mform->setDefault('status', (isset($customdata['status']) && $customdata['status'] !== '') ? $customdata['status'] : '');
        $mform->addHelpButton('status', 'status', 'bi_intellidata');

        $mform->addElement('select', 'exportenabled', get_string('export', 'bi_intellidata'),
            array('' => get_string('option_all', 'bi_intellidata')) + $options);
        $mform->setDefault('exportenabled', (isset($customdata['exportenabled']) && $customdata['exportenabled'] !== '') ? $customdata['exportenabled'] : '');
        $mform->addHelpButton('exportenabled', 'export', 'bi_intellidata');
        $this->add_action_buttons();
    }

    /**
     * @param bool $cancel
     * @param null $submitlabel
     * @throws \coding_exception
     */
    public function add_action_buttons($cancel = true, $submitlabel=null) {
        $mform =& $this->_form;

        // When two elements we need a group.
        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('search', 'bi_intellidata'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('clear', 'bi_intellidata'));
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }
}