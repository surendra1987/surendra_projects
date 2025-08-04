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

namespace mod_perform\webapi\middleware;

use core\webapi\resolver\payload;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\helpers\external_participant_token_validator;

/**
 * Interceptor that uses a participant instance reference structure in a payload
 * to retrieve a participant instance.
 */
class require_participant_instance_by_external extends require_participant_instance {

    /**
     * Retrieves a participant instance given the data in the incoming payload.
     *
     * @param payload $payload payload to parse.
     *
     * @return ?participant_instance the participant instance if it was found.
     */
    protected function load_pi(payload $payload): ?participant_instance {
        $token = static::payload_value($this->id_key, $payload);
        if (!$token) {
            return null;
        }

        $validator = new external_participant_token_validator($token);
        if (!$validator->is_valid()) {
            return null;
        }

        return $validator->get_participant_instance();
    }
}
