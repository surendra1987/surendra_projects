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

namespace mod_perform\webapi\resolver\mutation;

use core\webapi\middleware\require_advanced_feature;
use mod_perform\webapi\middleware\require_participant_instance_by_external as middleware_participant_instance;

/**
 * Handles the "mod_perform_close_participant_instance_by_external_nosession" GraphQL mutation.
 */
class close_participant_instance_by_external_nosession extends close_participant_instance {

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('performance_activities'),
            middleware_participant_instance::create(
                'input.' . middleware_participant_instance::DEF_TOKEN_KEY,
                false
            )
        ];
    }
}
