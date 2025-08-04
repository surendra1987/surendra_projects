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
 * @author Katherine Galano <katherine.galano@totara.com>
 * @package core_tag
 */

namespace core_tag\ai\interaction;

use core_ai\feature\interaction_input_interface;

/**
 *  Data transfer object that contains all input data required to run suggest tags AI interactions.
 */
class suggest_tags_input implements interaction_input_interface {
    /**
     * The content to use for suggesting tags.
     *
     * @var string
     */
    public string $course_content;
    /**
     * Tags to exclude from suggestion, because they are already applied.
     *
     * @var array
     */
    public array $existing_tags;

    /**
     * @param string $course_content
     * @param array $existing_tags
     */
    public function __construct(string $course_content, array $existing_tags) {
        $this->course_content = $course_content;
        $this->existing_tags = $existing_tags;
    }
}
