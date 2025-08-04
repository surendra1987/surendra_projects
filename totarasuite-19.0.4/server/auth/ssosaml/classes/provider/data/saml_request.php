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
 * @property-read string $binding
 * @property-read string $url
 * @property-read array $data
 * @property-read string $request_id
 */
class saml_request {
    /**
     * @var array Internal cache of data
     */
    protected array $data = [];

    /**
     * @param string $binding
     * @param string $url
     * @param string $request_id
     * @param array $data
     */
    public function __construct(string $binding, string $url, string $request_id, array $data = []) {
        $this->data['binding'] = $binding;
        $this->data['url'] = $url;
        $this->data['data'] = $data;
        $this->data['request_id'] = $request_id;
    }

    public function __get(string $name) {
        return $this->data[$name] ?? '';
    }
}
