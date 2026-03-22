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
 * @package hierarchy_position
 */

namespace hierarchy_position\webapi\resolver\mutation;

defined('MOODLE_INTERNAL') || die();

use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use dml_exception;
use hierarchy;
use hierarchy_position\exception\position_exception;
use hierarchy_position\webapi\resolver\position_helper;
use totara_customfield\exceptions\customfield_validation_exception;
use totara_customfield\webapi\customfield_resolver_helper;

global $CFG;
/** @var \core_config $CFG */
require_once $CFG->dirroot.'/totara/hierarchy/lib.php';

/**
 * Mutation to create a position
 */
class create_position extends mutation_resolver {
    use position_helper;

    /**
     * Creates a position.
     *
     * @param array $args
     * @param execution_context $ec
     * @return array
     * @throws dml_exception
     * @throws position_exception
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $DB, $USER;

        $input = $args['input'] ?? [];

        $framework = static::load_framework($input['framework'] ?? null);

        $type = !empty($input['type']) ? static::load_type($input['type']) : null;
        $parent = !empty($input['parent']) ? static::load_parent($input['parent']) : null;

        static::validate_parent($parent, null, $framework);
        static::validate_idnumber_is_unique($input['idnumber'] ?? null);

        if (isset($input['custom_fields'])) {
            if (!isset($type) || !$type) {
                throw new position_exception("Custom fields can not be sent if the position type is missing.");
            }
            if (!static::validate_custom_fields($input['custom_fields'], $type->id)) {
                throw new position_exception("Custom fields are not correct or do not belong to the provided position type.");
            }
        }

        $item = static::prepare_position(null, $input, $framework, $type, $parent);
        $hierarchy = hierarchy::load_hierarchy('position');

        $transaction = $DB->start_delegated_transaction();

        $position = $hierarchy->add_hierarchy_item(
            $item,
            $item->parentid,
            $item->frameworkid,
            true,
            true,
            false
        );
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
                throw new position_exception('Error saving custom fields: ' . $error_message, 0, $exception);
            }
        }

        $transaction->allow_commit();

        // load up custom fields for the position
        $custom_fields = customfield_get_custom_fields_and_data_for_items('position', [$position->id]);
        // add the custom fields to the execution context for the position type resolver
        $ec->set_variable('custom_fields', $custom_fields);

        return [
            'position' => $position,
        ];
    }


    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('positions'),
            new require_user_capability('totara/hierarchy:createposition')
        ];
    }

}
