<?php
/**
 * This file is part of Totara Core
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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @package core_filter
 */

if (!defined('MOODLE_INTERNAL')) {
    // It must be included from a Moodle page
    die('Direct access to this script is forbidden.');
}

require_once ($CFG->libdir . '/formslib.php');

class filter_global_settings_form extends \moodleform {
    /**
     * The form definition
     */
    public function definition() {
        /** @var MoodleQuickForm $mform object definition */
        $mform =& $this->_form;

        list($context) = get_context_info_array($this->_customdata['contextid']);
        $availablefilters = filter_get_available_in_context($context);
        $stroff = get_string('off', 'filters');
        $stron = get_string('on', 'filters');
        $strdefaultoff = get_string('defaultx', 'filters', $stroff);
        $strdefaulton = get_string('defaultx', 'filters', $stron);
        
        $activechoices = array(
            TEXTFILTER_INHERIT => '',
            TEXTFILTER_OFF => $stroff,
            TEXTFILTER_ON => $stron,
        );
        
        $mform->addElement('hidden', 'contextid', $this->_customdata['contextid']);
        $mform->setType('contextid', PARAM_INT);

        foreach ($availablefilters as $filter => $filterinfo) {
            if ($filterinfo->inheritedstate == TEXTFILTER_ON) {
                $activechoices[TEXTFILTER_INHERIT] = $strdefaulton;
            } else {
                $activechoices[TEXTFILTER_INHERIT] = $strdefaultoff;
            }

            $mform->addElement('select', $filter, filter_get_name($filter), $activechoices);
            $mform->setType($filter, PARAM_TEXT);
        }

        $this->add_action_buttons();
    }
}
