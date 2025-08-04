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

class customfield_decimal extends customfield_base implements decimal {

    use \totara_customfield\traits\decimal_field_helper;
    protected const LANG_COMPONENT = 'totara_customfield';
    /**
     * @inheritDoc
     */
    public function edit_field_add(&$mform): void {
        $this->edit_decimal_field_add($mform);
    }

    /**
     * @inheritDoc
     */
    function edit_field_set_default(&$mform) {
        $this->edit_decimal_field_set_default($mform);
    }

    /**
     * @inheritDoc
     */
    public function edit_load_item_data(&$item) {
        $this->edit_load_item_deciaml_data($item);
    }

    /**
     * @inheritDoc
     */
    public function edit_validate_field($data, $prefix, $tableprefix) {
        $errors = parent::edit_validate_field($data, $prefix, $tableprefix);
        if ($errors) {
            return $errors;
        }
        return $this->edit_validate_decimal_field($data);
    }

    /**
     * @inheritDoc
     */
    public function edit_save_data_preprocess($data) {
        return $this->edit_save_decimal_data_preprocess($data);
    }
}