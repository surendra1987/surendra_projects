<?php
/**
* This file is part of Totara Learn
*
* Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
* @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
* @package tool_diagnostic
*/

namespace tool_diagnostic\provider;

use tool_diagnostic\content\content;

interface provider {

    /**
     * Return a string that can uniquely identify the provider.
     *
     * @return string
     */
    public static function get_id(): string;

    /**
     * Is this provider enabled or not.
     *
     * @return bool
     */
    public function is_enabled(): bool;

    /**
     * Get the name of the provider.
     *
     * @return string
     */
    public function get_name(): string;

    /**
     * Get the description of the provider.
     *
     * @return string
     */
    public static function get_description(): string;

    /**
     * Execute the provider.
     *
     * @param string $path Path to put files.
     *
     * @return void
     */
    public function execute(string $path): void;

    /**
     * Get content from the provider.
     *
     * @return content
     */
    public function get_content(): content;

    /**
     * Clean up created files.
     *
     * @param string $path Path to files.
     *
     * @return void
     */
    public function cleanup(string $path): void;

    public static function get_order(): int;

}
