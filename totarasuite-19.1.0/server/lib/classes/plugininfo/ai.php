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
 * @package core
 */

namespace core\plugininfo;

use admin_settingpage;
use coding_exception;
use core_ai\configuration\config_collection;
use core_ai\configuration\enable_check;
use core_ai\feature\feature_base;
use core_ai\subsystem;
use core_component;
use moodle_url;
use part_of_admin_tree;

class ai extends base {

    /**
     * Can this AI connector be uninstalled? (Hint: yes.)
     *
     * @return true
     */
    public function is_uninstall_allowed() {
        return true;
    }

    /**
     * Where do you manage this AI connector plugin?
     *
     * @return moodle_url
     */
    public static function get_manage_url() {
        return new moodle_url('/integrations/ai/index.php');
    }

    /**
     * Returns the list of features defined this AI connector plugin.
     *
     * @return feature_base[]
     */
    public function get_supported_features(): array {
        $features = core_component::get_namespace_classes('feature', feature_base::class, $this->component);
        if (empty($features)) {
            debugging("The AI plugin needs to implement at least one feature.");
        }

        return $features;
    }

    /**
     * Get the implementation of this AI connector plugin's feature for use by a specific interaction
     *
     * @param string $feature_class
     * @return feature_base
     * @throws coding_exception
     */
    public function get_feature_for_interaction(string $feature_class, string $interaction_class_name): feature_base {
        $features = $this->get_supported_features();

        foreach ($features as $feature) {
            $class_parents = class_parents($feature);
            if (in_array($feature_class, $class_parents)) {
                return new $feature($this->get_config_collection(), $interaction_class_name);
            }
        }

        throw new coding_exception("$this->name does not support the $feature_class feature");
    }

    /**
     * Allows an AI connector plugin to say that it can or cannot be enabled, and why that is.
     *
     * @return enable_check
     */
    public function get_enable_check(): enable_check {
        return new enable_check();
    }

    /**
     * Get the configurable settings for this AI connector plugin
     */
    public function get_config_collection(): config_collection {
        return new config_collection([]);
    }

    /**
     * Adds the AI connector plugin's settings to the admin tree.
     *
     * @param part_of_admin_tree $adminroot
     * @param $parentnodename
     * @param $hassiteconfig
     */
    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        $config_collection = $this->get_config_collection();

        if ($adminroot->locate('root') && $config_collection->has_options()) {
            $component = subsystem::PLUGIN_TYPE . '_' . $this->name;
            $plugin_settings_page = new admin_settingpage(
                $component,
                $this->displayname,
                'moodle/site:config',
                !$this->is_enabled()
            );

            $config_collection->add_to_settings_page($plugin_settings_page);
            $adminroot->add($parentnodename, $plugin_settings_page);
        }
    }

    /**
     * Get all enabled AI connector plugins.
     *
     * @return array
     */
    public static function get_enabled_plugins() {
        $plugins = core_component::get_plugin_list('ai');
        $enabled_plugins = [];

        foreach ($plugins as $plugin_name => $path) {
            $enabled = get_config(subsystem::PLUGIN_TYPE . '_' . $plugin_name, 'enabled');

            if (!empty($enabled)) {
                $enabled_plugins[$plugin_name] = $plugin_name;
            }
        }

        return $enabled_plugins;
    }

    /**
     * Get the service API base URL.
     *
     * This should be implemented by connector plugins.
     *
     * @return string
     */
    public static function get_base_url(): string {
        throw new coding_exception('get_base_url() not implemented by connector plugin.');
    }

}
