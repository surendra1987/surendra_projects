<?php
/**
 * This file is part of Totara Core
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_customfield
 */

namespace totara_customfield\webapi;

use coding_exception;
use dml_exception;
use stdClass;
use totara_customfield\exceptions\customfield_validation_exception;

class customfield_resolver_helper {
    private string $item_type;
    private string $table_prefix;
    private array $validation_errors;

    /**
     * @param string $item_type
     * @throws coding_exception
     */
    public function __construct(string $item_type) {
        $this->item_type = $item_type;
        $customfield_item_info = customfield_get_item_information($this->item_type);
        $this->table_prefix = $customfield_item_info['info_table_prefix'];
    }

    /**
     * Function will take in an item and an array of customfields and update the associated custom fields
     * for the item. This should be called inside a transaction while updating the item. See DB->start_delegated_transaction
     *
     * @param stdClass $item
     * @param array $custom_fields<string, string>
     * @param array $field_conditions - used to narrow down custom fields where types are used (e.g. organisations / positions - filter on typeid)
     * @param array $delete_conditions - used to specify a field that is unique to the *_info_data table (E.g. courseid, organsationid, positionid)
     * @return void
     * @throws dml_exception
     * @throws customfield_validation_exception
     */
    public function save_customfields_for_item(stdClass $item, array $custom_fields, array $field_conditions, array $delete_conditions): void {
        global $DB;
        $fields_to_delete = [];

        // prepare custom fields on the item
        foreach ($custom_fields as $custom_field) {
            $field_shortname = $custom_field['shortname'];
            $key = 'customfield_' . $field_shortname;
            $item->$key = $custom_field['data'];
            // any fields which have null set are asking to have their field_data record deleted
            if (is_null($custom_field['data'])) {
                $fields_to_delete[$field_shortname] = true;
            }
        }

        // validate the custom fields
        $this->validation_errors = customfield_validation($item, $this->item_type, $this->table_prefix);
        if (!empty($this->validation_errors)) {
            throw new customfield_validation_exception("validation errors found for custom fields");
        }

        // set up the custom fields so we use any existing ones in conjunction with the updates
        $field_definitions = customfield_get_fields_definition($this->table_prefix, $field_conditions);
        $existing_field_data = customfield_get_data($item, $this->table_prefix, $this->item_type, false);
        // we only support basic types - json types are not currently supported (url, text area, location, files)
        $whitelist = ['checkbox', 'menu', 'text', 'datetime', 'integer', 'decimal'];

        foreach ($field_definitions as $field) {
            if (isset($item->{'customfield_' . $field->shortname}) || isset($fields_to_delete[$field->shortname])) {
                if ($field->required === "1" && isset($fields_to_delete[$field->shortname])) {
                    $this->validation_errors[] = $field->shortname . " is required and cannot be deleted";
                }
                // not every field is supported - e.g. the json fields are not whitelisted (url, text area, location, file)
                if (!in_array($field->datatype, $whitelist)) {
                    $this->validation_errors[] = $field->shortname . "Unsupported custom field datatype used";
                }
                // we are overriding the default with the new value - continue on as we don't want to overwrite with the default
                continue;
            }

            // this is here so that if the request only has one of the fields - we do not remove previously set fields
            if (isset($existing_field_data[$field->shortname])) {
                $item->{'customfield_' . $field->shortname} = $existing_field_data[$field->shortname];
            }
        }

        if (count($this->validation_errors) > 0) {
            throw new customfield_validation_exception("Validation errors found for custom fields");
        }

        // delete existing customfield_data for the item & save the new
        $DB->delete_records($this->table_prefix . '_info_data', $delete_conditions);
        customfield_save_data($item, $this->item_type, $this->table_prefix, true);
    }

    public function get_validation_errors(): array {
        return $this->validation_errors;
    }
}
