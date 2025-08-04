<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai;

use moodle_exception;

/**
 * AI interaction definition.
 * This defines AI interactions created by other components.
 * To interact with AI features using Totara, extend this class in your component.
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
     * Interact with the ai features
     *
     * @param array $params
     * @return array
     */
    abstract public function run(array $params): array;

    /**
     * Get an AI feature. This depends on the settings configured by the admin.
     *
     * @param string $feature_class
     * @return feature
     * @throws moodle_exception
     */
    protected function get_ai_feature(string $feature_class): feature {
        if (!subsystem::is_ready()) {
            throw new moodle_exception("AI feature has not been enabled or the default AI plugin has not been selected");
        }
        $ai_plugin = subsystem::get_default_plugin();

        return $ai_plugin->get_feature_for_interaction($feature_class, get_class($this));
    }
}
