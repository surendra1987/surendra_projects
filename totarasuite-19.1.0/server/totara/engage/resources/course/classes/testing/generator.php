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
 * @author Angela Kuanetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

namespace engage_course\testing;

use core\testing\component_generator;
use engage_course\totara_engage\resource\course;
use totara_engage\access\access;

class generator extends component_generator {
    /**
     * @param array|\stdClass $parameters
     * @return course
     */
    public function create_course_resource($parameters = []): course {
        global $USER;

        if ($parameters instanceof \stdClass) {
            $parameters = get_object_vars($parameters);
        }


        if (!is_array($parameters)) {
            throw new \coding_exception("Invalid parameter \$parameters");
        }
        if (isset($parameters['name'])) {
            $course = $this->datagenerator->create_course(['fullname' => $parameters['name']]);
        } else {
            $course = $this->datagenerator->create_course();
        }
        $parameters['instanceid'] = $course->id;
        if (!isset($parameters['name'])) {
            $parameters['name'] = $course->fullname;

            if (\core_text::strlen($parameters['name']) > 75) {
                $parameters['name'] = \core_text::substr($parameters['name'], 0, 75);
            }
        }
        $userid = null;

        if (isset($parameters['userid'])) {
            $userid = $parameters['userid'];
            unset($parameters['userid']);
        } else {
            $userid = $USER->id;
        }

        if (!isset($parameters['contextid'])) {
            $context = \context_user::instance($userid);
            $parameters['contextid'] = $context->id;
        }
        $parameters['access'] = access::PUBLIC;

        /** @var course $course */
        $course = course::create($parameters, $userid);
        return $course;
    }
}