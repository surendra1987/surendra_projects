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

namespace totara_mobile\webapi\resolver\middleware;

use Closure;
use core\orm\query\builder;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core_course\model\course;
use Exception;
use moodle_exception;

/**
 * Middleware to check course is mobile friendly or not
 */
class require_mobile_friendly_course implements middleware {

    /**
     * @var string
     */
    protected string $course;

    /**
     * @param string $course
     */
    public function __construct(string $course) {
        $this->course = $course;
    }

    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        $course = $this->get_payload_value($payload);
        // When it's course id, we fetch the course and set it to the payload
        if (!is_object($course)) {
            try {
                $course = course::load_by_id($course);
            } catch (Exception $exception) {
                throw new moodle_exception('invalidcourse');
            }
            $payload->set_variable('course', $course);
        }

        if (!builder::table('totara_mobile_compatible_courses')
            ->where('courseid', $course->id)
            ->exists()
        ) {
            throw new moodle_exception('This course is not compatible with this mobile friendly course.');
        }

        return $next($payload);
    }

    /**
     * @param payload $payload
     * @return mixed|null
     */
    protected function get_payload_value(payload $payload) {
        $keys = explode('.', $this->course);

        $initial = array_shift($keys);
        $result = $payload->get_variable($initial);

        if ($result) {
            foreach ($keys as $key) {
                $result = $result[$key] ?? null;
            }
        }

        return $result;
    }
}