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
 */

namespace fixtures;

use core_ai\configuration\config_collection;
use core_ai\remote_file\remote_file_provider;


/**
 * Sample remote file provider for testing
 */
class sample_remote_file_provider implements remote_file_provider {
    /**
     * Get an instance of the sample remote file implementation
     *
     * @param config_collection $config Configuration for the remote file
     * @param string|null $class_name Optional class name for logging
     * @return sample_remote_file
     */
    public function get_instance(config_collection $config, ?string $class_name = null): sample_remote_file {
        if ($class_name === null) {
            $class_name = sample_remote_file::class;
        }

        return new sample_remote_file($config, $class_name);
    }

    /**
     * Get the provider name
     *
     * @return string
     */
    public function get_name(): string {
        return sample_remote_file::get_name();
    }
}
