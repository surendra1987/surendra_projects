<?php
/**
 * This file is part of Totara Core
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
 * @package core_ai
 */

namespace core_ai;

use core\plugininfo\ai;
use context_system;
use core_component;
use core_plugin_manager;
use core_ai\feature\feature_base;

/**
 * This class is the central hub for managing the subsystem.
 * It manages the following actions:
 *  enabling & disabling ai plugins
 *  setting the default ai plugin
 *  listing the interactions, features, ai_plugins.
 */
class subsystem {

    /**
     * @var string plugin type name
     */
    public const PLUGIN_TYPE = 'ai';

    public const ADMIN_TREE_FOLDER = 'aifolder';

    private const REQUIRED_CAPABILITY = 'moodle/site:config';

    /**
     * Get all ai plugins
     *
     * @return ai[]
     */
    public static function get_ai_plugins(): array {
        return core_plugin_manager::instance()->get_plugins_of_type(self::PLUGIN_TYPE);
    }

    /**
     * Get list of features available in loader.
     *
     * @return feature_base[]|string[]
     */
    public static function get_features(): array {
        return core_component::get_component_subclasses_of(feature_base::class, 'core_ai', false);
    }

    /**
     * Returns the list of defined interactions across the system.
     *
     * @return interaction[]|string[]
     */
    public static function get_interactions(): array {
        return core_component::get_namespace_classes('ai\interaction', interaction::class);
    }

    /**
     * Enable plugin.
     *
     * @param string $plugin
     * @return bool
     */
    public static function enable_plugin(string $plugin): bool {
        require_capability(static::REQUIRED_CAPABILITY, context_system::instance());
        $enable = set_config('enabled', 1, self::PLUGIN_TYPE . '_' . $plugin);

        // Set as default plugin if only one plugin is present.
        $default_plugin = static::get_default_plugin();
        if (empty($default_plugin) && count(self::get_ai_plugins()) < 2) {
            self::set_default_plugin($plugin);
        }
        core_plugin_manager::reset_caches();

        return $enable;
    }

    /**
     * Disable plugin.
     *
     * @param string $plugin
     * @return bool
     */
    public static function disable_plugin(string $plugin): bool {
        require_capability(static::REQUIRED_CAPABILITY, context_system::instance());
        $disable = set_config('enabled', 0, self::PLUGIN_TYPE . '_' . $plugin);
        core_plugin_manager::reset_caches();

        // unset default ai
        $default_plugin = static::get_default_plugin();
        if (!empty($default_plugin) && $default_plugin->name === $plugin) {
            set_config('default_ai', null, 'core_ai');
        }

        return $disable;
    }

    /**
     * Set default plugin
     *
     * @param string $plugin_name
     * @return bool
     */
    public static function set_default_plugin(string $plugin_name): bool {
        require_capability(static::REQUIRED_CAPABILITY, context_system::instance());

        return set_config('default_ai', $plugin_name, 'core_ai');
    }

    /**
     * Get the default plugin
     *
     * @return ai|null
     */
    public static function get_default_plugin(): ?ai {
        // TODO: This is where to look to the calling interaction to see what it says to use as default.

        // If no preference, use default system plugin.
        $default_plugin = get_config('core_ai', 'default_ai');
        $ai_plugins = subsystem::get_ai_plugins();

        // default plugin has been deleted or has not been set.
        if (empty($default_plugin) || empty($ai_plugins[$default_plugin])) {
            return null;
        }

        return $ai_plugins[$default_plugin];
    }

    /**
     * Check if the AI subsystem is ready to handle interactions.
     * Checks if the experimental feature is enabled and a default plugin has been selected.
     *
     * @return bool
     */
    public static function is_ready(): bool {
        global $CFG;

        return !empty($CFG->enable_ai) && !empty(static::get_default_plugin());
    }
}
