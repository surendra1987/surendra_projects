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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\webapi\middleware;

use auth_ssosaml\exception\duplicate_idp_entity_id_exception;
use auth_ssosaml\exception\invalid_metadata_exception;
use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use totara_webapi\client_aware_exception;

/**
 * Middleware to translate exceptions to client_aware_exceptions.
 */
class translate_exceptions implements middleware {
    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        try {
            return $next($payload);
        } catch (invalid_metadata_exception $e) {
            throw new client_aware_exception($e, ['category' => 'auth_ssosaml/invalid_metadata']);
        } catch (duplicate_idp_entity_id_exception $e) {
            throw new client_aware_exception($e, ['category' => 'auth_ssosaml/duplicate_idp_entity_id']);
        }
    }
}
