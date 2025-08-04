<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\resolver\type;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_perform\models\activity\subject_static_instance as subject_static_instance_model;

class subject_static_instance extends type_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        /** @var subject_static_instance_model $source */
        if (!$source instanceof subject_static_instance_model) {
            throw new coding_exception(__METHOD__ . ' requires subject_static_instance model');
        }

        if ($field === 'job_assignment') {
            return $source->get_job_assignment();
        }

        if ($field === 'former_manager') {
            return $source->get_former_manager();
        }

        throw new coding_exception("'$field' field is not implemented yet");
    }
}
