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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\webapi\middleware;

use auth_ssosaml\exception\no_openssl_exception;
use auth_ssosaml\local\util;
use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;

/**
 * Verify the openssl extension is enabled.
 *
 */
class openssl_check implements middleware {
    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        // Make sure openssl is enabled
        if (!util::is_openssl_loaded()) {
            throw new no_openssl_exception();
        }

        return $next($payload);
    }
}