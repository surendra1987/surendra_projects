<?php
/*
 * This file is part of Totara LMS
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
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\suggest;

use totara_catalog\local\config;

defined('MOODLE_INTERNAL') || die();

class suggest_feature {

    const SUGGEST_PLUGIN_NONE = 'NONE';

    /**
     * Finds all the instances of suggesters in the codebase
     *
     * @return array
     */
    public static function get_available(): array {
        $available = [];
        /**
         * @var suggest_base $class
         */
        foreach (static::load_suggesters() as $class) {
            if ($class::is_available()) {
                $available[$class] = $class::get_name();
            }
        }
        return $available;
    }

    /**
     * Finds all the instances of suggesters in the codebase formatted for a dropdown (\ are replaced with __)
     *
     * @return array
     */
    public static function get_available_options(): array {
        $available = [];
        /**
         * @var suggest_base $class
         */
        foreach (static::get_available() as $class => $name) {
            $available[str_replace('\\', '__', $class)] = $name;
        }
        return $available;
    }

    /**
     * Finds a plugin from it's stored preference name
     * @param string $option
     * @return string
     */
    public static function find_option(string $option): string {
        return str_replace('__', '\\', $option);
    }

    /**
     * Creates an instance of the configured suggester
     * @param $user
     * @return suggest_base|null
     */
    public static function get_configured_plugin_instance($user): ?suggest_base {
        $suggesters = static::get_available();
        $class = static::find_option(config::instance()->get_value('suggest_plugin'));
        if (empty($suggesters) || $class == static::SUGGEST_PLUGIN_NONE) {
            return null;
        }
        // There's a suggest plugin defined, but the class either does not exist, or the plugin isn't available
        if (!array_key_exists($class, $suggesters) || !$class::is_available()) {
            error_log('Suggest plugin: ' . $class . ' not found or unavailable.');
            return null;
        }

        return $class::for_language(...static::extract_lang($user));
    }

    /**
     * Extracts the lang and spelling/country from a $user instance. Defaults to server $CFG
     * @param \stdClass $user
     * @return array [string $language, ?string $spelling]
     */
    public static function extract_lang(\stdClass $user): array {
        global $CFG;

        $lang = $user->lang ?? $CFG->lang;
        $parts = preg_split("/[-_]/u", $lang);
        $language = $parts[0];
        $spelling = empty($parts[1]) ? null : $parts[1];
        if (empty($spelling)) {
            $spelling = $user->country ?? $CFG->country;
        }
        return [$language, $spelling];
    }
    /**
     * Use the cached list of classes to find all the instances of suggest_base in the codebase.
     *
     * @return string[]
     */
    protected static function load_suggesters() {
        return \core_component::get_namespace_classes('suggest', suggest_base::class);
    }
}
