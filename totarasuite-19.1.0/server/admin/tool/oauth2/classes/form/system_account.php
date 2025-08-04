<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package tool_oauth2
 */

namespace tool_oauth2\form;
defined('MOODLE_INTERNAL') || die();

use core\form\persistent;

/**
 * Label form for system account.
 */
class system_account extends persistent {
    /** @var string $persistentclass */
    protected static $persistentclass = 'core\\oauth2\\system_account';

    /** @var array $fieldstoremove */
    protected static $fieldstoremove = array('submitbutton', 'action');

    /**
     * Definition of the form
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $system_account = $this->get_persistent();

        // Username - mandatory
        $mform->addElement('text', 'username', get_string('systemaccountname', 'tool_oauth2'));
        $mform->addRule('username', null, 'required', null, 'client');
        $mform->addRule('username', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType('username', PARAM_TEXT);

        // Email - Optional/can be blank
        $mform->addElement('text', 'email', get_string('systemaccountemail', 'tool_oauth2'));
        $mform->addRule('email', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType('email', PARAM_TEXT);

        $mform->addElement('hidden', 'issuerid', $system_account->get('issuerid'));
        $mform->setConstant('issuerid', $this->_customdata['issuerid']);
        $mform->setType('issuerid', PARAM_INT);

        $mform->addElement('hidden', 'id', $system_account->get('id'));
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges', 'tool_oauth2'));
    }

}

