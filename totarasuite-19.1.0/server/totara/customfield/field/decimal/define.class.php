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

use totara_customfield\interfaces\decimal;

/** @var core_config $CFG */
require_once($CFG->dirroot.'/totara/customfield/definelib.php');

class customfield_define_decimal extends customfield_define_base implements decimal {

    use \totara_customfield\traits\decimal_field_helper;
    protected const LANG_COMPONENT = 'totara_customfield';
    /**
     * @inheritDoc
     */
    public function define_form_specific(&$form) {
        $this->define_decimal_form_specific($form);
    }

    /**
     * @inheritDoc
     */
    public function define_validate_specific($data, $files, $tableprefix) {
        $errors = parent::define_validate_specific($data, $files, $tableprefix);
        if ($errors) {
            return $errors;
        }
        return $this->define_decimal_validate_specific($data);
    }

    /**
     * @inheritDoc
     */
    public function define_save_preprocess($data, $old = null) {
        return $this->define_decimal_save_preprocess($data);
    }
}