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
 * @package format_pathway
 */

namespace format_pathway\webapi\resolver\middleware;

use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use Exception;
use moodle_exception;

class validate_format_pathway_course implements middleware {
    /**
     * @var string
     */
    protected $course_id;

    /**
     * @param string $plugin
     */
    public function __construct(string $course_id) {
        $this->course_id = $course_id;
    }

    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        $course_id = $payload->get_variable($this->course_id);

        try {
            $course = get_course($course_id);
        } catch (Exception $exception) {
            throw new moodle_exception('invalidcourse');
        }

        if ($course->format !== 'pathway') {
            throw new moodle_exception('The pathway course is required.');
        }

        $payload->set_variable('course', $course);
        return $next($payload);
    }
}