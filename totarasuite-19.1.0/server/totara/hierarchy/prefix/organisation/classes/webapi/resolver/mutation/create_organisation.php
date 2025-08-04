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
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use dml_exception;
use dml_transaction_exception;
use hierarchy_organisation\exception\organisation_exception;
use hierarchy_organisation\webapi\resolver\organisation_helper;
use required_capability_exception;
use totara_customfield\exceptions\customfield_validation_exception;
use totara_customfield\webapi\customfield_resolver_helper;

/** @var \core_config $CFG */
global $CFG;
require_once $CFG->dirroot.'/totara/hierarchy/lib.php';

/**
 * Mutation to create an organisation
 */
class create_organisation extends mutation_resolver {
    use organisation_helper;

    /**
     * Creates an organisation.
     *
     * @param array $args
     * @param execution_context $ec
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws required_capability_exception
     * @throws organisation_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $DB, $USER;

        $input = $args['input'] ?? [];

        $framework = static::load_framework($input['framework'] ?? null);

        $type = !empty($input['type']) ? static::load_type($input['type']) : null;
        $parent = !empty($input['parent']) ? static::load_parent($input['parent']) : null;

        static::validate_parent($parent, null, $framework);
        static::validate_idnumber_is_unique($input['idnumber'] ?? null);

        if (isset($input['custom_fields'])) {
            if ($type === null) {
                throw new organisation_exception("Custom fields can not be sent if the organisation type is missing.");
            }

            if (!static::validate_custom_fields($input['custom_fields'], $type->id)) {
                throw new organisation_exception("Custom fields are not correct or do not belong to the provided organisation type.");
            }
        }

        $item = static::prepare_organisation(null, $input, $framework, $type, $parent);
        $hierarchy = \hierarchy::load_hierarchy('organisation');

        $transaction = $DB->start_delegated_transaction();

        $organisation = $hierarchy->add_hierarchy_item($item, $item->parentid, $item->frameworkid, true, true, false);
        $item->id = $organisation->id;

        // save the custom fields
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
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('organisations'),
            new require_user_capability('totara/hierarchy:createorganisation')
        ];
    }

}
