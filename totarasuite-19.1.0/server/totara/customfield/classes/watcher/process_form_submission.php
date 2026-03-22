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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Mark Metcalfe <mark.metcalfe@totara.com>
 * @package totara_customfield
 */

namespace totara_customfield\watcher;

defined('MOODLE_INTERNAL') || die();

use core\hook\moodleform_process_submission;

/**
 * Override moodleform submission.
 */
class process_form_submission {

    /**
     * Set the default value for custom fields in the form submission if the submitted value is empty for decimal and integer fields.
     * @param moodleform_process_submission $hook
     * @return void
     */
    public static function set_default_value(moodleform_process_submission $hook): void {
        foreach ($hook->submission as $field_key => &$submitted_value) {
            if (!is_string($submitted_value) ||
                !str_starts_with($field_key, 'customfield_') ||
                !in_array($hook->mform->_form->_types[$field_key] ?? '', ['float', 'integer'])
            ) {
                // We only care about setting the default value if it is a integer or decimal (float) custom field.
                continue;
            }
            $default_value = $hook->mform->_form->_defaultValues[$field_key] ?? null;
            if (isset($default_value) && strlen($submitted_value) === 0) {
                $submitted_value = $default_value;
            }
        }
    }

}
