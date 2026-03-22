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
 * @author Simon Chester <simon.chester@totara.com>
 * @package mfa_totp
 */

namespace mfa_totp\webapi\middleware;

use Closure;
use moodle_exception;
use mfa_totp\totp;
use totara_webapi\client_aware_exception;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;

/**
 * Validate that the supplied token is valid for the supplied secret.
 */
class validate_token implements middleware {
    /**
     * Current time.
     *
     * @var int
     */
    private int $time;

    public function __construct() {
        $this->time = time();
    }

    /** @inheritDoc */
    public function handle(payload $payload, Closure $next): result {
        $input = $payload->get_variable('input');

        $totp = new totp($input['secret']);
        if (!$totp->validate($this->time, $input['token'])) {
            $e = new moodle_exception('error:invalid_token', 'mfa_totp');
            throw new client_aware_exception($e, ['category' => 'mfa_totp/invalid_token']);
        }

        return $next($payload);
    }
}
