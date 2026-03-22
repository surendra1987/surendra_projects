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

namespace auth_ssosaml\local;

use auth_ssosaml\exception\saml_not_enabled;

/**
 * Small helper methods not tied specifically to the model, for internal use.
 */
class util {
    /**
     * Assert that the plugin is enabled.
     * @return void
     */
    public static function assert_saml_enabled(): void {
        if (!is_enabled_auth('ssosaml')) {
            throw new saml_not_enabled();
        }
    }

    /**
     * @return bool
     */
    public static function is_openssl_loaded(): bool {
        return extension_loaded('openssl');
    }
}
