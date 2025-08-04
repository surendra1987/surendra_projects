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

namespace core_ai\feature\generative_prompt_with_file;

use coding_exception;
use core_ai\feature\feature_base;
use core_ai\remote_file\remote_file_provider;

/**
 * Generative prompt with file abstract class; create a concrete implementation in any ai connector
 * plugin that supports generative prompt with associated file input.
 */
abstract class generative_prompt_with_file_feature extends feature_base {
    
    /**
     * Get the display name for this feature.
     *
     * @return string
     * @throws coding_exception
     */
    public static function get_name(): string {
        return get_string('feature_generative_prompt_with_file', 'ai');
    }

    /**
     * Get the remote file provider for this feature
     *
     * @return remote_file_provider
     */
    abstract public function get_remote_file_provider(): remote_file_provider;
}
