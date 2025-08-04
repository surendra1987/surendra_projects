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

namespace auth_ssosaml\webapi\middleware;

use auth_ssosaml\local\util;
use Closure;
use core\webapi\middleware\require_system_capability;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;

/**
 * Middleware for capability checking of the auth_ssosaml component setup.
 */
class require_capability extends require_system_capability {
    /**
     * @inheritDoc
     */
    public function __construct() {
        parent::__construct('auth/ssosaml:manage');
    }

    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        // Make sure SAML is enabled
        util::assert_saml_enabled();

        return parent::handle($payload, $next);
    }
}
