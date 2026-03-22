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

namespace hierarchy_position\webapi\resolver\query;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core\exception\unresolved_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_user_capability;
use core\webapi\query_resolver;
use dml_exception;
use hierarchy_position\exception\position_exception;
use hierarchy_position\reference\hierarchy_position_record_reference;

class position extends query_resolver {

    /**
     * @throws position_exception
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec): array {
        $reference = $args['reference'];

        if (empty($reference)) {
           throw new position_exception('The required "reference" parameter is empty.');
        }

        try {
            $position = hierarchy_position_record_reference::load_for_viewer($reference);

            $custom_fields = customfield_get_custom_fields_and_data_for_items('position', [$position->id]);
            // add the custom fields to the execution context for the position type resolver
            $ec->set_variable('custom_fields', $custom_fields);
        } catch (unresolved_record_reference $exception) {
            return [
                'position' => null,
                'found' => false,
            ];
        }

        return [
            'position' => $position,
            'found' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('positions'),
            new require_user_capability('totara/hierarchy:viewposition'),
        ];
    }
}
