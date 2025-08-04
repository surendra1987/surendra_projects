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
 * @package core_mfa
 */

namespace core_mfa;

use core_component;

/**
 * Public API for the MFA system
 */
class mfa {
    /**
     * @var array|string[]|null
     */
    protected static ?array $incompatible_plugins = null;

    /**
     * Get the list of incompatible mfa plugins.
     *
     * @return array|string[]
     */
    public static function get_incompatible_auth_plugins(): array {
        if (!is_array(self::$incompatible_plugins)) {
            $plugins = core_component::get_plugin_list('auth');
            $incompatible = [];
            foreach ($plugins as $auth => $notused) {
                $plugin = get_auth_plugin($auth);
                if (!$plugin::supports_mfa()) {
                    $incompatible[] = $auth;
                }
            }
            self::$incompatible_plugins = $incompatible;
        }

        return self::$incompatible_plugins;
    }

    /**
     * Checks auth plugin are compatible with MFA
     *
     * @return bool
     */
    public static function has_incompatible_auth_plugins(): bool {
        return !empty(self::get_incompatible_auth_plugins());
    }
}
