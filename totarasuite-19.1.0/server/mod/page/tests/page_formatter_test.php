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
 * @package mod_page
 */

use core\format;
use core_phpunit\testcase;
use totara_mobile\download\download_helper;

class mod_page_page_formatter_test extends testcase {

    /**
     * @return void
     */
    public function test_formatter(): void {
        self::setAdminUser();
        [$course, $page] = $this->create_test_data();

        $cm = get_coursemodule_from_id(null, $page->cmid, null, true, MUST_EXIST);
        $page_instance = download_helper::get_downloadable_activity_content('page', $cm);

        $formatter = new \mod_page\formatter\page_formatter($page_instance, $page_instance->get_mod_context());
        self::assertEquals($page_instance->name, $formatter->format('name', format::FORMAT_PLAIN));
        self::assertEquals($page_instance->intro, $formatter->format('intro', format::FORMAT_MOBILE));
        self::assertEquals($page_instance->content, $formatter->format('content',format::FORMAT_MOBILE));
        self::assertEquals($page_instance->introformat, $formatter->format('introformat'));
        self::assertEquals($page_instance->attachments, $formatter->format('attachments'));
        self::assertEquals($page_instance->timemodified, $formatter->format('timemodified', format::FORMAT_PLAIN));
        self::assertEquals($page_instance->contentformat, $formatter->format('contentformat'));
        self::assertEquals($page_instance->viewurl, $formatter->format('viewurl', format::FORMAT_PLAIN));
    }

    /**
     * @return array
     */
    private function create_test_data(): array {
        global $DB;

        $gen = self::getDataGenerator();
        $course = $gen->create_course();
        $todb = new \stdClass();
        $todb->courseid = $course->id;
        $DB->insert_record('totara_mobile_compatible_courses', $todb);
        $page = $gen->create_module('page', ['course' => $course, 'name' => "page name"]);

        return [$course, $page];
    }
}