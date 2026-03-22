<?php
/*
 * This file is part of Totara Suite
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package totara_core
 */

namespace totara_core\version_specific;

use totara_core\advanced_feature;

/**
 * Manager class for version-specific settings. Extends the advanced_feature 'feature flag'
 * settings in a more dynamic way.
 */
class version_specific_feature extends advanced_feature {

    /**
     * Override the advanced_feature scheme and just use the name provided.
     *
     * @inheritDoc
     */
    public static function get_config_name(string $feature): string {
        return $feature;
    }

    /**
     * Create a dynamic list rather than a hard-coded one. Also, take advantage of the fact that
     * return type array might be indexed or associative, and return a list of class => setting pairs.
     *
     * @inheritDoc
     */
    public static function get_available(): array {
        $available = [];
        foreach (static::load_version_specific_settings() as $class) {
            $available[$class] = $class::get_setting_name();
        }
        return $available;
    }

    /**
     * Use the cached list of classes to find all the instances of version_specific_setting_base in the codebase.
     *
     * @return string[]
     */
    protected static function load_version_specific_settings() {
        return \core_component::get_namespace_classes('local\\version_specific', version_specific_setting_base::class);
    }
}
