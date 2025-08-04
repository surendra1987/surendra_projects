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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package hierarchy_organisation
 */

namespace hierarchy_organisation\webapi\resolver;

defined('MOODLE_INTERNAL') || die();

use core\exception\unresolved_record_reference;
use dml_exception;
use hierarchy_organisation\exception\organisation_exception;
use hierarchy_organisation\reference\hierarchy_organisation_framework_record_reference;
use hierarchy_organisation\reference\hierarchy_organisation_record_reference;
use hierarchy_organisation\reference\hierarchy_organisation_type_record_reference;
use stdClass;

trait organisation_helper {

    /**
     * Validates the target organisation exists and returns the organisation based on the given $target_organisation
     *
     * @param array $target_organisation The target organisation to validate and fetch
     * @return stdClass The fetched organisation
     * @throws dml_exception
     * @throws organisation_exception If the organisation doesn't exist
     */
    private static function load_target_organisation(array $target_organisation): stdClass {
        try {
            return hierarchy_organisation_record_reference::load_for_viewer($target_organisation);
        } catch (unresolved_record_reference $exception) {
            throw new organisation_exception("The target organisation does not exist or you do not have permissions to view it.", 0, $exception);
        }
    }

    /**
     * Validates the framework exists and returns the framework based on the given $framework_ref
     *
     * @param array $framework_ref The reference to the framework to validate and fetch
     * @return stdClass The framework
     * @throws dml_exception
     * @throws organisation_exception If the framework doesn't exist
     */
    private static function load_framework(array $framework_ref): stdClass {
        try {
            return hierarchy_organisation_framework_record_reference::load_for_viewer($framework_ref);
        } catch (unresolved_record_reference $exception) {
            throw new organisation_exception("The organisation framework does not exist or you do not have permissions to view it.", 0, $exception);
        }
    }

    /**
     * Validates the type exists and returns the type based on the given $type_ref
     * @param array $type_ref The reference to the type to validate and fetch
     * @return stdClass The type
     * @throws dml_exception
     * @throws organisation_exception If the type doesn't exist
     */
    private static function load_type(array $type_ref): stdClass {
        try {
            return hierarchy_organisation_type_record_reference::load_for_viewer($type_ref);
        } catch (unresolved_record_reference $exception) {
            throw new organisation_exception("The organisation type does not exist or you do not have permissions to view it.", 0, $exception);
        }
    }

    /**
     * Validates the parent organisation exists and is in the same framework as the given $framework_id and returns the parent
     * @param array $parent_ref The reference to the parent organisation
     * @return stdClass The parent organisation
     * @throws dml_exception
     * @throws organisation_exception If the parent organisation doesn't exist
     */
    private static function load_parent(array $parent_ref): stdClass {
        try {
            return hierarchy_organisation_record_reference::load_for_viewer($parent_ref);
        } catch (unresolved_record_reference $exception) {
            throw new organisation_exception("The parent organisation does not exist or you do not have permissions to view it.", 0, $exception);
        }
    }

    /**
     * Validates the parent organisation when compared with the given organisation and the framework
     * This will method will fail (throw an exception) when either of the following conditions are met:
     *  - The parent framework does not match the child organisation's framework
     *  - The parent organisation is the same as the child organisation
     * @param object|null $parent The parent object
     * @param object|null $child_organisation The child organisation
     * @param object $framework The framework for the child
     * @throws organisation_exception - Thrown when the parent is not valid
     */
    private static function validate_parent(?object $parent, ?object $child_organisation, object $framework): void {
        if ($parent === null) {
            return;
        }

        if ($parent->frameworkid != $framework->id) {
            throw new organisation_exception("The parent organisation belongs to a different framework.");
        }

        if ($child_organisation === null) {
            return;
        }

        if ($child_organisation->id == $parent->id) {
            throw new organisation_exception("The parent was resolved to be the same as the target organisation. The organisation cannot be a parent of itself.");
        }
    }

