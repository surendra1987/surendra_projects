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

use core\webapi\execution_context;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use totara_mobile\webapi\resolver\middleware\require_mobile_friendly_course;

class totara_mobile_webapi_middleware_require_mobile_friendly_course_test extends \core_phpunit\testcase {
    /**
     * @covers ::handle
     */
    public function test_require_mobile_friendly_course(): void {
        global $DB;
        self::setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $context = execution_context::create("dev");

        $id_key = 'course';
        $args = [$id_key => $course];
        $payload = payload::create($args, $context);
        $require = new require_mobile_friendly_course($id_key);
        try {
            $require->handle($payload, function (payload $payload): result {
                return new result($payload->get_variables());
            });
        } catch (moodle_exception $e) {
            self::assertEquals('This course is not compatible with this mobile friendly course.', $e->getMessage());
        }

        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);
        $result = $require->handle($payload, function (payload $payload): result {
            return new result($payload->get_variables());
        });

        self::assertNotEmpty($result->get_data());
    }

}
