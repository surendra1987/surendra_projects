<?php
/**
 * This file is part of Totara Learn
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

namespace engage_course\totara_engage\card;

use engage_course\totara_engage\resource\course;
use moodle_url;
use theme_config;
use totara_engage\card\card;
use totara_tui\output\component;

class course_card extends card {
    private int $course_id;

    /**
     * @return component
     */
    public function get_tui_component(): component {
        return new component("engage_course/components/card/CourseCard");
    }

    /**
     * @param theme_config|null $theme_config
     * @return array
     */
    public function get_extra_data(?theme_config $theme_config = null): array {
        return [
            'image' => $this->get_card_image('engage_course_resource', $theme_config)->out(false),
            'usage' => course::get_resource_usage($this->instanceid),
        ];
    }

    /**
     * @param string|null $preview_mode
     * @param theme_config|null $theme_config
     * @return moodle_url|null
     */
    public function get_card_image(?string $preview_mode = null, ?theme_config $theme_config = null): ?moodle_url {
        return course_get_image($this->get_courseid());
    }

    /**
     * @return component
     */
    public function get_card_image_component(): component {
        return new component('engage_course/components/card/CourseCardImage');
    }

    /**
     * @param array $args
     * @return string
     */
    public function get_url(array $args): string {
        return (new moodle_url('/totara/engage/resources/course/index.php', ['id' => $this->get_instanceid()]))->out(false);
    }

    /**
     * @return int
     */
    private function get_courseid(): int {
        if (empty($this->course_id)) {
            $this->course_id = course::from_resource_id($this->instanceid)->get_course_id();
        }

        return $this->course_id;
    }

}