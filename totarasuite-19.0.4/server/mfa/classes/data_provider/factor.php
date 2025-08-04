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
 * @package core_mfa
 */

namespace core_mfa\data_provider;

use core\collection;
use core_mfa\factor as mfa_factor;
use core_mfa\factor_factory;
use core_plugin_manager;

/**
 * Data provider for factors.
 */
class factor {
    /**
     * Get factors that can be registered by the user.
     *
     * @param int $user_id
     * @return collection
     */
    public static function get_registerable_factors(int $user_id): collection {
        return self::get_enabled_factors()
            ->filter(function (mfa_factor $factor) use ($user_id) {
                return $factor->user_can_register($user_id);
            });
    }

    /**
     * Get enabled factors.
     *
     * @return collection
     */
    public static function get_enabled_factors(): collection {
        $factor_factory = new factor_factory();
        return collection::new(core_plugin_manager::instance()->get_enabled_plugins('mfa') ?? [])
            ->map(function (string $name) use ($factor_factory) {
                return $factor_factory->get($name);
            });
    }

    /**
     * Get enabled factor.
     *
     * @return mfa_factor|null
     */
    public static function get_enabled_factor($name): ?mfa_factor {
        $enabled = core_plugin_manager::instance()->get_enabled_plugins('mfa');
        if (!in_array($name, $enabled)) {
            return null;
        }

        $factory = new factor_factory();
        return $factory->get($name);
    }
}
