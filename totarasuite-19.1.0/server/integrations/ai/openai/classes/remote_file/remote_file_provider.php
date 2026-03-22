<?php
/**
 * This file is part of Totara Core
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
 * @author Andrew Watkins <andrew.watkins@pixelfusion.co.nz>
 * @package ai_openai
 */

namespace ai_openai\remote_file;

use core_ai\configuration\config_collection;
use core_ai\remote_file\remote_file_provider as remote_file_provider_interface;
use core_ai\remote_file\remote_file_base;

/**
 * OpenAI file provider class for registering the OpenAI file implementation
 */
class remote_file_provider implements remote_file_provider_interface {
    /**
     * Create and return an OpenAI file instance
     *
     * @param config_collection $config Configuration for the file provider
     * @param string|null $interaction_class_name Optional class name for interaction logging
     * @return remote_file_base
     */
    public function get_instance(config_collection $config, ?string $interaction_class_name = null): remote_file_base {
        if ($interaction_class_name === null) {
            $interaction_class_name = remote_file::class;
        }

        return new remote_file($config, $interaction_class_name);
    }

    /**
     * Get the provider name
     *
     * @return string
     */
    public function get_name(): string {
        return remote_file::get_name();
    }
}
