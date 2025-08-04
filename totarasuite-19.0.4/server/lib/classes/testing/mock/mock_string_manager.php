<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core
 */

namespace core\testing\mock;

class mock_string_manager implements \core_string_manager {
    public $mock_strings = [];

    public function get_string($identifier, $component = '', $a = null, $lang = null) {
        return $this->mock_strings[$component][$identifier] ?? "[$identifier,$component]";
    }

    public function string_exists($identifier, $component) {
        return !str_contains($identifier, 'nonexistent');
    }

    public function string_deprecated($identifier, $component) {
        return false;
    }

    public function get_list_of_countries($returnall = false, $lang = null) {
        return [];
    }

    public function get_list_of_languages($lang = null, $standard = 'iso6392') {
        return [];
    }

    public function translation_exists($lang, $includeall = true) {
        return $lang === 'en' || $lang === 'de';
    }

    public function get_list_of_translations($returnall = false) {
        return [];
    }

    public function get_list_of_currencies($lang = null) {
        return [];
    }

    public function load_component_strings($component, $lang, $disablecache = false, $disablelocal = false) {
    }

    public function reset_caches($phpunitreset = false) {
    }

    public function get_revision() {
        return -1;
    }
}
