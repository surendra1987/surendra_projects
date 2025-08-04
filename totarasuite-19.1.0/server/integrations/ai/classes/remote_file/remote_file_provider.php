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
 * @package core_ai
 */

namespace core_ai\remote_file;

use core_ai\configuration\config_collection;

/**
 * Interface for AI providers that support remote file operations
 *
 * This interface implements the factory pattern to create remote file implementations
 * without exposing the concrete implementation details to client code.
 */
interface remote_file_provider {
    /**
     * Get an instance of a remote file implementation
     *
     * @param config_collection $config Configuration parameters
     * @param string $interaction_class_name Class name to use for interaction logging
     * @return remote_file_base A concrete instance of the remote file implementation
     */
    public function get_instance(config_collection $config, string $interaction_class_name): remote_file_base;
}
