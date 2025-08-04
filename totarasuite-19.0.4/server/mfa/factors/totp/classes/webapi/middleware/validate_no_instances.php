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
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use mfa_totp\factor;

/**
 * Validate that the secret is a valid secret, and is strong enough.
 */
class validate_no_instances implements middleware {
    /** @inheritDoc */
    public function handle(payload $payload, Closure $next): result {
        global $USER;
        if (!(new factor())->user_can_register($USER->id)) {
            throw new \coding_exception('User already has instance');
        }

        return $next($payload);
    }
}
