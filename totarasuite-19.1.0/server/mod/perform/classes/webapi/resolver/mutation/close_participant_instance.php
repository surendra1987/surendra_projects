<?php
/*
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use mod_perform\webapi\middleware\require_participant_instance;

/**
 * Handles the "mod_perform_close_participant_instance" GraphQL mutation.
 */
class close_participant_instance extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        // The require_participant_instance middleware retrieves the participant
        // instance and passes it via $args.
        $pi = $args[require_participant_instance::PI_KEY];

        $can_close = $pi->manual_closure_evaluation_result;
        if (!$can_close->passed()) {
            return ['success' => false, 'error' => $can_close->description()];
        }

        $pi->manually_close_and_complete();
        return ['success' => true, 'error' => ''];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('performance_activities'),
            new require_authenticated_user(),
            require_participant_instance::create(
                'input.' . require_participant_instance::DEF_ID_KEY,
                false
            )
        ];
    }
}
