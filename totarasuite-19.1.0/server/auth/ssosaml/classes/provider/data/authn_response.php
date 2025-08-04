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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider\data;

use coding_exception;

/**
 * Data object holding the response from an AuthnRequest
 *
 * @property-read string|null $in_response_to ID of the AuthNRequest. Null if initiated from the IdP
 * @property-read string $status Status of the response
 * @property-read string $issuer EntityId of the IdP
 * @property-read string $expired_at Timestamp the Assertion will expire
 * @property-read int|null $session_not_on_or_after Timestamp after which the session must be discarded
 * @property-read string|null $session_index Session index used for Single Logout. Null if IdP does not support single logout
 * @property-read string $name_id Subject nameId returned
 * @property-read string $name_id_format Format of nameId returned
 * @property-read array $attributes Attributes provided
 * @property-read string|null $relay_state Url to redirect to after login
 * @property-read int|null $log_id The attached log id this auth generated
 */
class authn_response {

    private array $data;

    private function __construct(array $data) {
        $this->data = $data;
    }

    public static function make(array $data): self {
        $required_fields = ['name_id', 'name_id_format', 'issuer', 'status'];

        foreach ($required_fields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new coding_exception("$field field is required in data provided");
            }
        }

        return new self($data);
    }

    public function __get($name) {
        return $this->data[$name] ?? null;
    }

    public function __isset($name): bool {
        return !empty($this->data[$name]);
    }

    /**
     * Get value of attribute.
     * @param string $name
     *
     * @return array|string|null
     */
    public function get_attribute(string $name) {
        if (strtolower($name) === 'nameid') {
            return $this->name_id;
        }

        if (empty($this->data['attributes'][$name])) {
            return null;
        }

        return $this->data['attributes'][$name];
    }

}