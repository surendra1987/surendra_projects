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

use core_ai\feature\feature_base;
use core_ai\feature\interaction_input_interface;
use core_ai\feature\interaction_output_interface;
use moodle_exception;

/**
 * Base class for implementing AI interactions throughout the product.
 *
 * To interact with Totara AI features, extend this class in your component, and implement the abstract methods.
 */
abstract class interaction {

    /**
     * Get the name of the interaction.
     *
     * @return string
     */
    abstract public static function get_name(): string;

    /**
     * Get the description of what the interaction does.
     *
     * @return string
     */
    abstract public static function get_description(): string;

    /**
     * Get the classname of the feature used by this interaction.
     *
     * @return string
     */
    abstract public static function get_feature_class(): string;

    /**
     * Interact with AI features
     *
     * @param interaction_input_interface $params
     * @return interaction_output_interface
     */
    abstract public function run(interaction_input_interface $params): interaction_output_interface;

    /**
     * Get the appropriate AI connector plugin's implementation of the feature class.
     *
     * @return feature_base
     */
    protected function get_ai_feature(): feature_base {
        if (!subsystem::is_ready()) {
            throw new moodle_exception("AI feature has not been enabled or the default AI plugin has not been selected");
        }

        $ai_plugin = subsystem::get_default_plugin();

        return $ai_plugin->get_feature_for_interaction(static::get_feature_class(), get_class($this));
    }
}
