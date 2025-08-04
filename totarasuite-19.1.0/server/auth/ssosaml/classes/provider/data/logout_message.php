<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider\data;

/**
 * Data object holding the message from a logout request/response
 *
 * @property-read bool $status Status of the message
 * @property-read string $type Type of the message
 * @property-read string|null $in_response_to In response to of the message
 * @property-read string|null name_id Name id of the message
 * @property-read string|null $session_index Session index of the message
 * @property-read string|null $id ID of the message
 * @property-read string|null $relay_state Relay state of the message
 *
 *
 */

use auth_ssosaml\exception\response_validation;

/**
 * @property-read string|null $id
 * @property-read string|null $in_response_to
 * @property-read string|null $type
 * @property-read string|null $status
 * @property-read string|null $status_message
 * @property-read string|null $name_id
 * @property-read string|null $session_index
 * @property-read string|null $relay_state
 * @property-read int|null $log_id

 */
class logout_message {

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var string
     */
    private string $type;

    public const LOGOUT_REQUEST = 'REQUEST';

    public const LOGOUT_RESPONSE = 'RESPONSE';

    /**
     * @param string $type
     * @param array $data
     */
    public function __construct(string $type, array $data) {
        if (!in_array($type, [self::LOGOUT_REQUEST, self::LOGOUT_RESPONSE])) {
            throw response_validation\message_type::make(null, 'Type');
        }
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name) {
        if ($name === 'type') {
            return $this->type;
        }

        return $this->data[$name] ?? null;
    }
}