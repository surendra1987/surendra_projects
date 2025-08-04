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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core
 */

namespace core\output;

defined('MOODLE_INTERNAL') || die();

/**
 * A wrapper for the mustache cache, to ignore errors writing to the cache due
 * to concurrency.
 */
class mustache_file_cache_handler extends \Mustache_Cache_FilesystemCache {
    /**
     * Safely cache the data, and silently return otherwise.
     * This will not throw the normal/standard Mustache_Exception_RuntimeException exception.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function cache($key, $value) {
        try {
            parent::cache($key, $value);
        } catch (\Mustache_Exception_RuntimeException $ex) {
            // It's possible due to a race condition we couldn't write to the cache
            debugging('Unable to write to mustache cache: ' . $ex->getMessage(), DEBUG_DEVELOPER, $ex->getTrace());
        }
    }
}