<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totaralearning.com>
 * @package totara_customfield
 */

defined('MOODLE_INTERNAL') || die();

class customfield_integer extends customfield_base {

    use \totara_customfield\traits\integer_field_helper;

    protected const LANG_COMPONENT = 'totara_customfield';

    /**
     * @inheritDoc
     */
    public function edit_field_add(&$mform): void {
        $this->edit_integer_field_add($mform);
    }

    /**
     * @inheritDoc
     */
    public function edit_validate_field($itemnew, $prefix, $tableprefix) {
        $errors = parent::edit_validate_field($itemnew, $prefix, $tableprefix);
        if ($errors) {
            return $errors;
        }
        return $this->edit_validate_integer_field($itemnew);
    }
}