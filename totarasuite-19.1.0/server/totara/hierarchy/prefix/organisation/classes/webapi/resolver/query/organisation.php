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

namespace hierarchy_organisation\webapi\resolver\query;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core\exception\unresolved_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\query_resolver;
use dml_exception;
use hierarchy_organisation\exception\organisation_exception;
use hierarchy_organisation\reference\hierarchy_organisation_record_reference;

class organisation extends query_resolver {

    /**
     * @throws organisation_exception
     * @throws dml_exception|coding_exception
     */
    public static function resolve(array $args, execution_context $ec): array {
        $reference = $args['reference'] ?? [];
        if (empty($reference)) {
            throw new organisation_exception('The required variable "reference" is empty.');
        }

        try {
            $org = hierarchy_organisation_record_reference::load_for_viewer($reference);
            // grab the custom fields related to the organisation
            $custom_fields = customfield_get_custom_fields_and_data_for_items('organisation', [$org->id]);
            // add the custom fields to the execution context for the organisation type resolver
            $ec->set_variable('custom_fields', $custom_fields);
        } catch (unresolved_record_reference $exception) {
            return [
                'organisation' => null,
                'found' => false,
            ];
        }

        return [
            'organisation' => $org,
            'found' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('organisations'),
            new require_user_capability('totara/hierarchy:vieworganisation'),
        ];
    }
}
