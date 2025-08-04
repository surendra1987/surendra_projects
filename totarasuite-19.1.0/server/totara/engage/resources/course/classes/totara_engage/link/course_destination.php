<?php
/**
 * This file is part of Totara Learn
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

namespace engage_course\totara_engage\link;

use core\format;
use engage_course\totara_engage\resource\course;
use moodle_url;
use totara_engage\formatter\resource_formatter;
use totara_engage\link\destination_generator;

/**
 * Build the link to the course page
 *
 * @package course_article\totara_engage\link
 */
final class course_destination extends destination_generator {
    /**
     * @var array
     */
    protected $auto_populate = ['id'];

    /**
     * @return string
     */
    public function label(): string {
        $course = course::from_resource_id($this->attributes['id']);

        $resource_formatter = new resource_formatter($course);
        return get_string(
            'back_button',
            'engage_course',
            $resource_formatter->format('name', format::FORMAT_PLAIN)
        );
    }

    /**
     * @return moodle_url
     */
    protected function base_url(): moodle_url {
        return new moodle_url('/totara/engage/resources/course/index.php');
    }
}