    /**
     * Validates the given ID number is unique
     * @param string|null $idnumber The ID number to check
     * @return bool True if the id number is unique
     * @throws dml_exception
     * @throws organisation_exception If the ID number isn't unique
     */
    private static function validate_idnumber_is_unique(?string $idnumber = null): bool {
        if ($idnumber === null) {
            return true;
        }

        try {
            hierarchy_organisation_record_reference::load_for_viewer(
                [
                    'idnumber' => $idnumber
                ]
            );

            throw new organisation_exception("The idnumber is utilised by an existing organisation.");
        } catch (unresolved_record_reference $exception) {
            return true;
        }
    }

    /**
     * Validates organisation custom fields against the specified type ID.
     *
     * @param array $custom_fields The custom fields to validate.
     * @param int $type_id The ID of the organisation type to validate against.
     * @return bool True if all custom fields are valid, false otherwise.
     * @throws dml_exception If a database error occurs.
     * @throws organisation_exception If custom fields don't exist for the organisation type
     */
    private static function validate_custom_fields(array $custom_fields, int $type_id): bool {
        global $DB;

        $nonexistent_fields = [];

        foreach ($custom_fields as $custom_field) {
            if (empty($custom_field['shortname'])) {
                throw new organisation_exception("A custom field entry was passed in without the shortname");
            }

            $custom_field_exists = $DB->record_exists(
                'org_type_info_field',
                [
                    'shortname' => $custom_field['shortname'],
                    'typeid' => $type_id
                ]
            );

            if (!$custom_field_exists) {
                $nonexistent_fields[] = $custom_field['shortname'];
            }
        }

        if (!empty($nonexistent_fields)) {
            throw new organisation_exception("The following custom fields do not exist in the organisation type: " . implode(', ', $nonexistent_fields));
        }

        return true;
    }

    /**
     * Prepares an organisation object based on the provided input.
     *
     * @param array $input The input data for the organisation.
     * @param object $framework The organisation framework object.
     * @param object|null $type The organisation type object or null.
     * @param object|null $parent The parent organisation object or null.
     * @return stdClass The prepared organisation object.
     * @throws organisation_exception
     */
    private static function prepare_organisation(?object $organisation, array $input, object $framework, ?object $type, ?object $parent): stdClass {
        global $USER;
        $item = new stdClass();

        if (array_key_exists('fullname', $input)) {
            if (empty($input['fullname'])) {
                throw new organisation_exception("The `fullname` field is required and cannot be null or empty.");
            }
            $item->fullname = $input['fullname'];
        }

        if (array_key_exists('idnumber', $input)) {
            $item->idnumber = $input['idnumber'];
        }

        if (array_key_exists('description', $input)) {
            $item->description = $input['description'];
        }

        $item->visible = $organisation->visible ?? 1;
        if (array_key_exists('visible', $input)) {
            $item->visible = $input['visible'] ? 1 : 0;
        }

        $item->frameworkid = $framework->id;
        $item->typeid = ($type) ? $type->id : 0;
        $item->parentid = ($parent) ? $parent->id : 0;
        $item->timemodified = time();
        /** @var object $USER */
        $item->usermodified = $USER->id;

        return $item;
    }

    /**
     * Updates the framework of children of a given parent with the given framework ID
     * Uses the parent's path to determine the children to update.
     * @param stdClass $parent The parent of the children to update
     * @param int $framework_id The framework ID to move the children to
     * @throws dml_exception
     */
    private static function update_framework_of_children(stdClass $parent, int $framework_id): void {
        global $DB;

        $params = [
            'frameworkid' => $framework_id,
            'itempath'  => "$parent->path/%",
        ];

        $sql = "UPDATE {org}
            SET frameworkid = :frameworkid
            WHERE (" . $DB->sql_like('path', ':itempath') . ")
        ";

        $DB->execute($sql, $params);
    }
}
