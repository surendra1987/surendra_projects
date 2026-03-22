<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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

namespace auth_ssosaml\webapi\middleware;

use auth_ssosaml\model\idp;
use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;

/**
 * Verify the specified IdP actually exists.
 *
 */
class verify_idp implements middleware {
    /**
     * @var string Key for the ID field
     */
    protected string $id_key;

    /**
     * @param string $id_key
     */
    private function __construct(string $id_key) {
        $this->id_key = $id_key;
    }

    /**
     * @param string $id_key
     * @return static
     */
    public static function from(string $id_key): self {
        return new self($id_key);
    }

    /**
     * Execute the middleware, and if the capability check fails throw an exception.
     *
     * @param payload $payload
     * @param Closure $next
     * @return result
     */
    public function handle(payload $payload, Closure $next): result {
        $id = $this->get_payload_value($this->id_key, $payload);

        if (empty($id)) {
            throw new \coding_exception('No id provided');
        }

        $idp = idp::load_by_id($id);
        if (!$idp) {
            throw new \coding_exception('No id provided');
        }

        $payload->set_variable('idp', $idp);

        return $next($payload);
    }

    /**
     * Common helper method to extract a single value from a posted payload.
     *
     * @param string $payload_keys
     * @param payload $payload
     * @return mixed|null
     */
    protected function get_payload_value(string $payload_keys, payload $payload) {
        $keys = explode('.', $payload_keys);

        $initial = array_shift($keys);
        $result = $payload->get_variable($initial);

        if ($result) {
            foreach ($keys as $key) {
                $result = $result[$key] ?? null;
            }
        }

        return $result;
    }
}