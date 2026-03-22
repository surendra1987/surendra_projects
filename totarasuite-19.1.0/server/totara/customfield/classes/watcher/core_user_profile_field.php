<?php
/**
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package totara_customfield
 */

namespace totara_customfield\watcher;

defined('MOODLE_INTERNAL') || die();

use core_user\hook\profile_field_set_data as user_profile_field;

class core_user_profile_field {
    /**
     * Set default value to "Yes" and disabled it for decimal or integer fields.
     * This requires as decimal or integer fields can not set to empty string
     *
     * @param user_profile_field $hook
     */
    public static function set_data(user_profile_field $hook) {
        $form = $hook->mform;
        $datatype = $form->_form->getElement('datatype')->getAttribute('value');
        if (('decimal' == $datatype || 'integer' == $datatype) && !$form->is_submitted()) {
            $form->_form->getElement('required')->setValue(1);
            $form->_form->getElement('required')->updateAttributes('disabled');
        }
    }
}