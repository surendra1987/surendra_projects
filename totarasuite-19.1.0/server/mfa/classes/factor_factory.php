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

namespace core_mfa;

/**
 * Factory for factor class instances.
 *
 * @package core_mfa
 */
class factor_factory {
    /**
     * Get an instance of the `factor` class for the provided plugin.
     *
     * @param string $name Plugin name, e.g. "totp".
     * @return factor
     */
    public function get(string $name): factor {
        $classname = "mfa_$name\\factor";
        if (!is_subclass_of($classname, factor::class)) {
            throw new \coding_exception("class \\mfa_$name\\factor does not extend \\core_mfa\\factor");
        }
        return new $classname();
    }
}
