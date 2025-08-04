<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package mod_approval
 */

namespace mod_approval\webapi\resolver\type;

use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_approval\exception\helper\validation;
use mod_approval\model\assignment\assignment as assignment_model;


/**
 * Workflow Assignment type resolver
 */
class workflow_assignment extends type_resolver {
    /**
     * @param string $field
     * @param object $workflow_assignment
     * @param array $args
     * @param execution_context $ec
     *
     * @return mixed
     */
    public static function resolve(string $field, $workflow_assignment, array $args, execution_context $ec) {
        validation::instance_of($workflow_assignment, assignment_model::class, 'Expected assignment model');

        // Resolve assignment_type into it's enum.
        if ($field === 'assignment_type') {
            return $workflow_assignment->get_assignment_type_enum();
        }

        // There is no formatter, return the raw data.
        return $workflow_assignment->$field;
    }
}
