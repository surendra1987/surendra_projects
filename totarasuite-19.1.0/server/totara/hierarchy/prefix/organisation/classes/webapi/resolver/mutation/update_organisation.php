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
 * @author Wajdi Bshara <wajdi.bshara@aleido.com>
 * @package hierarchy_organisation
 */

namespace hierarchy_organisation\webapi\resolver\mutation;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core\exception\unresolved_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use dml_exception;
use hierarchy_organisation\exception\organisation_exception;
use hierarchy_organisation\reference\hierarchy_organisation_framework_record_reference;
use hierarchy_organisation\reference\hierarchy_organisation_record_reference;
use hierarchy_organisation\reference\hierarchy_organisation_type_record_reference;
use hierarchy_organisation\webapi\resolver\organisation_helper;
use stdClass;
use totara_customfield\exceptions\customfield_validation_exception;
use totara_customfield\webapi\customfield_resolver_helper;

global $CFG;
/** @var \core_config $CFG */
require_once $CFG->dirroot.'/totara/hierarchy/lib.php';

/**
 * Mutation to create an organisation
 */
class update_organisation extends mutation_resolver {
    use organisation_helper;

    /**
     * Updates an organisation.
     *
     * @param array $args
     * @param execution_context $ec
     * @return array
     * @throws dml_exception
     * @throws \dml_transaction_exception
     * @throws unresolved_record_reference
     * @throws organisation_exception|coding_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $DB;

        $target_organisation = $args['target_organisation'] ?? [];
        $input = $args['input'] ?? [];

        $target_organisation = static::load_target_organisation($target_organisation);

        $framework = static::get_framework($target_organisation, $input['framework'] ?? null);
        $type = static::get_type($target_organisation, $input['type'] ?? null);

        // Deliberately trying to set the parent to null
        if (array_key_exists('parent', $input) && $input['parent'] === null) {
            $parent = null;
        } else {
            $parent = static::get_parent($target_organisation, $input['parent'] ?? null);
        }

        static::validate_parent($parent, $target_organisation, $framework);

        if (!empty($input['idnumber']) && ($input['idnumber'] != $target_organisation->idnumber)) {
            static::validate_idnumber_is_unique($input['idnumber']);
        }

        if (isset($input['custom_fields'])) {
            if (!$type) {
                throw new organisation_exception("Organisation type could not be determined. A type is necessary to update custom fields.");
            }

            if (!static::validate_custom_fields($input['custom_fields'], $type->id)) {
                throw new organisation_exception("The custom fields could not be updated. Check that the custom fields are associated with the organisation's type and that the reference is correct.");
            }
        }

        $item = static::prepare_organisation($target_organisation, $input, $framework, $type, $parent);
        $hierarchy = \hierarchy::load_hierarchy('organisation');

        $transaction = $DB->start_delegated_transaction();

        $organisation = $hierarchy->update_hierarchy_item(
            $target_organisation->id,
            $item,
            true,
            true,
            false
        );

        // When updating an organisation's framework, ensure all child items are moved to the new framework as well.
        // This adjustment is handled separately and not through the `update_hierarchy_item()` method.
        // Note: This logic will be removed once TL-42690 is implemented.
        if ($framework->id !== $target_organisation->frameworkid) {
            static::update_framework_of_children($organisation, $organisation->frameworkid);
        }

        $item->id = $organisation->id;
        // update the custom fields
        if (isset($input['custom_fields'])) {
            $customfield_helper = new customfield_resolver_helper('organisation');
            try {
                $customfield_helper->save_customfields_for_item(
                    $item,
                    $input['custom_fields'],
                    ['typeid' => $item->typeid],
                    ['organisationid' => $item->id]
                );
            } catch (customfield_validation_exception $exception) {
                $error_message = implode(', ', $customfield_helper->get_validation_errors());
                throw new organisation_exception('Error saving custom fields: ' . $error_message);
            }
        }

        $transaction->allow_commit();

        // load up custom fields for the organisation
        $custom_fields = customfield_get_custom_fields_and_data_for_items('organisation', [$organisation->id]);
        // add the custom fields to the execution context for the organisation type resolver
        $ec->set_variable('custom_fields', $custom_fields);

        return [
            'organisation' => $organisation
        ];
    }

    /**
     * Retrieves the framework
     *
     * @param stdClass $organisation The target organisation
     * @param array|null $framework_ref The reference to the framework to load, if null the organisation's framework will be used
     * @return stdClass The framework
     * @throws dml_exception
     * @throws organisation_exception
     * @throws unresolved_record_reference|organisation_exception
     */
    private static function get_framework(stdClass $organisation, ?array $framework_ref): stdClass {
        if (!empty($framework_ref)) {
            return static::load_framework($framework_ref);
        }

        return hierarchy_organisation_framework_record_reference::load_for_viewer(
            [
                'id' => $organisation->frameworkid
            ]
        );
    }

    /**
     * Retrieves the existing type on the given organisation, if it has one.
     *
     * @param object $organisation
     * @return stdClass|null stdClass representing the type, or Null if no type is applied to the organisation
     * @throws dml_exception
     */
    private static function get_existing_type(object $organisation): ?stdClass {
        try {
            return hierarchy_organisation_type_record_reference::load_for_viewer(
                [
                    'id' => $organisation->typeid,
                ]
            );
        } catch (unresolved_record_reference $exception) {
            return null;
        }
    }

    /**
     * Retrieves the type
     * @param stdClass $organisation The target organisation
     * @param array|null $type_ref The type to load, if null the type from the organisation will be used
     * @return stdClass|null the type or null if the type could not be resolved (for example when the type = 0 (unclassified))
     * @throws dml_exception
     * @throws organisation_exception
     */
    private static function get_type(stdClass $organisation, ?array $type_ref): ?stdClass {
        if ($type_ref === null) {
            return static::get_existing_type($organisation);
        }

        if (empty($type_ref)) {
            return null;
        }

        return static::load_type($type_ref);
    }

    /**
     * Retrieves the existing parent for the given organisation
     * @param object $organisation The given organisation
     * @return stdClass|null Returns the parent object or null if the given organisation doesn't have a parent or if the parent is "0"
     * @throws dml_exception
     * @throws unresolved_record_reference
     */
    private static function get_existing_parent(object $organisation): ?stdClass {
        if ($organisation->parentid === "0") {
            return null;
        }

        try {
            return hierarchy_organisation_record_reference::load_for_viewer(
                [
                    'id' => $organisation->parentid
                ]
            );
        } catch (unresolved_record_reference $exception) {
            return null;
        }
    }

    /**
     * Retrieves the parent
     * @param stdClass $organisation The target organisation
     * @param int $framework_id The framework ID of the organisation
     * @param array|null $parent_ref The reference of the parent organisation
     * @return stdClass|null the parent organisation
     * @throws dml_exception
     * @throws organisation_exception If the parent is the same as the target organisation
     * @throws unresolved_record_reference
     */
    private static function get_parent(stdClass $organisation, ?array $parent_ref): ?stdClass {
        if ($parent_ref === null) {
            return static::get_existing_parent($organisation);
        }

        if (empty($parent_ref)) {
            return null;
        }

        return static::load_parent($parent_ref);
    }
    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('organisations'),
            new require_user_capability('totara/hierarchy:updateorganisation')
        ];
    }

}
