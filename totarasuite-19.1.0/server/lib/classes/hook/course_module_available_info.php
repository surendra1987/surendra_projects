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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

namespace core\hook;

use totara_core\hook\base;

/**
 * The hook is for showing the different module available info based on different course format.
 */
class course_module_available_info extends base {

    /**
     * @var string
     */
    private string $course_format;

    /**
     * @var string
     */
    private string $mod_name;

    /**
     * @var string
     */
    private string $available_info = '';

    /**
     * @var array
     */
    private array $custom_style = [];

    public function __construct(string $course_format, string $mod_name) {
        $this->mod_name = $mod_name;
        $this->course_format = $course_format;
    }

    /**
     * @return string|null
     */
    public function get_available_info(): ?string {
        return $this->available_info;
    }

    /**
     * @return string
     */
    public function get_course_format(): string {
        return $this->course_format;
    }

    /**
     * @return string
     */
    public function get_mod_name(): string {
        return $this->mod_name;
    }

    /**
     * @param string $available_info
     * @return void
     */
    public function set_available_info(string $available_info): void {
        $this->available_info = $available_info;
    }

    /**
     * @return array
     */
    public function get_custom_style(): array {
        return $this->custom_style;
    }

    /**
     * @param array $custom_style
     * @return void
     */
    public function set_custom_style(array $custom_style): void {
        $this->custom_style = $custom_style;
    }
}