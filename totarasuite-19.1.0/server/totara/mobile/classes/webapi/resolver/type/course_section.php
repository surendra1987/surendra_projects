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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_mobile
 */

namespace totara_mobile\webapi\resolver\type;

use core\webapi\execution_context;
use core\webapi\resolver\type\course_section as section;
use totara_mobile\download\download_helper;

class course_section extends section {
    /**
     * @param string $field
     * @param $section
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed|string|string[]|null
     */
    public static function resolve(string $field, $section, array $args, execution_context $ec) {
        if ($field == 'attachments') {
            if ($ec->get_relevant_context()) {
                $context = $ec->get_relevant_context();
            } else {
                $context = \context_course::instance($section->course);
            }
            return download_helper::get_attachments_for_content($context, 'course', 'section', $section->id);
        }

        return parent::resolve($field, $section, $args, $ec);
    }
}