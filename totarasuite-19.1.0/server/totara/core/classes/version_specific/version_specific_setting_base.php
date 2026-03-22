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
 * Base class for version-specific settings that are used to temporarily enable/disable new
 * features or behaviours in minor releases, before they become permanently-enabled in the
 * next major release.
 *
 * Extend this class in your component to automagically add a setting to the version specific settings page.
 */
abstract class version_specific_setting_base {

    /**
     * Default constructor, intentionally left empty. This class should not be instanciated and the constructor only
     * exists for unit tests.
     */
    final public function __construct() { }

    /**
     * Returns the Totara minor version in which the setting was created, such as '19.1'.
     *
     * @return string
     */
    abstract public static function get_introduced_version(): string;

    /**
     * Frankenstyle component that the setting belongs to, determined from namespace.
     *
     * @return string
     */
    public static function get_component_name(): string {
        return explode('\\', static::class)[0];
    }

    /**
     * Base setting name, determined from class name.
     *
     * @return string
     */
    public static function get_base_name(): string {
        return substr(static::class, strrpos(static::class, '\\') + 1);
    }

    /**
     * Creates a unique version-specific setting name for this setting.
     *
     * @return string
     */
    public static function get_setting_name(): string {
        $version_replaced = str_replace('.', '_', static::get_introduced_version());
        return 'totara_' . $version_replaced . '_' . static::get_component_name() . '_' . static::get_base_name();
    }

    /**
     * Setting's default value.
     *
     * @return int
     */
    public static function get_default() {
        return advanced_feature::DISABLED;
    }

    /**
     * Effective value in the next major version (when the setting will no longer exist)
     *
     * @return int
     */
    public static function get_nextmajor_default() {
        return advanced_feature::ENABLED;
    }

    /**
     * Whether changing the setting requires all caches to be purged. Please avoid this if possible.
     *
     * @return bool
     */
    public static function require_purge_caches(): bool {
        return false;
    }

    /**
     * Returns the lang string that is the setting's display name.
     *
     * @return \lang_string
     */
    public static function get_display_name(): \lang_string {
        return new \lang_string(static::get_base_name(), static::get_component_name());
    }

    /**
     * Returns the lang string that is the setting's help text.
     *
     * @return \lang_string
     */
    public static function get_help_text(): \lang_string {
        return new \lang_string(static::get_base_name() . '_help', static::get_component_name());
    }
}
