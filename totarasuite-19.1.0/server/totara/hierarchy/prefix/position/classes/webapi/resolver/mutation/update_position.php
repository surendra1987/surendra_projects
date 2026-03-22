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
 * @package hierarchy_position
 */

namespace hierarchy_position\webapi\resolver\mutation;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core\exception\unresolved_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use core_config;
use dml_exception;
use dml_transaction_exception;
use hierarchy;
use hierarchy_position\exception\position_exception;
use hierarchy_position\reference\hierarchy_position_framework_record_reference;
use hierarchy_position\reference\hierarchy_position_record_reference;
use hierarchy_position\reference\hierarchy_position_type_record_reference;
use hierarchy_position\webapi\resolver\position_helper;
use stdClass;
use totara_customfield\exceptions\customfield_validation_exception;
use totara_customfield\webapi\customfield_resolver_helper;

global $CFG;
/** @var core_config $CFG */
require_once $CFG->dirroot.'/totara/hierarchy/lib.php';

/**
 * Mutation to create a position
 */
class update_position extends mutation_resolver {
    use position_helper;

    /**
     * Updates a position.
     *
     * @param array $args
     * @param execution_context $ec
     * @return array
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws unresolved_record_reference
     * @throws position_exception
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $DB;

        $target_position = $args['target_position'] ?? [];
        $input = $args['input'] ?? [];

        $target_position = static::load_target_position($target_position);

        $framework = static::get_framework($target_position, $input['framework'] ?? null);
        $type = static::get_type($target_position, $input['type'] ?? null);

        // Deliberately trying to set the parent to null
        if (array_key_exists('parent', $input) && $input['parent'] === null) {
            $parent = null;
        } else {
            $parent = static::get_parent($target_position, $input['parent'] ?? null);
        }

        static::validate_parent($parent, $target_position, $framework);

        if (!empty($input['idnumber']) && ($input['idnumber'] != $target_position->idnumber)) {
            static::validate_idnumber_is_unique($input['idnumber']);
        }

        if (isset($input['custom_fields'])) {
            if (!$type) {
                throw new position_exception("Position type could not be determined. A type is necessary to update custom fields.");
            }

            if (!static::validate_custom_fields($input['custom_fields'], $type->id)) {
                throw new position_exception("The custom fields could not be updated. Check that the custom fields are associated with the position's type.");
            }
        }

        $item = static::prepare_position($target_position, $input, $framework, $type, $parent);
        $hierarchy = hierarchy::load_hierarchy('position');

        $transaction = $DB->start_delegated_transaction();

        $position = $hierarchy->update_hierarchy_item(
            $target_position->id,
            $item,
            true,
            true,
            false
        );

        // When updating an organisation's framework, ensure all child items are moved to the new framework as well.
        // This adjustment is handled separately and not through the `update_hierarchy_item()` method.
        // Note: This logic will be removed once TL-42690 is implemented.
        if ($framework->id !== $target_position->frameworkid) {
            static::update_framework_of_children($position, $position->frameworkid);
        }

        $item->id = $position->id;

        // update the custom fields
        if (isset($input['custom_fields'])) {
            $customfield_helper = new customfield_resolver_helper('position');
            try {
                $customfield_helper->save_customfields_for_item(
                    $item,
                    $input['custom_fields'],
                    ['typeid' => $item->typeid],
                    ['positionid' => $item->id]
                );
            } catch (customfield_validation_exception $exception) {
                $error_message = implode(', ', $customfield_helper->get_validation_errors());
                throw new position_exception('Error saving custom fields: ' . $error_message);
            }
        }
        $transaction->allow_commit();

        // load up custom fields for the position
        $custom_fields = customfield_get_custom_fields_and_data_for_items('position', [$position->id]);
        // add the custom fields to the execution context for the position type resolver
        $ec->set_variable('custom_fields', $custom_fields);

        return [
            'position' => $position
        ];
    }

    /**
     * Retrieves the existing type on the given position, if it has one.
     *
     * @param object $position The given position
     * @return stdClass|null Null if no type is applied to the position
     * @throws dml_exception
     */
    private static function get_existing_type(object $position): ?stdClass {
        try {
            return hierarchy_position_type_record_reference::load_for_viewer(
                [
                    'id' => $position->typeid,
                ]
            );
        } catch (unresolved_record_reference $exception) {
            return null;
        }
    }

    /**
     * Retrieves the type
     *
     * @param object $position The target position
     * @param array|null $type_ref The type to load, if null the type from the position will be used
     * @return stdClass|null the type or null if the type could not be resolved (for example when the type = 0 (unclassified))
     * @throws dml_exception
     * @throws position_exception
     */
    private static function get_type(object $position, ?array $type_ref): ?stdClass {
        if ($type_ref === null) {
            return static::get_existing_type($position);
        }

        if (empty($type_ref)) {
            return null;
        }

        return static::load_type($type_ref);
    }

    /**
     * Retrieves the framework
     * @param object $position The target position
     * @param array|null $framework_ref The reference to the framework to load, if null the position's framework will be used
     * @return object The framework
     * @throws dml_exception
     * @throws position_exception
     * @throws unresolved_record_reference
     */
    private static function get_framework(object $position, ?array $framework_ref): stdClass {
        if (!empty($framework_ref)) {
            return static::load_framework($framework_ref);
        }

        return hierarchy_position_framework_record_reference::load_for_viewer(
            [
                'id' => $position->frameworkid
            ]
        );
    }

    /**
     * Retrieves the existing parent for the given position
     * @param object $position The given position
     * @return stdClass|null Returns the parent object or null if the given position doesn't have a parent, or if the parent is "0"
     * @throws dml_exception
     */
    private static function get_existing_parent(object $position): ?stdClass {
        if ($position->parentid === "0") {
            return null;
        }

        try {
            return hierarchy_position_record_reference::load_for_viewer(
                [
                    'id' => $position->parentid
                ]
            );
        } catch (unresolved_record_reference $exception) {
            return null;
        }
    }

    /**
     * Retrieves the parent
     * @param object $position The target position
     * @param array|null $parent_ref The reference of the parent position
     * @return object|null the parent position
     * @throws dml_exception
     * @throws position_exception If the parent is the same as the target position
     */
    private static function get_parent(object $position, ?array $parent_ref): ?stdClass {
        if ($parent_ref === null) {
            return static::get_existing_parent($position);
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
            new require_advanced_feature('positions'),
            new require_user_capability('totara/hierarchy:updateposition')
        ];
    }
}
