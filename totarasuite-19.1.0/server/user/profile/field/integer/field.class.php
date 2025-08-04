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
 * @package profilefield_integer
 */

class profile_field_integer extends profile_field_base {

    use \totara_customfield\traits\integer_field_helper;

    protected const LANG_COMPONENT = 'profilefield_integer';

    /**
     * @inheritDoc
     */
    public function display_data() {
        return static::display_item_data($this->data);
    }

    /**
     * @inheritDoc
     */
    public function edit_field_add($mform) {
        $this->edit_integer_field_add($mform);
    }

    /**
     * @inheritDoc
     */
    public function totara_sync_data_preprocess($itemnew) {
        return $this->sync_data_preprocess($itemnew);
    }

    /**
     * @inheritDoc
     */
    public function edit_validate_field($itemnew) {
        $errors = parent::edit_validate_field($itemnew);
        if ($errors) {
            return $errors;
        }
        return $this->edit_validate_integer_field($itemnew);
    }

    /**
     * Get the full name of this field
     *
     * @return string
     */
    public function get_display_fullname(): string {
        return format_string($this->field->name);
    }
}